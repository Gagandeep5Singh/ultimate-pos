<?php

namespace App\Console\Commands;

use App\Business;
use App\BusinessLocation;
use App\Product;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllProductsReportExport;

class ExportProductsToGoogleSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:exportProductsToGoogleSheets {business_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all products and update Google Sheets (runs hourly)';

    protected $transactionUtil;
    protected $productUtil;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        parent::__construct();
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $business_id = $this->argument('business_id');
        
        // If no business_id provided, get all businesses
        if (empty($business_id)) {
            $businesses = Business::all();
        } else {
            $businesses = Business::where('id', $business_id)->get();
        }

        foreach ($businesses as $business) {
            $this->info("Exporting products for business: {$business->name} (ID: {$business->id})");
            
            try {
                $this->exportProducts($business->id);
                $this->info("Successfully exported products for business: {$business->name}");
            } catch (\Exception $e) {
                $this->error("Error exporting products for business {$business->name}: " . $e->getMessage());
                \Log::error("ExportProductsToGoogleSheets Error: " . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * Export products for a specific business
     */
    protected function exportProducts($business_id)
    {
        // Get all locations for this business
        $location_ids_for_columns = BusinessLocation::where('business_id', $business_id)
            ->pluck('id')
            ->toArray();

        // Build query exactly like ReportController exportAllProductsReport
        $query = Product::join('variations as v', 'products.id', '=', 'v.product_id')
            ->leftjoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftjoin('categories as c1', 'products.category_id', '=', 'c1.id')
            ->leftjoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
            ->leftjoin('units', 'products.unit_id', '=', 'units.id')
            ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftjoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
            ->where('products.business_id', $business_id)
            ->where('products.type', '!=', 'modifier');

        // Select variation-level data
        $products = $query->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.type as product_type',
            'products.sku',
            'pv.name as product_variation',
            'v.id as variation_id',
            'v.name as variation_name',
            'v.sub_sku',
            'c1.name as category',
            'c2.name as sub_category',
            'units.actual_name as unit',
            'brands.name as brand',
            'tax_rates.name as tax',
            'products.enable_stock',
            DB::raw("(SELECT COALESCE(SUM(vld2.qty_available), 0) FROM variation_location_details as vld2 WHERE vld2.variation_id = v.id) as current_stock"),
            DB::raw('v.sell_price_inc_tax as selling_price'),
            DB::raw('v.sale_price as sale_price'),
            DB::raw('v.dpp_inc_tax as purchase_price')
        );

        // Per-location stock columns
        foreach ($location_ids_for_columns as $locId) {
            $products->addSelect(DB::raw("(SELECT COALESCE(SUM(vld3.qty_available), 0) FROM variation_location_details as vld3 WHERE vld3.variation_id = v.id AND vld3.location_id = " . (int)$locId . ") as loc_{$locId}"));
        }

        // Group by variation ID
        $products->groupBy('v.id');

        try {
            $products = $products->get();
        } catch (\Exception $e) {
            \Log::error('ExportProductsToGoogleSheets Query Error: ' . $e->getMessage());
            throw $e;
        }

        // Prepare export data (same as ReportController)
        $export_data = [];
        $headers = ['Product', 'SKU', 'Current Stock'];
        
        // Add location headers
        $location_names = [];
        foreach ($location_ids_for_columns as $locId) {
            $loc = BusinessLocation::find($locId);
            if ($loc) {
                $location_names[$locId] = $loc->name;
                $headers[] = $loc->name;
            }
        }
        
        $headers[] = 'Business Location';
        $headers[] = 'Unit Purchase Price';
        $headers[] = 'Selling Price';
        $headers[] = 'Discount';
        $headers[] = 'Sale Price';
        $headers[] = '3x';
        $headers[] = 'Normal Profit';
        $headers[] = 'After Klarna Fees';
        $headers[] = 'Category';
        $headers[] = 'Sub Category';
        $headers[] = 'Brand';
        $headers[] = 'Tax';

        // Format data exactly like ReportController
        foreach ($products as $product) {
            // Format product name like ReportController
            if ($product->product_type == 'variable') {
                $product_name = $product->product_name . ' - ' . ($product->product_variation ?? '') . ' - ' . ($product->variation_name ?? '');
                $sku = $product->sub_sku ?? '';
            } else {
                $product_name = $product->product_name;
                $sku = $product->sku ?? '';
            }
            
            $row = [
                $product_name,
                $sku,
                $product->enable_stock ? $this->productUtil->num_f($product->current_stock, false, null, true) : '--',
            ];

            // Add location stocks
            foreach ($location_ids_for_columns as $locId) {
                $colName = 'loc_' . $locId;
                $stock = $product->$colName ?? 0;
                $row[] = $this->productUtil->num_f($stock, false, null, true);
            }

            // Business locations
            $product_obj = Product::find($product->product_id);
            $product_locations = $product_obj ? $product_obj->product_locations : collect();
            $location_names_list = [];
            foreach ($product_locations as $pl) {
                if (isset($location_names[$pl->location_id])) {
                    $location_names_list[] = $location_names[$pl->location_id];
                }
            }
            $row[] = !empty($location_names_list) ? implode(', ', $location_names_list) : '';

            // Purchase price
            $purchase_price = $product->purchase_price ?? 0;
            $row[] = $this->transactionUtil->num_f($purchase_price, true);

            // Selling price and related
            $selling_price = $product->selling_price ?? 0;
            $row[] = $this->transactionUtil->num_f($selling_price, true);

            // Discount
            $discount = '';
            if (!empty($product->sale_price) && $product->sale_price > 0 && $product->sale_price < $selling_price) {
                $diff = $selling_price - $product->sale_price;
                $percent = round($diff / $selling_price * 100);
                $discount = $this->transactionUtil->num_f($diff, true) . ' (' . $percent . '%)';
            }
            $row[] = $discount;

            // Sale price
            $sale_price_display = '';
            $selling_price_val = $selling_price;
            if (!empty($product->sale_price) && $product->sale_price > 0 && $product->sale_price < $selling_price) {
                $sale_price_display = $this->transactionUtil->num_f($product->sale_price, true);
                $selling_price_val = $product->sale_price; // Use sale price for calculations
            }
            $row[] = $sale_price_display;

            // 3x price
            $three_x = $selling_price_val > 0 ? $this->transactionUtil->num_f($selling_price_val / 3, true) : '';
            $row[] = $three_x;

            // Normal profit
            $normal_profit = '';
            if ($selling_price_val > 0 && $purchase_price > 0) {
                $normal_profit = $this->transactionUtil->num_f($selling_price_val - $purchase_price, true);
            }
            $row[] = $normal_profit;

            // Klarna profit
            $klarna_profit = '';
            if ($selling_price_val > 0 && $purchase_price > 0) {
                $normal_profit_val = $selling_price_val - $purchase_price;
                if ($normal_profit_val > 0) {
                    $klarna_fee = ($selling_price_val * 0.0499) + 0.35;
                    $klarna_profit = $this->transactionUtil->num_f($normal_profit_val - $klarna_fee, true);
                }
            }
            $row[] = $klarna_profit;

            $row[] = $product->category ?? '';
            $row[] = $product->sub_category ?? '';
            $row[] = $product->brand ?? '';
            $row[] = $product->tax ?? '';

            $export_data[] = $row;
        }

        // Create Excel file
        $filename = 'products_export_' . $business_id . '_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
        $filepath = 'exports/' . $filename;
        
        // Save to storage
        $export = new AllProductsReportExport($headers, $export_data);
        Excel::store($export, $filepath, 'public');
        
        // Also save a fixed filename for easy access (overwrites previous)
        $fixed_filename = 'products_export_' . $business_id . '.xlsx';
        $fixed_filepath = 'exports/' . $fixed_filename;
        Excel::store($export, $fixed_filepath, 'public');
        
        // Also create CSV version for Google Sheets IMPORTDATA function
        $csv_filename = 'products_export_' . $business_id . '.csv';
        $csv_filepath = 'exports/' . $csv_filename;
        $csv_full_path = storage_path('app/public/' . $csv_filepath);
        
        // Create CSV file
        $csv_file = fopen($csv_full_path, 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fwrite($csv_file, "\xEF\xBB\xBF");
        
        // Write headers
        fputcsv($csv_file, $headers);
        
        // Write data rows
        foreach ($export_data as $row) {
            fputcsv($csv_file, $row);
        }
        
        fclose($csv_file);
        
        // Get public URL
        $public_url = url('storage/' . $csv_filepath);
        
        $this->info("Products exported to:");
        $this->info("  Excel: storage/app/public/{$fixed_filepath}");
        $this->info("  CSV: storage/app/public/{$csv_filepath}");
        $this->info("  Public URL: {$public_url}");
        $this->info("");
        $this->info("To import to Google Sheets automatically:");
        $this->info("1. Open Google Sheets");
        $this->info("2. In cell A1, use: =IMPORTDATA(\"{$public_url}\")");
        $this->info("3. The sheet will update automatically when the CSV file is updated");
        
        \Log::info("Products exported for Google Sheets - CSV URL: {$public_url}");
        
        return true;
    }
}

