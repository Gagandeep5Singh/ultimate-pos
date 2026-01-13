<?php

namespace App\Http\Controllers;

use App\KamalTecCustomer;
use App\KamalTecSale;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class KamalTecCustomerController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of customers.
     */
    public function index()
    {
        if (request()->ajax()) {
            try {
                // Check if table exists
                if (!Schema::hasTable('kamal_tec_customers')) {
                    return response()->json([
                        'draw' => request()->get('draw', 1),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'error' => 'Customers table does not exist. Please run the migration first.'
                    ], 200);
                }
                
                $business_id = request()->session()->get('user.business_id');

                $customers = KamalTecCustomer::where('business_id', $business_id);

                // Search filter
                if (!empty(request()->search['value'])) {
                    $search = request()->search['value'];
                    $customers->where(function($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('nif', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%");
                    });
                }

                // Check if customer_id column exists (do this once, not in closure)
                $has_customer_id = Schema::hasColumn('kamal_tec_sales', 'customer_id');
                
                return DataTables::of($customers)
                    ->addColumn('full_name', function ($row) {
                        return $row->first_name . ' ' . $row->last_name;
                    })
                    ->addColumn('total_sales', function ($row) use ($has_customer_id) {
                        if ($has_customer_id) {
                            try {
                                return KamalTecSale::where('customer_id', $row->id)
                                    ->where('status', '!=', 'cancelled')
                                    ->count();
                            } catch (\Exception $e) {
                                return 0;
                            }
                        }
                        return 0;
                    })
                    ->addColumn('total_amount', function ($row) use ($has_customer_id) {
                        if ($has_customer_id) {
                            try {
                                $total = KamalTecSale::where('customer_id', $row->id)
                                    ->where('status', '!=', 'cancelled')
                                    ->sum('total_amount');
                                return '<span class="display_currency" data-currency_symbol="true">' . ($total ?? 0) . '</span>';
                            } catch (\Exception $e) {
                                return '<span class="display_currency" data-currency_symbol="true">0</span>';
                            }
                        }
                        return '<span class="display_currency" data-currency_symbol="true">0</span>';
                    })
                    ->addColumn('action', function ($row) {
                        $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        $html .= '<li><a href="' . route('kamal-tec-customers.show', [$row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                        $html .= '<li><a href="' . route('kamal-tec-customers.edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                        $html .= '<li><a href="' . route('kamal-tec-customers.destroy', [$row->id]) . '" class="delete-customer"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</a></li>';

                        $html .= '</ul></div>';

                        return $html;
                    })
                    ->editColumn('dob', function ($row) {
                        return $row->dob ? $this->commonUtil->format_date($row->dob) : '-';
                    })
                    ->rawColumns(['action', 'total_amount'])
                    ->make(true);
            } catch (\Exception $e) {
                \Log::error('Customer DataTable Error: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'draw' => request()->get('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Error loading data: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('kamal_tec_customer.index');
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('kamal_tec_customer.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'dob' => 'nullable',
                'nif' => 'nullable|string|max:50',
                'number' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'dob_country' => 'nullable|string|max:100',
                'address' => 'nullable|string',
            ]);

            // Convert date format
            $dob = null;
            if (!empty($request->dob)) {
                $date_format = session('business.date_format', 'd/m/Y');
                try {
                    $dob = \Carbon\Carbon::createFromFormat($date_format, $request->dob)->format('Y-m-d');
                } catch (\Exception $e) {
                    $dob = \Carbon\Carbon::parse($request->dob)->format('Y-m-d');
                }
            }

            $customer = KamalTecCustomer::create([
                'business_id' => $business_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $dob,
                'nif' => $request->nif,
                'number' => $request->number,
                'email' => $request->email,
                'dob_country' => $request->dob_country,
                'address' => $request->address,
                'created_by' => $user_id,
            ]);

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success')
            ];

            if ($request->ajax()) {
                return response()->json($output);
            }

            // If return_to is sale, redirect to sale create with customer selected
            if ($request->input('return_to') == 'sale' && $output['success'] == 1) {
                return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'create'])
                    ->with('status', $output)
                    ->with('selected_customer_id', $customer->id);
            }

            return redirect()->route('kamal-tec-customers.index')
                ->with('status', $output);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Create Customer Validation Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => 0,
                    'msg' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            \Log::error('Create Customer Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong') . ': ' . $e->getMessage()
            ];

            if ($request->ajax()) {
                return response()->json($output, 500);
            }

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $customer = KamalTecCustomer::where('business_id', $business_id)
            ->with(['sales' => function($query) {
                $query->where('status', '!=', 'cancelled')
                    ->orderBy('sale_date', 'desc');
            }])
            ->findOrFail($id);

        $sales = $customer->sales()
            ->where('status', '!=', 'cancelled')
            ->with('saleLines.product')
            ->get();

        return view('kamal_tec_customer.show', compact('customer', 'sales'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $customer = KamalTecCustomer::where('business_id', $business_id)->findOrFail($id);

        return view('kamal_tec_customer.edit', compact('customer'));
    }

    /**
     * Get customers for dropdown (AJAX)
     */
    public function getCustomers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $search = $request->get('search', '');

        $customers = KamalTecCustomer::where('business_id', $business_id);

        if (!empty($search)) {
            $customers->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhere('nif', 'like', "%{$search}%");
            });
        }
        
        $customers = $customers->select('id', 'first_name', 'last_name', 'email', 'number', 'nif')
            ->limit(50)
            ->get()
            ->map(function($customer) {
                $displayText = $customer->first_name . ' ' . $customer->last_name;
                $details = [];
                if ($customer->nif) {
                    $details[] = 'NIF: ' . $customer->nif;
                }
                if ($customer->number) {
                    $details[] = 'Tel: ' . $customer->number;
                }
                if ($customer->email) {
                    $details[] = $customer->email;
                }
                if (!empty($details)) {
                    $displayText .= ' (' . implode(', ', $details) . ')';
                }
                return [
                    'id' => $customer->id,
                    'text' => $displayText,
                ];
            });
        
        return response()->json($customers);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $customer = KamalTecCustomer::where('business_id', $business_id)->findOrFail($id);

            $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'dob' => 'nullable',
                'nif' => 'nullable|string|max:50',
                'number' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'dob_country' => 'nullable|string|max:100',
                'address' => 'nullable|string',
            ]);

            // Convert date format
            $dob = null;
            if (!empty($request->dob)) {
                $date_format = session('business.date_format', 'd/m/Y');
                try {
                    $dob = \Carbon\Carbon::createFromFormat($date_format, $request->dob)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        // Try other common formats
                        $formats_to_try = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                        $parsed = false;
                        
                        foreach ($formats_to_try as $format) {
                            try {
                                $dob = \Carbon\Carbon::createFromFormat($format, $request->dob)->format('Y-m-d');
                                $parsed = true;
                                break;
                            } catch (\Exception $e2) {
                                continue;
                            }
                        }
                        
                        // Last resort: use Carbon's flexible parser
                        if (!$parsed) {
                            $dob = \Carbon\Carbon::parse($request->dob)->format('Y-m-d');
                        }
                    } catch (\Exception $e3) {
                        \Log::error('Date parsing error: ' . $e3->getMessage());
                        // Leave dob as null if parsing fails
                    }
                }
            }

            // Update fields individually to avoid date casting issues
            $customer->first_name = $request->first_name;
            $customer->last_name = $request->last_name;
            $customer->dob = $dob;
            $customer->nif = $request->nif ?? null;
            $customer->number = $request->number ?? null;
            $customer->email = $request->email ?? null;
            $customer->dob_country = $request->dob_country ?? null;
            $customer->address = $request->address ?? null;
            $customer->save();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.updated_success')
            ];

            if ($request->ajax()) {
                return response()->json($output);
            }

            return redirect()->route('kamal-tec-customers.index')
                ->with('status', $output);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Update Customer Validation Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => 0,
                    'msg' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            \Log::error('Update Customer Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong') . ': ' . $e->getMessage()
            ];

            if ($request->ajax()) {
                return response()->json($output, 500);
            }

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $customer = KamalTecCustomer::where('business_id', $business_id)->findOrFail($id);

            // Check if customer has sales
            if ($customer->sales()->count() > 0) {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Cannot delete customer with existing sales'
                ]);
            }

            $customer->delete();

            return response()->json([
                'success' => 1,
                'msg' => __('lang_v1.deleted_success')
            ]);
        } catch (\Exception $e) {
            \Log::error('Delete Customer Error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }
}

