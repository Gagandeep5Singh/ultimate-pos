<?php

namespace App\Http\Controllers;

use App\SellingPriceGroup;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_spg_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@activateDeactivate\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs  @if($is_active) tw-dw-btn-error @else tw-dw-btn-success @endif activate_deactivate_spg"><i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif</button>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.'.$spg->id]);

            $output = ['success' => true,
                'data' => $spg,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show interface to download product price excel file.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductPrice(){
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.update_product_price');
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $business_id = request()->user()->business_id;
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                            ->where('p.business_id', $business_id)
                            ->whereIn('p.type', ['single', 'variable'])
                            ->select('sub_sku', 'p.name as product_name', 'variations.name as variation_name', 'p.type', 'variations.id', 'pv.name as product_variation_name', 'sell_price_inc_tax', 'variations.sale_price')
                            ->with(['group_prices'])
                            ->get();
        $export_data = [];
        foreach ($variations as $variation) {
            $temp = [];
            $temp['product'] = $variation->type == 'single' ? $variation->product_name : $variation->product_name.' - '.$variation->product_variation_name.' - '.$variation->variation_name;
            $temp['sku'] = $variation->sub_sku;
            $temp['Selling Price Including Tax'] = $variation->sell_price_inc_tax;
            $temp['SALE PRICE'] = $variation->sale_price ?? ''; // Add SALE PRICE column

            foreach ($price_groups as $price_group) {
                $price_group_id = $price_group->id;
                $variation_pg = $variation->group_prices->filter(function ($item) use ($price_group_id) {
                    return $item->price_group_id == $price_group_id;
                });

                $temp[$price_group->name] = $variation_pg->isNotEmpty() ? $variation_pg->first()->price_inc_tax : '';
            }
            $export_data[] = $temp;
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }
        ob_start();

        return collect($export_data)->downloadExcel(
            'product_prices.xlsx',
            null,
            true
        );
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');

                $parsed_array = Excel::toArray([], $file);

                $headers = $parsed_array[0][0];

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = request()->user()->business_id;
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

                // Find column indexes from headers
                $sku_col = array_search('SKU (Leave blank to auto generate sku)', $headers);
                $variation_skus_col = array_search('VARIATION SKUs (| seperated values & blank if product type if single)', $headers);
                $selling_price_col = array_search('SELLING PRICE', $headers);
                
                // Find SALE PRICE column - search for exact match first, then partial match
                $sale_price_col = array_search('SALE PRICE', $headers);
                if ($sale_price_col === false) {
                    // Try to find column containing "SALE PRICE" (e.g., "SALE PRICE rev")
                    foreach ($headers as $idx => $header) {
                        if (stripos($header, 'SALE PRICE') !== false) {
                            $sale_price_col = $idx;
                            break;
                        }
                    }
                }

                // If columns not found, try old format (backward compatibility)
                if ($sku_col === false) {
                    // Old format: column 0=product name, 1=SKU, 2=selling price, 3=SALE PRICE (if exists)
                    $sku_col = array_search('sku', array_map('strtolower', $headers));
                    if ($sku_col === false) {
                        $sku_col = 1; // Default to column 1
                    }
                    $selling_price_col = array_search('Selling Price Including Tax', $headers);
                    if ($selling_price_col === false) {
                        $selling_price_col = 2; // Default to column 2
                    }
                    // Try to find SALE PRICE column (exact or partial match)
                    if ($sale_price_col === false) {
                        $sale_price_col = array_search('SALE PRICE', $headers);
                        if ($sale_price_col === false) {
                            foreach ($headers as $idx => $header) {
                                if (stripos($header, 'SALE PRICE') !== false) {
                                    $sale_price_col = $idx;
                                    break;
                                }
                            }
                        }
                    }
                    $variation_skus_col = false;
                }

                //Get price group names from headers (columns after SALE PRICE)
                $imported_pgs = [];
                foreach ($headers as $key => $value) {
                    if (! empty($value) && $key > $sale_price_col) {
                        // Skip LOCATION STOCK column
                        if (strtoupper($value) != 'LOCATION STOCK') {
                            $imported_pgs[$key] = $value;
                        }
                    }
                }

                $error_msg = '';
                DB::beginTransaction();

                foreach ($imported_data as $key => $value) {
                    // Get variation SKUs (pipe-separated) - new format
                    if ($variation_skus_col !== false && !empty($value[$variation_skus_col])) {
                        $variation_skus = explode('|', $value[$variation_skus_col]);
                        $selling_prices = !empty($value[$selling_price_col]) ? explode('|', $value[$selling_price_col]) : [];
                        $sale_prices = !empty($value[$sale_price_col]) ? explode('|', $value[$sale_price_col]) : [];
                    } else {
                        // Old format (SellingPriceGroupController export) or single product - use product SKU
                        $variation_skus = !empty($value[$sku_col]) ? [trim($value[$sku_col])] : [];
                        $selling_prices = !empty($value[$selling_price_col]) ? [$value[$selling_price_col]] : [];
                        // For old format, SALE PRICE is in column 3 (after Selling Price Including Tax)
                        // Always include SALE PRICE column even if empty (to allow setting to NULL)
                        if ($sale_price_col !== false) {
                            $sale_prices = [isset($value[$sale_price_col]) ? $value[$sale_price_col] : ''];
                        } else {
                            $sale_prices = [];
                        }
                    }

                    // Update each variation
                    foreach ($variation_skus as $idx => $sub_sku) {
                        if (empty($sub_sku)) {
                            continue;
                        }

                        $variation = Variation::where('sub_sku', trim($sub_sku))->first();
                        if (empty($variation)) {
                            $row = $key + 2; // +2 because header is row 1, data starts at row 2
                            $error_msg = __('lang_v1.product_not_found_exception', ['sku' => $sub_sku, 'row' => $row]);

                            throw new \Exception($error_msg);
                        }

                        // Update selling price if provided
                        if (isset($selling_prices[$idx]) && !empty($selling_prices[$idx])) {
                            $new_selling_price = $this->parsePriceValue($selling_prices[$idx]);
                            if ($variation->sell_price_inc_tax != $new_selling_price) {
                                //update price for base selling price, adjust default_sell_price, profit %
                                $variation->sell_price_inc_tax = $new_selling_price;
                                $tax = $variation->product->product_tax()->get();
                                $tax_percent = !empty($tax) && !empty($tax->first()) ? $tax->first()->amount : 0;
                                $variation->default_sell_price = $this->commonUtil->calc_percentage_base($new_selling_price, $tax_percent);
                                $variation->profit_percent = $this->commonUtil
                                                ->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                            }
                        }

                        // Update SALE PRICE - always update if column exists (even if empty to set NULL)
                        if ($sale_price_col !== false) {
                            $sale_price_value = null;
                            
                            // Get the sale price value for this variation
                            if (isset($sale_prices[$idx])) {
                                $sale_price_value = $sale_prices[$idx];
                            } elseif (isset($value[$sale_price_col])) {
                                // For old format or single product, get directly from column
                                $sale_price_value = $value[$sale_price_col];
                            }
                            
                            // Process the value
                            if ($sale_price_value !== null) {
                                $sale_price_str = trim((string) $sale_price_value);
                                
                                // If cell is empty, blank, or just whitespace, set to NULL (remove sale price)
                                if (empty($sale_price_str) || $sale_price_str === '' || $sale_price_str === '0' || $sale_price_str === '0.00' || $sale_price_str === '0,00') {
                                    $variation->sale_price = null;
                                    \Log::info("SALE PRICE Import - Setting to NULL (empty cell) | SKU: " . $sub_sku);
                                } else {
                                    // Parse and update the value
                                    $parsed_value = $this->parsePriceValue($sale_price_value);
                                    \Log::info("SALE PRICE Import - Original: " . var_export($sale_price_value, true) . " | Parsed: " . var_export($parsed_value, true) . " | SKU: " . $sub_sku);
                                    $variation->sale_price = $parsed_value;
                                }
                            } else {
                                // Column exists but value not set - set to NULL
                                $variation->sale_price = null;
                                \Log::info("SALE PRICE Import - Setting to NULL (column exists but no value) | SKU: " . $sub_sku);
                            }
                        }

                        $variation->update();
                    }

                    //update selling price groups (if any)
                    foreach ($imported_pgs as $k => $v) {
                        // For price groups, use the first variation's SKU to find the product
                        $first_sku = !empty($variation_skus[0]) ? trim($variation_skus[0]) : (!empty($value[$sku_col]) ? $value[$sku_col] : null);
                        if (empty($first_sku)) {
                            continue;
                        }

                        $variation = Variation::where('sub_sku', $first_sku)->first();
                        if (empty($variation)) {
                            continue;
                        }

                        $price_group = $price_groups->filter(function ($item) use ($v) {
                            return strtolower($item->name) == strtolower($v);
                        });

                        if ($price_group->isNotEmpty()) {
                            //Check if price is numeric
                            if (! is_null($value[$k]) && ! empty($value[$k]) && ! is_numeric($value[$k])) {
                                $row = $key + 2;
                                $error_msg = __('lang_v1.price_group_non_numeric_exception', ['row' => $row]);

                                throw new \Exception($error_msg);
                            }

                            if (! is_null($value[$k]) && ! empty($value[$k])) {
                                VariationGroupPrice::updateOrCreate(
                                    ['variation_id' => $variation->id,
                                        'price_group_id' => $price_group->first()->id,
                                    ],
                                    ['price_inc_tax' => $this->parsePriceValue($value[$k]),
                                    ]
                                );
                            }
                        } else {
                            $row = $key + 2;
                            $error_msg = __('lang_v1.price_group_not_found_exception', ['pg' => $v, 'row' => $row]);

                            throw new \Exception($error_msg);
                        }
                    }
                }
                DB::commit();
            }
            $output = ['success' => 1,
                'msg' => __('lang_v1.product_prices_imported_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect('update-product-price')->with('notification', $output);
        }

        return redirect('update-product-price')->with('status', $output);
    }

    /**
     * Parse price value to handle decimal separator issues
     * Converts values like "1449,99" or "1449.99" to proper numeric format
     * Returns float value ready to store in database
     */
    private function parsePriceValue($value)
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        // If already a float/int and no string separators, return as is
        if (is_numeric($value) && !is_string($value)) {
            return (float) $value;
        }

        // Convert to string and trim
        $value = trim((string) $value);
        
        // Remove any currency symbols or spaces
        $value = preg_replace('/[€$£¥\s]/', '', $value);
        
        // If it's a pure number string (no separators), return as float
        if (is_numeric($value) && strpos($value, ',') === false && strpos($value, '.') === false) {
            return (float) $value;
        }

        // Check if value contains both comma and period
        $has_comma = strpos($value, ',') !== false;
        $has_period = strpos($value, '.') !== false;

        if ($has_comma && $has_period) {
            // Both separators present - determine which is decimal
            $comma_pos = strrpos($value, ',');
            $period_pos = strrpos($value, '.');
            
            // The last separator is usually the decimal separator
            if ($comma_pos > $period_pos) {
                // Format: "1.449,99" - period is thousand, comma is decimal
                $value = str_replace('.', '', $value); // Remove thousand separator
                $value = str_replace(',', '.', $value); // Convert decimal to period
            } else {
                // Format: "1,449.99" - comma is thousand, period is decimal
                $value = str_replace(',', '', $value); // Remove thousand separator
                // Period already in place
            }
        } elseif ($has_comma) {
            // Only comma - determine if decimal or thousand based on pattern
            $parts = explode(',', $value);
            if (count($parts) == 2 && isset($parts[1]) && strlen($parts[1]) <= 2) {
                // Format: "1449,99" - comma is decimal separator (most common case)
                // ALWAYS treat as decimal when 1-2 digits after comma
                $value = str_replace(',', '.', $value);
            } elseif (count($parts) > 2) {
                // Multiple commas - all are thousand separators: "1,449,999" - remove all
                $value = str_replace(',', '', $value);
            } else {
                // Single comma - check digits after
                if (isset($parts[1]) && strlen($parts[1]) <= 2) {
                    // 1-2 digits after comma = decimal separator
                    $value = str_replace(',', '.', $value);
                } else {
                    // More digits or no digits after = thousand separator
                    $value = str_replace(',', '', $value);
                }
            }
        } elseif ($has_period) {
            // Only period - check if it's likely decimal or thousand
            $parts = explode('.', $value);
            if (count($parts) == 2 && isset($parts[1]) && strlen($parts[1]) <= 2) {
                // Format: "1449.99" - period is decimal separator (standard format)
                // Keep as is - already correct format
            } elseif (count($parts) > 2) {
                // Multiple periods - all are thousand separators: "1.449.999" - remove all
                $value = str_replace('.', '', $value);
            } else {
                // Single period - check digits after
                if (isset($parts[1]) && strlen($parts[1]) <= 2) {
                    // 1-2 digits after period = decimal separator
                    // Keep as is
                } else {
                    // More digits after = thousand separator, remove it
                    $value = str_replace('.', '', $value);
                }
            }
        }

        // Convert to float and return
        $result = (float) $value;
        return $result;
    }

    /**
     * Activate/deactivate selling price group.
     */
    public function activateDeactivate($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];

            return $output;
        }
    }
}
