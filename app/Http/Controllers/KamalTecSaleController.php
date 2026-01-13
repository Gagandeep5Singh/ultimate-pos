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
                
                // Get status tab from request, default to 'pending' for AJAX requests
                $status_tab = request()->get('status_tab', 'pending');

                $sales = KamalTecSale::leftJoin('kamal_tec_customers AS ktc', 'kamal_tec_sales.customer_id', '=', 'ktc.id')
                    ->leftJoin('business_locations AS bl', 'kamal_tec_sales.location_id', '=', 'bl.id')
                    ->leftJoin('users AS u', 'kamal_tec_sales.created_by', '=', 'u.id')
                    ->where('kamal_tec_sales.business_id', $business_id)
                    ->select(
                        'kamal_tec_sales.*',
                        DB::raw("CONCAT(COALESCE(ktc.first_name, ''), ' ', COALESCE(ktc.last_name, '')) as customer_name"),
                        'bl.name as location_name',
                        DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as created_by_name"),
                        DB::raw("GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as due_commission")
                    );

            // Status tab filter - always filter by status_tab
            $sales->where('kamal_tec_sales.status', $status_tab);

            // Date range filter
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $sales->whereDate('kamal_tec_sales.sale_date', '>=', request()->start_date)
                    ->whereDate('kamal_tec_sales.sale_date', '<=', request()->end_date);
            }

            // Kamal Tec Customer filter
            if (!empty(request()->kamal_tec_customer_id)) {
                $sales->where('kamal_tec_sales.customer_id', request()->kamal_tec_customer_id);
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

            // Floa Ref filter
            if (!empty(request()->floa_ref)) {
                $sales->where('kamal_tec_sales.floa_ref', 'LIKE', '%' . request()->floa_ref . '%');
            }

            return DataTables::of($sales)
                ->addColumn('kt_invoice_no', function ($row) {
                    return $row->kt_invoice_no ?? '-';
                })
                ->addColumn('floa_ref', function ($row) {
                    return $row->floa_ref ?? '-';
                })
                ->filterColumn('customer_name', function($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(ktc.first_name, ''), ' ', COALESCE(ktc.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('invoice_no', function($query, $keyword) {
                    $query->whereRaw("kamal_tec_sales.invoice_no like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('kt_invoice_no', function($query, $keyword) {
                    $query->whereRaw("kamal_tec_sales.kt_invoice_no like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('floa_ref', function($query, $keyword) {
                    $query->whereRaw("kamal_tec_sales.floa_ref like ?", ["%{$keyword}%"]);
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        return action([\App\Http\Controllers\KamalTecSaleController::class, 'show'], [$row->id]);
                    }
                ])
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

                    $html .= '<li><a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\KamalTecSaleController::class, 'editFloaRef'], [$row->id]) . '" data-container=".view_modal"><i class="fa fa-edit"></i> Update Floa Ref & KT Invoice No</a></li>';

                    // Status change options
                    $html .= '<li class="divider"></li>';
                    if ($row->status == 'pending') {
                        // From pending, can only change to cancelled
                        $html .= '<li><a href="#" class="change-status" data-id="' . $row->id . '" data-status="cancelled"><i class="fa fa-times-circle"></i> Change to Cancelled</a></li>';
                    } else {
                        // From other statuses, can change to any status
                        if ($row->status != 'pending') {
                            $html .= '<li><a href="#" class="change-status" data-id="' . $row->id . '" data-status="pending"><i class="fa fa-hourglass-half"></i> Change to Pending</a></li>';
                        }
                        // Only show "Change to Open" if Floa Ref exists
                        if ($row->status != 'open' && !empty($row->floa_ref) && $row->floa_ref != '-') {
                            $html .= '<li><a href="#" class="change-status" data-id="' . $row->id . '" data-status="open"><i class="fa fa-clock-o"></i> Change to Open</a></li>';
                        }
                        if ($row->status != 'closed') {
                            $html .= '<li><a href="#" class="change-status" data-id="' . $row->id . '" data-status="closed"><i class="fa fa-check-circle"></i> Change to Closed</a></li>';
                        }
                        if ($row->status != 'cancelled') {
                            $html .= '<li><a href="#" class="change-status" data-id="' . $row->id . '" data-status="cancelled"><i class="fa fa-times-circle"></i> Change to Cancelled</a></li>';
                        }
                    }

                    $html .= '<li class="divider"></li>';
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
                        'pending' => '<span class="label label-info change-status-badge" data-id="' . $row->id . '" data-status="pending" style="cursor: pointer;" title="Click to change status"><i class="fa fa-hourglass-half"></i> ' . __('lang_v1.pending') . '</span>',
                        'open' => '<span class="label label-warning change-status-badge" data-id="' . $row->id . '" data-status="open" style="cursor: pointer;" title="Click to change status"><i class="fa fa-clock-o"></i> ' . __('lang_v1.open') . '</span>',
                        'closed' => '<span class="label label-success change-status-badge" data-id="' . $row->id . '" data-status="closed" style="cursor: pointer;" title="Click to change status"><i class="fa fa-check-circle"></i> ' . __('lang_v1.closed') . '</span>',
                        'cancelled' => '<span class="label label-danger change-status-badge" data-id="' . $row->id . '" data-status="cancelled" style="cursor: pointer;" title="Click to change status"><i class="fa fa-times-circle"></i> ' . __('lang_v1.cancelled') . '</span>',
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
        
        // Get status tab from request, default to 'open'
        $status_tab = request()->get('status_tab', 'pending');
        
        // Get Kamal Tec Customers for filter
        $kamal_tec_customers = \App\KamalTecCustomer::where('business_id', $business_id)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->mapWithKeys(function ($customer) {
                return [$customer->id => $customer->first_name . ' ' . $customer->last_name];
            });

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.index', compact('kamal_tec_customers', 'products', 'status_tab'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        // Get Kamal Tec Customers
        $kamal_tec_customers = \App\KamalTecCustomer::where('business_id', $business_id)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->mapWithKeys(function ($customer) {
                return [$customer->id => $customer->first_name . ' ' . $customer->last_name];
            });

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.create', compact('business_locations', 'kamal_tec_customers', 'products'));
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

            // Unformat numeric values before validation
            $unformatted_data = [];
            
            if (!empty($request->quantities)) {
                foreach ($request->quantities as $key => $qty) {
                    $unformatted_data['quantities'][$key] = $this->commonUtil->num_uf($qty);
                }
            }
            
            if (!empty($request->unit_prices)) {
                foreach ($request->unit_prices as $key => $price) {
                    $unformatted_data['unit_prices'][$key] = $this->commonUtil->num_uf($price);
                }
            }
            
            if (!empty($request->commission_value)) {
                $unformatted_data['commission_value'] = $this->commonUtil->num_uf($request->commission_value);
            }
            
            // Merge unformatted values into request
            $request->merge($unformatted_data);
            
            // Debug: Log all request data
            \Log::info('Kamal Tec Sale Store Request:', [
                'all_data' => $request->all(),
                'products' => $request->products,
                'quantities' => $request->quantities,
                'unit_prices' => $request->unit_prices,
                'original_date' => $request->input('sale_date'),
                'converted_date' => $sale_date
            ]);

            // Validate required fields (now with unformatted numbers)
            $validated = $request->validate([
                'kamal_tec_customer_id' => 'required|exists:kamal_tec_customers,id',
                'sale_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
                'quantities.*' => 'required|numeric|min:0.01',
                'unit_prices.*' => 'required|numeric|min:0',
                'commission_type' => 'required|in:percent,fixed',
                'commission_value' => 'required|numeric|min:0',
                'kt_invoice_no' => 'nullable|string|max:255',
            ], [
                'kamal_tec_customer_id.required' => 'Please select a Kamal Tec customer',
                'kamal_tec_customer_id.exists' => 'Selected customer does not exist',
                'sale_date.required' => 'Sale date is required',
                'sale_date.date' => 'The sale date is not a valid date.',
                'products.required' => 'Please add at least one product',
                'products.min' => 'Please add at least one product',
                'products.*.required' => 'All products must be valid',
                'quantities.*.required' => 'Please enter quantity for all products',
                'quantities.*.numeric' => 'Quantity must be a number',
                'quantities.*.min' => 'Quantity must be greater than 0',
                'unit_prices.*.required' => 'Please enter price for all products',
                'unit_prices.*.numeric' => 'Unit price must be a number',
                'unit_prices.*.min' => 'Unit price must be 0 or greater',
                'commission_type.required' => 'Commission type is required',
                'commission_value.required' => 'Commission value is required',
                'commission_value.numeric' => 'Commission value must be a number',
                'commission_value.min' => 'Commission value must be 0 or greater',
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

            // Calculate commission (value is already unformatted from validation step)
            $commission_amount = 0;
            $commission_value = $request->commission_value ?? 0;
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
                'contact_id' => null, // No longer using regular contacts
                'customer_id' => $request->kamal_tec_customer_id,
                'sale_date' => $sale_date,
                'invoice_no' => $invoice_no,
                'kt_invoice_no' => $request->kt_invoice_no,
                'floa_ref' => $request->floa_ref,
                'status' => 'pending',
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
            
            // Redirect to pending tab (where new sales should appear)
            return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'pending'])->with('status', $output);
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
            ->with(['contact', 'customer', 'location', 'creator', 'saleLines.product', 'payments'])
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

        // Get Kamal Tec Customers
        $kamal_tec_customers = \App\KamalTecCustomer::where('business_id', $business_id)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->mapWithKeys(function ($customer) {
                return [$customer->id => $customer->first_name . ' ' . $customer->last_name];
            });

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.edit', compact('sale', 'business_locations', 'kamal_tec_customers', 'products'));
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
                'kamal_tec_customer_id' => 'required|exists:kamal_tec_customers,id',
                'sale_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*' => 'required|exists:products,id',
                'quantities.*' => 'required|numeric|min:0.01',
                'unit_prices.*' => 'required|numeric|min:0',
                'commission_type' => 'required|in:percent,fixed',
                'commission_value' => 'required|numeric|min:0',
                'kt_invoice_no' => 'nullable|string|max:255',
            ], [
                'kamal_tec_customer_id.required' => 'Please select a Kamal Tec customer',
                'kamal_tec_customer_id.exists' => 'Selected customer does not exist',
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
            $sale->customer_id = $request->kamal_tec_customer_id;
            $sale->contact_id = null; // No longer using regular contacts
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
            
            // Redirect to appropriate tab based on sale status
            $status_tab = $sale->status;
            return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => $status_tab])->with('status', $output);
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

        // Redirect to pending tab (default after deletion)
        return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'pending'])->with('status', $output);
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

    /**
     * Update sale status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($id);

            $request->validate([
                'status' => 'required|in:pending,open,closed,cancelled'
            ]);

            // Business logic: Cannot set to 'open' if Floa Ref is empty
            // Floa Ref is required for 'open' status (installment must be done)
            if ($request->status == 'open' && (empty($sale->floa_ref) || $sale->floa_ref == '-')) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Cannot change to Open status. Floa Ref is required. Please update Floa Ref first.'
                ], 400);
            }

            $sale->status = $request->status;
            $sale->save();

            return response()->json([
                'success' => true,
                'msg' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Show form to edit Floa Ref
     */
    public function editFloaRef($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($id);

        return view('kamal_tec_sale.partials.edit_floa_ref')
            ->with(compact('sale'));
    }

    /**
     * Update Floa Ref and KT Invoice No
     */
    public function updateFloaRef(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($id);

            $request->validate([
                'floa_ref' => 'nullable|string|max:255',
                'kt_invoice_no' => 'nullable|string|max:255'
            ]);

            // Update both fields (can be empty/null)
            $sale->floa_ref = $request->input('floa_ref');
            $sale->kt_invoice_no = $request->input('kt_invoice_no');
            
            // Auto-change status to 'open' when Floa Ref is updated (if currently pending and Floa Ref is provided)
            // Only change to 'open' if Floa Ref is actually provided (not empty)
            if ($sale->status == 'pending' && !empty(trim($request->input('floa_ref', '')))) {
                $sale->status = 'open';
            } elseif ($sale->status == 'open' && empty(trim($request->input('floa_ref', '')))) {
                // If Floa Ref is removed/cleared and status is open, revert to pending
                $sale->status = 'pending';
            }
            
            $sale->save();

            $msg = 'Floa Ref and KT Invoice No updated successfully';

            return response()->json([
                'success' => 1,
                'msg' => $msg
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Floa Ref & KT Invoice No Error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'msg' => 'Something went wrong'
            ], 500);
        }
    }
}
