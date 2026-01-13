<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductPricesWithStockExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $business_id;

    public function __construct($business_id)
    {
        $this->business_id = $business_id;
    }

    public function title(): string
    {
        return 'Product Prices & Stock';
    }

    public function headings(): array
    {
        // Get all locations for this business
        $locations = DB::table('business_locations')
            ->where('business_id', $this->business_id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        // Create headings array
        $headings = [
            'Product',
            'SKU',
            'Category',
            'Subcategory',
            'Selling Price Including Tax',
            'Unit Purchase Price'
        ];

        // Add each location as a column
        foreach ($locations as $location) {
            $headings[] = $location;
        }

        return $headings;
    }

    public function array(): array
    {
        // Get all locations first
        $locations = DB::table('business_locations')
            ->where('business_id', $this->business_id)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        // Get product data with variations
        $products = DB::table('products as p')
            ->join('variations as v', 'v.product_id', '=', 'p.id')
            ->leftJoin('categories as c1', 'p.category_id', '=', 'c1.id')
            ->leftJoin('categories as c2', 'p.sub_category_id', '=', 'c2.id')
            ->where('p.business_id', $this->business_id)
            ->where('p.type', '!=', 'modifier')
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'v.sub_sku as sku',
                'c1.name as category',
                'c2.name as sub_category',
                'v.sell_price_inc_tax as selling_price',
                'v.default_purchase_price as purchase_price',
                'v.id as variation_id'
            )
            ->orderBy('p.name')
            ->orderBy('v.sub_sku')
            ->get();

        // Pre-load all stock data
        $variationIds = $products->pluck('variation_id')->toArray();
        
        $stockData = DB::table('variation_location_details')
            ->whereIn('variation_id', $variationIds)
            ->get()
            ->groupBy('variation_id');

        $data = [];

        foreach ($products as $product) {
            $row = [
                $product->product_name,
                $product->sku,
                $product->category ?? '',
                $product->sub_category ?? '',
                $product->selling_price ?? 0,
                $product->purchase_price ?? 0
            ];

            // Get stock for this variation
            $locationStock = [];
            if (isset($stockData[$product->variation_id])) {
                foreach ($stockData[$product->variation_id] as $stock) {
                    $locationStock[$stock->location_id] = $stock->qty_available;
                }
            }

            // Add stock for each location
            foreach ($locations as $location) {
                $row[] = $locationStock[$location->id] ?? 0;
            }

            $data[] = $row;
        }

        return $data;
    }
}