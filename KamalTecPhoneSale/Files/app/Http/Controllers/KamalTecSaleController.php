<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\KamalTecSale;
use App\KamalTecSaleLine;
use App\KamalTecPayment;
use App\Product;
use App\ReferenceCount;
use App\Utils\Util;
use App\Exports\KamalTecSalesExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KamalTecSaleController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Generate invoice number in format KTS-000001
     */
    private function generateInvoiceNumber($business_id)
    {
        $ref_count = $this->commonUtil->setAndGetReferenceCount('kamal_tec_sale', $business_id);
        $invoice_no = 'KTS-' . str_pad($ref_count, 6, '0', STR_PAD_LEFT);
        
        return $invoice_no;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $sales = KamalTecSale::leftJoin('contacts AS c', 'kamal_tec_sales.contact_id', '=', 'c.id')
                    ->leftJoin('business_locations AS bl', 'kamal_tec_sales.location_id', '=', 'bl.id')
                    ->leftJoin('users AS u', 'kamal_tec_sales.created_by', '=', 'u.id')
                    ->where('kamal_tec_sales.business_id', $business_id)
                    ->select(
                        'kamal_tec_sales.*',
                        'c.name as customer_name',
                        'c.contact_id as customer_contact_id',
                        'bl.name as location_name',
                        DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as created_by_name"),
                        DB::raw("GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as due_commission")
                    );

            // Date range filter
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $sales->whereDate('kamal_tec_sales.sale_date', '>=', request()->start_date)
                    ->whereDate('kamal_tec_sales.sale_date', '<=', request()->end_date);
            }

            // Customer filter
            if (!empty(request()->contact_id)) {
                $sales->where('kamal_tec_sales.contact_id', request()->contact_id);
            }

            // Status filter
            if (!empty(request()->status)) {
                $sales->where('kamal_tec_sales.status', request()->status);
            }

            // Paid status filter
            if (request()->has('paid_status')) {
                $paid_status = request()->paid_status;
                if ($paid_status == 'paid') {
                    $sales->whereRaw('kamal_tec_sales.due_amount <= 0');
                } elseif ($paid_status == 'partial') {
                    $sales->whereRaw('kamal_tec_sales.paid_amount > 0 AND kamal_tec_sales.due_amount > 0');
                } elseif ($paid_status == 'due') {
                    $sales->whereRaw('kamal_tec_sales.due_amount > 0');
                }
            }

            // Commission type filter
            if (!empty(request()->commission_type)) {
                $sales->where('kamal_tec_sales.commission_type', request()->commission_type);
            }

            // Product filter (search in sale lines)
            if (!empty(request()->product_id)) {
                $sales->whereHas('saleLines', function ($query) {
                    $query->where('product_id', request()->product_id);
                });
            }

            return DataTables::of($sales)
                ->addColumn('kt_invoice_no', function ($row) {
                    return $row->kt_invoice_no ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="' . action([\App\Http\Controllers\KamalTecSaleController::class, 'show'], [$row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';

                    $html .= '<li><a href="' . action([\App\Http\Controllers\KamalTecSaleController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';

                    $html .= '<li><a href="' . action([\App\Http\Controllers\KamalTecPaymentController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal" data-href="' . action([\App\Http\Controllers\KamalTecPaymentController::class, 'addPayment'], [$row->id]) . '"><i class="fa fa-money"></i> ' . __("purchase.add_payment") . '</a></li>';

                    $html .= '<li><a href="' . action([\App\Http\Controllers\KamalTecSaleController::class, 'destroy'], [$row->id]) . '" class="delete-sale"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</a></li>';

                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('sale_date', '{{@format_date($sale_date)}}')
                ->editColumn('total_amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->total_amount . '</span>';
                })
                ->editColumn('commission_amount', function ($row) {
                    // Show due commission (already calculated in query)
                    $dueCommission = $row->due_commission ?? 0;
                    return '<span class="display_currency" data-currency_symbol="true">' . $dueCommission . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $statuses = [
                        'open' => '<span class="label label-warning">' . __('lang_v1.open') . '</span>',
                        'closed' => '<span class="label label-success">' . __('lang_v1.closed') . '</span>',
                        'cancelled' => '<span class="label label-danger">' . __('lang_v1.cancelled') . '</span>',
                    ];
                    return $statuses[$row->status] ?? $row->status;
                })
                ->rawColumns(['action', 'total_amount', 'commission_amount', 'status', 'kt_invoice_no'])
                ->make(true);
            } catch (\Exception $e) {
                \Log::error('Kamal Tec Sale List Error: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'draw' => request()->draw ?? 1,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Error loading data. Please check if migration has been run: php artisan migrate'
                ], 500);
            }
        }

        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->pluck('name', 'id');

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.index', compact('customers', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->pluck('name', 'id');

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.create', compact('business_locations', 'customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Convert date format from datepicker to database format (YYYY-MM-DD)
            $sale_date = null;
            if (!empty($request->sale_date)) {
                try {
                    $date_str = trim($request->sale_date);
                    
                    // First try using the business date format
                    $date_format = session('business.date_format', 'd/m/Y');
                    try {
                        $sale_date = \Carbon\Carbon::createFromFormat($date_format, $date_str)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // If that fails, try common date formats
                        $formats_to_try = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                        $parsed = false;
                        
                        foreach ($formats_to_try as $format) {
                            try {
                                $sale_date = \Carbon\Carbon::createFromFormat($format, $date_str)->format('Y-m-d');
                                $parsed = true;
                                break;
                            } catch (\Exception $e2) {
                                continue;
                            }
                        }
                        
                        // Last resort: use Carbon's flexible parser
                        if (!$parsed) {
                            $sale_date = \Carbon\Carbon::parse($date_str)->format('Y-m-d');
                        }
                    }
                    
                    // Replace the request value with converted date
                    $request->merge(['sale_date' => $sale_date]);
                } catch (\Exception $e) {
                    \Log::error('Date conversion error: ' . $e->getMessage() . ' - Date: ' . $request->sale_date);
                    throw new \Exception('Invalid date format: ' . $request->sale_date . '. Please select a valid date from the calendar.');
                }
            }

            // Debug: Log all request data
            \Log::info('Kamal Tec Sale Store Request:', [
                'all_data' => $request->all(),
                'products' => $request->products,
                'quantities' => $request->quantities,
                'unit_prices' => $request->unit_prices,
                'original_date' => $request->input('sale_date'),
                'converted_date' => $sale_date
            ]);

            // Validate required fields
            $validated = $request->validate([
                'contact_id' => 'required|exists:contacts,id',
                'sale_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
                'quantities.*' => 'required|numeric|min:0.01',
                'unit_prices.*' => 'required|numeric|min:0',
                'commission_type' => 'required|in:percent,fixed',
                'commission_value' => 'required|numeric|min:0',
            ], [
                'contact_id.required' => 'Please select a customer',
                'sale_date.required' => 'Sale date is required',
                'sale_date.date' => 'The sale date is not a valid date.',
                'products.required' => 'Please add at least one product',
                'products.min' => 'Please add at least one product',
                'products.*.required' => 'All products must be valid',
                'quantities.*.required' => 'Please enter quantity for all products',
                'unit_prices.*.required' => 'Please enter price for all products',
            ]);

            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            // Generate invoice number
            $invoice_no = $this->generateInvoiceNumber($business_id);

            // Calculate totals
            $total_amount = 0;
            $sale_lines = [];
            
            // Debug: Log received data
            \Log::info('Kamal Tec Sale Store - Products received:', ['products' => $request->products, 'quantities' => $request->quantities, 'prices' => $request->unit_prices]);
            
            if (!empty($request->products) && is_array($request->products)) {
                foreach ($request->products as $key => $product_id) {
                    if (!empty($product_id)) {
                        $product = Product::find($product_id);
                        if (!$product) {
                            throw new \Exception("Product not found: " . $product_id);
                        }
                        // Values are already unformatted from validation step
                        $qty = $request->quantities[$key] ?? 1;
                        $unit_price = $request->unit_prices[$key] ?? 0;
                        
                        if ($qty <= 0 || $unit_price <= 0) {
                            throw new \Exception("Invalid quantity or price for product: " . $product->name);
                        }
                        
                        $line_total = $qty * $unit_price;
                        $total_amount += $line_total;

                        $sale_lines[] = [
                            'product_id' => $product_id,
                            'sku_snapshot' => $product->sku ?? '',
                            'product_name_snapshot' => $product->name,
                            'qty' => $qty,
                            'unit_price' => $unit_price,
                            'line_total' => $line_total,
                            'imei_serial' => $request->imei_serials[$key] ?? null,
                        ];
                    }
                }
            }

            if (empty($sale_lines)) {
                throw new \Exception("Please add at least one product to the sale. No products were received.");
            }

            // Calculate commission
            $commission_amount = 0;
            $commission_value = $this->commonUtil->num_uf($request->commission_value ?? 0);
            if ($request->commission_type == 'percent') {
                $commission_amount = ($total_amount * $commission_value) / 100;
            } else {
                $commission_amount = $commission_value;
            }

            // Calculate paid and due amounts
            // Paid amount = commission amount (what Kamal Tec receives)
            // Due amount = total amount - commission amount (what customer owes to 3rd party)
            $paid_amount = $commission_amount;
            $due_amount = $total_amount - $commission_amount;

            // Create sale
            $sale = KamalTecSale::create([
                'business_id' => $business_id,
                'location_id' => $request->location_id ?: null,
                'contact_id' => $request->contact_id,
                'sale_date' => $sale_date,
                'invoice_no' => $invoice_no,
                'kt_invoice_no' => $request->kt_invoice_no,
                'status' => 'open',
                'total_amount' => $total_amount,
                'commission_type' => $request->commission_type,
                'commission_value' => $commission_value,
                'commission_amount' => $commission_amount,
                'paid_amount' => $paid_amount,
                'due_amount' => $due_amount,
                'notes' => $request->notes,
                'created_by' => $user_id,
            ]);

            // Create sale lines
            foreach ($sale_lines as $line) {
                $line['kamal_tec_sale_id'] = $sale->id;
                KamalTecSaleLine::create($line);
            }

            // Add initial payment if provided
            if (!empty($request->initial_payment_amount)) {
                $initial_payment = $this->commonUtil->num_uf($request->initial_payment_amount);
                if ($initial_payment > 0) {
                    KamalTecPayment::create([
                        'kamal_tec_sale_id' => $sale->id,
                        'paid_on' => $sale_date,
                        'amount' => $initial_payment,
                        'method' => $request->initial_payment_method ?? 'cash',
                        'note' => $request->initial_payment_note,
                    ]);

                    $sale->updatePaymentStatus();
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
            
            return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'])->with('status', $output);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage() ?: __('messages.something_went_wrong'),
            ];
            
            return redirect()->back()
                ->with('status', $output)
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $sale = KamalTecSale::where('business_id', $business_id)
            ->with(['contact', 'location', 'creator', 'saleLines.product', 'payments'])
            ->findOrFail($id);

        return view('kamal_tec_sale.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $sale = KamalTecSale::where('business_id', $business_id)
            ->with(['saleLines.product'])
            ->findOrFail($id);

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->pluck('name', 'id');

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.edit', compact('sale', 'business_locations', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        \Log::info('=== UPDATE METHOD CALLED ===', [
            'sale_id' => $id,
            'request_method' => $request->method(),
            'all_request_data' => $request->all(),
            'kt_invoice_no' => $request->kt_invoice_no
        ]);
        
        try {
            // Convert date format from datepicker to database format (YYYY-MM-DD)
            $sale_date = null;
            if (!empty($request->sale_date)) {
                try {
                    $date_str = trim($request->sale_date);
                    
                    // First try using the business date format
                    $date_format = session('business.date_format', 'd/m/Y');
                    try {
                        $sale_date = \Carbon\Carbon::createFromFormat($date_format, $date_str)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // If that fails, try common date formats
                        $formats_to_try = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                        $parsed = false;
                        
                        foreach ($formats_to_try as $format) {
                            try {
                                $sale_date = \Carbon\Carbon::createFromFormat($format, $date_str)->format('Y-m-d');
                                $parsed = true;
                                break;
                            } catch (\Exception $e2) {
                                continue;
                            }
                        }
                        
                        // Last resort: use Carbon's flexible parser
                        if (!$parsed) {
                            $sale_date = \Carbon\Carbon::parse($date_str)->format('Y-m-d');
                        }
                    }
                    
                    // Replace the request value with converted date
                    $request->merge(['sale_date' => $sale_date]);
                } catch (\Exception $e) {
                    \Log::error('Date conversion error: ' . $e->getMessage() . ' - Date: ' . $request->sale_date);
                    throw new \Exception('Invalid date format: ' . $request->sale_date . '. Please select a valid date from the calendar.');
                }
            }

            // Unformat numeric values before validation
            $unformatted_request = $request->all();
            
            if (!empty($request->quantities)) {
                foreach ($request->quantities as $key => $qty) {
                    $unformatted_request['quantities'][$key] = $this->commonUtil->num_uf($qty);
                }
            }
            
            if (!empty($request->unit_prices)) {
                foreach ($request->unit_prices as $key => $price) {
                    $unformatted_request['unit_prices'][$key] = $this->commonUtil->num_uf($price);
                }
            }
            
            if (!empty($request->commission_value)) {
                $unformatted_request['commission_value'] = $this->commonUtil->num_uf($request->commission_value);
            }
            
            // Replace request data with unformatted values
            $request->merge($unformatted_request);
            
            // Validate required fields (now with unformatted numbers)
            $validated = $request->validate([
                'contact_id' => 'required|exists:contacts,id',
                'sale_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
                'quantities.*' => 'required|numeric|min:0.01',
                'unit_prices.*' => 'required|numeric|min:0',
                'commission_type' => 'required|in:percent,fixed',
                'commission_value' => 'required|numeric|min:0',
                'kt_invoice_no' => 'nullable|string|max:255',
            ], [
                'contact_id.required' => 'Please select a customer',
                'sale_date.required' => 'Sale date is required',
                'sale_date.date' => 'The sale date is not a valid date.',
                'products.required' => 'Please add at least one product',
                'products.min' => 'Please add at least one product',
                'quantities.*.required' => 'Quantity is required for all products',
                'quantities.*.numeric' => 'Quantity must be a number',
                'quantities.*.min' => 'Quantity must be greater than 0',
                'unit_prices.*.required' => 'Unit price is required for all products',
                'unit_prices.*.numeric' => 'Unit price must be a number',
                'unit_prices.*.min' => 'Unit price must be 0 or greater',
                'commission_type.required' => 'Commission type is required',
                'commission_value.required' => 'Commission value is required',
                'commission_value.numeric' => 'Commission value must be a number',
                'commission_value.min' => 'Commission value must be 0 or greater',
            ]);

            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($id);

            // Calculate totals
            $total_amount = 0;
            $sale_lines = [];
            
            if (!empty($request->products)) {
                foreach ($request->products as $key => $product_id) {
                    if (!empty($product_id)) {
                        $product = Product::find($product_id);
                        if (!$product) {
                            throw new \Exception("Product not found: " . $product_id);
                        }
                        // Values are already unformatted from validation step
                        $qty = $request->quantities[$key] ?? 1;
                        $unit_price = $request->unit_prices[$key] ?? 0;
                        
                        if ($qty <= 0 || $unit_price <= 0) {
                            throw new \Exception("Invalid quantity or price for product: " . $product->name);
                        }
                        
                        $line_total = $qty * $unit_price;
                        $total_amount += $line_total;

                        $sale_lines[] = [
                            'product_id' => $product_id,
                            'sku_snapshot' => $product->sku,
                            'product_name_snapshot' => $product->name,
                            'qty' => $qty,
                            'unit_price' => $unit_price,
                            'line_total' => $line_total,
                            'imei_serial' => $request->imei_serials[$key] ?? null,
                        ];
                    }
                }
            }

            // Calculate commission
            $commission_amount = 0;
            $commission_value = $this->commonUtil->num_uf($request->commission_value ?? 0);
            if ($request->commission_type == 'percent') {
                $commission_amount = ($total_amount * $commission_value) / 100;
            } else {
                $commission_amount = $commission_value;
            }

            // Calculate paid amount from existing payments
            $total_paid = $sale->payments()->sum('amount');
            
            // Extract and clean KT Invoice Number
            $kt_invoice_no = !empty($request->kt_invoice_no) ? trim($request->kt_invoice_no) : null;
            if (empty($kt_invoice_no) || $kt_invoice_no === '') {
                $kt_invoice_no = null;
            }
            
            \Log::info('Updating Kamal Tec Sale - Before Save', [
                'sale_id' => $sale->id,
                'kt_invoice_no_request' => $request->kt_invoice_no,
                'kt_invoice_no_processed' => $kt_invoice_no,
                'kt_invoice_no_current' => $sale->kt_invoice_no,
                'total_amount' => $total_amount,
                'commission_amount' => $commission_amount,
                'total_paid' => $total_paid
            ]);
            
            // Update all sale fields - set KT Invoice Number first to ensure it's included
            $sale->kt_invoice_no = $kt_invoice_no;
            $sale->location_id = $request->location_id ?: null;
            $sale->contact_id = $request->contact_id;
            $sale->sale_date = $sale_date;
            $sale->total_amount = $total_amount;
            $sale->commission_type = $request->commission_type;
            $sale->commission_value = $commission_value;
            $sale->commission_amount = $commission_amount;
            $sale->paid_amount = $total_paid; // Sum of all payments
            $sale->due_amount = $total_amount - $commission_amount; // Customer's due to 3rd party
            $sale->notes = $request->notes ?? null;
            
            // Save the sale
            $saved = $sale->save();
            
            if (!$saved) {
                \Log::error('Failed to save Kamal Tec Sale', ['sale_id' => $sale->id]);
                throw new \Exception('Failed to save sale');
            }
            
            // Refresh to get the latest data from database
            $sale->refresh();
            
            \Log::info('Kamal Tec Sale updated successfully - After Save', [
                'sale_id' => $sale->id,
                'kt_invoice_no_saved' => $sale->kt_invoice_no,
                'kt_invoice_no_expected' => $kt_invoice_no,
                'saved' => $saved
            ]);

            // Delete existing lines and create new ones
            $sale->saleLines()->delete();
            foreach ($sale_lines as $line) {
                $line['kamal_tec_sale_id'] = $sale->id;
                KamalTecSaleLine::create($line);
            }

            // Recalculate payment status
            $sale->updatePaymentStatus();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
            
            return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'])->with('status', $output);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation failed in Kamal Tec Sale update', [
                'sale_id' => $id,
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("Kamal Tec Sale Update Error - File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            \Log::emergency("Kamal Tec Sale Update Error - Stack Trace: " . $e->getTraceAsString());
            \Log::emergency("Kamal Tec Sale Update Error - Request Data: " . json_encode($request->all()));

            $output = [
                'success' => 0,
                'msg' => $e->getMessage() ?: __('messages.something_went_wrong'),
            ];
            
            return redirect()->back()
                ->with('status', $output)
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($id);

            // Delete related records
            $sale->payments()->delete();
            $sale->saleLines()->delete();
            $sale->delete();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        // Return JSON response for AJAX requests, otherwise redirect
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($output);
        }

        return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'])->with('status', $output);
    }

    /**
     * Export all sales to Excel
     */
    public function export(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'contact_id' => $request->contact_id,
            'product_id' => $request->product_id,
            'status' => $request->status,
            'commission_type' => $request->commission_type,
        ];

        $filename = 'kamal_tec_sales_export_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new KamalTecSalesExport($business_id, $filters), $filename);
    }
}
