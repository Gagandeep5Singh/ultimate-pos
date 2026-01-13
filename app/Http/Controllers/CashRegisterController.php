<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\CashRegister;
use App\Utils\CashRegisterUtil;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $cashRegisterUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  CashRegisterUtil  $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil, ModuleUtil $moduleUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Prompt the user to choose a location immediately after login.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLocationSelection(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $selected_location = $request->session()->get('user.location_id');

        return view('cash_register.select_location')->with(compact('business_locations', 'selected_location'));
    }

    /**
     * Handle the login location selection and redirect to POS or register creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLocationSelection(Request $request)
    {
        $location_id = $request->input('location_id');
        $business_id = $request->session()->get('user.business_id');

        // Validate that location is valid and accessible for this user
        $location = \App\BusinessLocation::where('business_id', $business_id)
                    ->where('id', $location_id)
                    ->first();

        if (empty($location) || ! \App\User::can_access_this_location($location_id, $business_id)) {
            return redirect()->back()
                ->with('status', ['success' => 0, 'msg' => __('lang_v1.invalid_location')])
                ->withInput();
        }

        // Persist selection to session for downstream POS/register checks
        $request->session()->put('user.location_id', $location_id);

        $redirect_to = $request->session()->pull('post_login_redirect', action([\App\Http\Controllers\HomeController::class, 'index']));

        // If a register is already open for this location, skip cash-in-hand prompt
        if ($this->cashRegisterUtil->countOpenedRegister($location_id) > 0) {
            return redirect($redirect_to);
        }

        // No open register: redirect to create screen with location locked in
        return redirect()->action(
            [\App\Http\Controllers\CashRegisterController::class, 'create'],
            ['location_id' => $location_id, 'redirect_to' => $redirect_to]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //like:repair
        $sub_type = request()->get('sub_type');
        
        $location_id = request()->get('location_id');
        $redirect_to = request()->get('redirect_to');
        
        // If no location_id passed, try to get from session or get first permitted location
        if (empty($location_id)) {
            $location_id = request()->session()->get('user.location_id');
            
            // If still no location, get first permitted location
            if (empty($location_id)) {
                $business_id = request()->session()->get('user.business_id');
                $permitted_locations = auth()->user()->permitted_locations($business_id);
                if ($permitted_locations != 'all' && !empty($permitted_locations)) {
                    $location_id = $permitted_locations[0];
                } else {
                    // Get first location for business
                    $first_location = \App\BusinessLocation::where('business_id', $business_id)->first();
                    $location_id = $first_location ? $first_location->id : null;
                }
            }
        }

        //Check if there is an open register for THIS SPECIFIC location only
        // Each location can have its own independent register
        if (!empty($location_id)) {
            $open_register_count = $this->cashRegisterUtil->countOpenedRegister($location_id);
            if ($open_register_count > 0) {
                // Register already open for this location, redirect to POS
                return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
            }
        }
        
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('cash_register.create')->with(compact('business_locations', 'sub_type', 'location_id', 'redirect_to'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //like:repair
        $sub_type = request()->get('sub_type');

        try {
            // Get location_id from form - this is the user's selected location
            $location_id = $request->input('location_id');
            $redirect_to = $request->input('redirect_to');
            
            // Validate that location_id is provided
            if (empty($location_id)) {
                return redirect()->back()
                    ->with('status', ['success' => 0, 'msg' => __('lang_v1.please_select_location')])
                    ->withInput();
            }
            
            $business_id = $request->session()->get('user.business_id');
            
            // Verify the location belongs to this business and user has access
            $location = \App\BusinessLocation::where('business_id', $business_id)
                            ->where('id', $location_id)
                            ->first();
            
            if (empty($location)) {
                return redirect()->back()
                    ->with('status', ['success' => 0, 'msg' => __('lang_v1.invalid_location')])
                    ->withInput();
            }

            // Persist location to session for subsequent requests
            $request->session()->put('user.location_id', $location_id);
            
            // Check if there's already an open register for THIS SPECIFIC location
            // Each location should have its own independent register
            $existing_register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open')
                                ->where('location_id', $location_id)
                                ->first();
            
            if ($existing_register) {
                // Register already open for this location, just redirect to POS
                if (! empty($redirect_to)) {
                    return redirect($redirect_to);
                }

                return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
            }
            
            $initial_amount = 0;
            if (! empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');

            // Create shared register for location (first person to login opens it)
            $register = CashRegister::create([
                'business_id' => $business_id,
                'user_id' => $user_id, // Store who opened it
                'status' => 'open',
                'location_id' => $location_id, // Use the location_id from form
                'created_at' => \Carbon::now()->format('Y-m-d H:i:00'),
            ]);
            if (! empty($initial_amount)) {
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial',
                ]);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }

        if (! empty($redirect_to)) {
            return redirect($redirect_to);
        }

        return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = ! empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);

        return view('cash_register.register_details')
                    ->with(compact('register_details', 'details', 'payment_types', 'close_time'));
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails()
    {
        if (! auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $location_id = request()->session()->get('user.location_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails(null, $location_id);

        // Get user_id from register (who opened it) for transaction details
        $user_id = $register_details->user_id ?? auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        // Get all transactions for this register (all users at this location)
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled, $register_details->location_id);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        return view('cash_register.register_details')
                ->with(compact('register_details', 'details', 'payment_types', 'close_time'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister($id = null)
    {
        // FIX: Catch any errors that occur before try block (like auth issues)
        try {
            if (! auth()->check()) {
                abort(401, 'Unauthorized');
            }
            
            if (! auth()->user()->can('close_cash_register')) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = request()->session()->get('user.business_id');
            $location_id = request()->session()->get('user.location_id');
            
            // Get register details - if no ID provided, get by location (shared register)
            // FIX: Wrap in try-catch to handle database errors gracefully
            try {
                $register_details = $this->cashRegisterUtil->getRegisterDetails($id, $location_id);
            } catch (\Exception $db_error) {
                \Log::error('Database error in getRegisterDetails: ' . $db_error->getMessage());
                throw $db_error; // Re-throw to be caught by outer catch
            }

            // FIX: Check if register exists, if not return error HTML for modal
            if (empty($register_details)) {
                $error_html = '<div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="modal-title">' . __('cash_register.close_register') . '</h3>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <strong>' . __('cash_register.no_register_found') . '</strong>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">' . __('messages.close') . '</button>
                        </div>
                    </div>
                </div>';
                return response($error_html);
            }

            $user_id = $register_details->user_id ?? auth()->user()->id;
            // Handle both object and array access for open_time
            $open_time = is_object($register_details) ? ($register_details->open_time ?? \Carbon::now()->toDateTimeString()) : ($register_details['open_time'] ?? \Carbon::now()->toDateTimeString());
            $close_time = \Carbon::now()->toDateTimeString();

            // FIX: Wrap module check in try-catch to prevent database errors from breaking the modal
            try {
                $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
            } catch (\Exception $e) {
                \Log::warning('Error checking types_of_service module: ' . $e->getMessage());
                $is_types_of_service_enabled = false;
            }

            // Get all transactions for this register (all users at this location)
            $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled, $register_details->location_id ?? $location_id);

            // FIX: Wrap payment_types and pos_settings in try-catch
            try {
                $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id ?? $location_id, true, $business_id);
            } catch (\Exception $e) {
                \Log::warning('Error getting payment types: ' . $e->getMessage());
                $payment_types = [];
            }

            try {
                $pos_settings = ! empty(request()->session()->get('business.pos_settings')) ? json_decode(request()->session()->get('business.pos_settings'), true) : [];
            } catch (\Exception $e) {
                \Log::warning('Error getting POS settings: ' . $e->getMessage());
                $pos_settings = [];
            }

            // FIX: Wrap view rendering in try-catch
            try {
                return view('cash_register.close_register_modal')
                            ->with(compact('register_details', 'details', 'payment_types', 'pos_settings'));
            } catch (\Exception $view_error) {
                \Log::error('Error rendering close_register_modal view: ' . $view_error->getMessage());
                throw $view_error; // Re-throw to be caught by outer catch
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            \Log::emergency('Stack trace: '.$e->getTraceAsString());
            
            // Show user-friendly error message
            $error_message = 'Error loading register details. Please check the logs for details.';
            if (config('app.debug')) {
                $error_message = 'Error: ' . htmlspecialchars($e->getMessage()) . ' in ' . $e->getFile() . ':' . $e->getLine();
            }
            
            $error_html = '<div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h3 class="modal-title">Error</h3>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>' . $error_message . '</strong>
                            <br><br>
                            <small>If this error persists, please check your database connection and application logs.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>';
            return response($error_html, 200); // Return 200 so modal can display the error
        }
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        if (! auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = ['success' => 0,
                    'msg' => 'Feature disabled in demo!!',
                ];

                return redirect()->action([\App\Http\Controllers\HomeController::class, 'index'])->with('status', $output);
            }

            $input = $request->only(['closing_amount', 'total_card_payment_closed', 'closing_note']);
$input['total_card_payment_closed'] = $this->cashRegisterUtil->num_uf($request->input('total_card_payment_closed'));
            $input['closing_amount'] = $this->cashRegisterUtil->num_uf($input['closing_amount']);
            $register_id = $request->input('register_id');
            $location_id = $request->input('location_id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';
            $input['denominations'] = ! empty(request()->input('denominations')) ? json_encode(request()->input('denominations')) : null;

            // Close register by ID or by location (anyone can close the shared register)
            $register_closed = false;
            if (!empty($register_id)) {
                $register_closed = CashRegister::where('id', $register_id)
                                ->where('status', 'open')
                                ->update($input);
            } elseif (!empty($location_id)) {
                $business_id = request()->session()->get('user.business_id');
                $register_closed = CashRegister::where('business_id', $business_id)
                                ->where('location_id', $location_id)
                                ->where('status', 'open')
                                ->update($input);
            }
            
            $output = ['success' => 1,
                'msg' => __('cash_register.close_success'),
            ];
            
            // After closing register, redirect to home (user can open new register from POS if needed)
            // This prevents staying on POS screen with no open register
            return redirect()->action([\App\Http\Controllers\HomeController::class, 'index'])->with('status', $output);
            
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            
            return redirect()->action([\App\Http\Controllers\HomeController::class, 'index'])->with('status', $output);
        }
    }
}
