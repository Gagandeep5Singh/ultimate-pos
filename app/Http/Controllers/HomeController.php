<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Charts\CommonChart;
use App\Currency;
use App\Media;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class HomeController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;
    protected $productUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil,
        ProductUtil $productUtil,
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->user_type == 'user_customer') {
            return redirect()->action([\Modules\Crm\Http\Controllers\DashboardController::class, 'index']);
        }

        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (! auth()->user()->can('dashboard.data')) {
            return view('home.index');
        }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        //ensure start date starts from at least 30 days before to get sells last 30 days
        $least_30_days = \Carbon::parse($fy['start'])->subDays(30)->format('Y-m-d');

        //get all sells
        $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $least_30_days, $fy['end']);

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        //Chart for sells last 30 days
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $total_sell_on_date = $sells_this_fy->where('date', $date)->sum('total_sells');

            if (! empty($total_sell_on_date)) {
                $all_sell_values[] = (float) $total_sell_on_date;
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Group sells by location
        $location_sells = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $total_sell_on_date_location = $sells_this_fy->where('date', $date)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_on_date_location)) {
                    $values[] = (float) $total_sell_on_date_location;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = new CommonChart;

        $sells_chart_1->labels($labels)
                        ->options($this->__chartOptions(__(
                            'home.total_sells',
                            ['currency' => $currency->code]
                            )));

        if (! empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }

        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), 'line', $all_sell_values);
        }

        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                            ->format('M-Y');
            $date = strtotime('+1 month', $date);

            $total_sell_in_month_year = $sells_this_fy->where('yearmonth', $month_year)->sum('total_sells');

            if (! empty($total_sell_in_month_year)) {
                $values[] = (float) $total_sell_in_month_year;
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);

        $fy_sells_by_location_data = [];

        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $total_sell_in_month_year_location = $sells_this_fy->where('yearmonth', $month)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_in_month_year_location)) {
                    $values_data[] = (float) $total_sell_in_month_year_location;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }

        $sells_chart_2 = new CommonChart;
        $sells_chart_2->labels($labels)
                    ->options($this->__chartOptions(__(
                        'home.total_sells',
                        ['currency' => $currency->code]
                            )));
        if (! empty($fy_sells_by_location_data)) {
            foreach ($fy_sells_by_location_data as $location_sell) {
                $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        }

        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (! empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        $common_settings = ! empty(session('business.common_settings')) ? session('business.common_settings') : [];

        // Get default location from session (selected at login)
        $default_location_id = request()->session()->get('user.location_id');
        if (empty($default_location_id) || !isset($all_locations[$default_location_id])) {
            $default_location_id = null;
        }

        return view('home.index', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin', 'default_location_id'));
    }

    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            // get user id parameter
            $created_by = request()->user_id;

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id, $created_by);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id, $created_by);

            $total_ledger_discount = $this->transactionUtil->getTotalLedgerDiscount($business_id, $start, $end);

            $purchase_details['purchase_due'] = $purchase_details['purchase_due'] - $total_ledger_discount['total_purchase_discount'];

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense',
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id,
                $created_by
            );

            $total_purchase_inc_tax = ! empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase_inc_tax;
            $output['total_purchase_return'] = $total_purchase_return_inc_tax;
            $output['total_purchase_return_paid'] = $this->transactionUtil->getTotalPurchaseReturnPaid($business_id, $start, $end, $location_id);

            $total_sell_inc_tax = ! empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = ! empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);

            $output['total_sell'] = $total_sell_inc_tax;
            $output['total_sell_return'] = $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'] - $total_ledger_discount['total_sell_discount'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            //NET = TOTAL SALES - INVOICE DUE - EXPENSE
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];
          
            // Get totals per payment method
            // IMPORTANT: Match the Sell Payment Report calculation exactly
            // The report includes change returns (is_return=1) as negative amounts
            // Formula: SUM(IF(is_return=1, -amount, amount)) for sales - SUM(amount) for sell returns
            
            // Get sales payments including change returns (is_return=1 treated as negative)
            $payment_method_totals = DB::table('transactions as t')
                ->join('transaction_payments as tp', 'tp.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->whereBetween('t.transaction_date', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->when($location_id, function ($q) use ($location_id) {
                    $q->where('t.location_id', $location_id);
                })
                ->select('tp.method', DB::raw('SUM(IF(tp.is_return = 1, -1 * tp.amount, tp.amount)) as total'))
                ->groupBy('tp.method')
                ->get();

            // Get refund payments from sell returns (these are always positive in DB, but should be subtracted)
            $refund_method_totals = DB::table('transactions as t')
                ->join('transaction_payments as tp', 'tp.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell_return')
                ->where('t.status', 'final')
                ->whereBetween('t.transaction_date', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->when($location_id, function ($q) use ($location_id) {
                    $q->where('t.location_id', $location_id);
                })
                ->select('tp.method', DB::raw('SUM(tp.amount) as total'))
                ->groupBy('tp.method')
                ->get();

            $pm = $payment_method_totals->pluck('total', 'method');
            $rm = $refund_method_totals->pluck('total', 'method');

            // Calculate net payment method totals: (sales with change returns) - (sell return refunds)
            // This matches the Sell Payment Report total calculation
            $output['payment_methods'] = [
                'cash'          => (float) (($pm['cash'] ?? 0) - ($rm['cash'] ?? 0)),
                'card'          => (float) (($pm['card'] ?? 0) - ($rm['card'] ?? 0)),
                'bank_transfer' => (float) (($pm['bank_transfer'] ?? 0) - ($rm['bank_transfer'] ?? 0)), // built-in method
                'mbway'         => (float) (($pm['custom_pay_1'] ?? 0) - ($rm['custom_pay_1'] ?? 0)),   // Custom Payment 1 = MbWay
                'klarna'        => (float) (($pm['custom_pay_6'] ?? 0) - ($rm['custom_pay_6'] ?? 0)),   // Custom Payment 6 = Klarna
            ];

            // Calculate GROSS PROFIT from actual product cost price vs selling price difference
            // This calculates: (Selling Price - Cost Price) for each product sold
            $permitted_locations = auth()->user()->permitted_locations();
            $gross_profit = $this->transactionUtil->getGrossProfit(
                $business_id,
                $start,
                $end,
                $location_id,
                $created_by,
                $permitted_locations
            );
            
            // Ensure gross profit is a float value
            $gross_profit = !empty($gross_profit) ? (float) $gross_profit : 0;
            $output['gross_profit'] = $gross_profit;

            // NET PROFIT = Gross Profit – Expense
            $expense = $output['total_expense'];
            $net_profit = $gross_profit - $expense;
            $output['net_profit'] = $net_profit;

            // Klarna fees: 4.99% + 0.35€ per transaction
            $klarna_percent = 4.99; // Klarna % fee
            $klarna_fixed   = 0.35; // Klarna fixed fee per transaction

           // Klarna total volume
$klarna_total = DB::table('transaction_payments as tp')
    ->join('transactions as t', 'tp.transaction_id', '=', 't.id')
    ->where('t.business_id', $business_id)
    ->where('t.type', 'sell')
    ->where('tp.method', 'custom_pay_6') // same key as in mapping
    ->whereBetween('t.transaction_date', [$start . ' 00:00:00', $end . ' 23:59:59'])
    ->when($location_id, function ($q) use ($location_id) {
        $q->where('t.location_id', $location_id);
    })
    ->sum('tp.amount');

// Klarna transactions count
$klarna_count = DB::table('transaction_payments as tp')
    ->join('transactions as t', 'tp.transaction_id', '=', 't.id')
    ->where('t.business_id', $business_id)
    ->where('t.type', 'sell')
    ->where('tp.method', 'custom_pay_6')
    ->whereBetween('t.transaction_date', [$start . ' 00:00:00', $end . ' 23:59:59'])
    ->when($location_id, function ($q) use ($location_id) {
        $q->where('t.location_id', $location_id);
    })
    ->count();

            // Calculate Klarna fees: (Klarna Total * 4.99%) + (0.35€ * Number of Transactions)
            $klarna_fees = ($klarna_total * ($klarna_percent / 100)) + ($klarna_fixed * $klarna_count);

            $output['klarna_fees'] = $klarna_fees;

            // Net profit after Klarna fees
            $output['net_after_klarna'] = $net_profit - $klarna_fees;

            // Calculate current month daily sales and projected sales
            $today = \Carbon\Carbon::now();
            $currentMonthStart = $today->copy()->startOfMonth();
            $currentMonthEnd = $today->copy()->endOfMonth();
            $currentDay = $today->day;
            $daysInMonth = $today->daysInMonth;
            $remainingDays = $daysInMonth - $currentDay;
            
            // Get daily sales for current month (actual sales up to today)
            $daily_sales = [];
            $daily_labels = [];
            $projected_sales = [];
            
            // Get sales for current month with location filter
            $currentMonthSellsQuery = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereBetween('transaction_date', [
                    $currentMonthStart->format('Y-m-d') . ' 00:00:00',
                    $today->format('Y-m-d') . ' 23:59:59'
                ]);
            
            if ($location_id) {
                $currentMonthSellsQuery->where('location_id', $location_id);
            }
            
            $currentMonthSells = $currentMonthSellsQuery->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-%d') as date"),
                DB::raw('SUM(final_total) as total_sells')
            )
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m-%d')"))
            ->get()
            ->keyBy('date');
            
            // Calculate actual daily sales for current month
            $totalCurrentMonthSales = 0;
            for ($day = 1; $day <= $currentDay; $day++) {
                $date = $currentMonthStart->copy()->addDays($day - 1)->format('Y-m-d');
                $daily_labels[] = $day . ' ' . $currentMonthStart->format('M');
                
                $daySales = 0;
                if ($currentMonthSells->has($date)) {
                    $daySales = (float) $currentMonthSells->get($date)->total_sells;
                }
                $daily_sales[] = $daySales;
                $totalCurrentMonthSales += $daySales;
            }
            
            // Calculate average daily sale for current month
            $avg_daily_sale = $currentDay > 0 ? ($totalCurrentMonthSales / $currentDay) : 0;
            
            // Calculate projected sales for remaining days
            $projectedTotal = $totalCurrentMonthSales; // Start with actual sales
            for ($day = $currentDay + 1; $day <= $daysInMonth; $day++) {
                $daily_labels[] = $day . ' ' . $currentMonthStart->format('M') . ' (Proj)';
                $projectedDaySale = $avg_daily_sale;
                $projected_sales[] = $projectedDaySale;
                $projectedTotal += $projectedDaySale;
            }
            
            // Also calculate for the selected period (for backward compatibility)
            $startCarbon = \Carbon\Carbon::parse($start);
            $endCarbon = \Carbon\Carbon::parse($end);
            $days = $startCarbon->diffInDays($endCarbon) + 1;
            $avg_daily_sale_period = $days > 0 ? ($output['total_sell'] / $days) : 0;
            $days_in_month = $startCarbon->daysInMonth;
            $projected_month_sale = $avg_daily_sale_period * $days_in_month;

            $output['avg_daily_sale'] = (float) $avg_daily_sale_period;
            $output['projected_month_sale'] = (float) $projected_month_sale;
            
            // Add current month projection data
            $output['current_month_daily_sales'] = $daily_sales;
            $output['current_month_projected_sales'] = $projected_sales;
            $output['current_month_labels'] = $daily_labels;
            $output['current_month_total_actual'] = (float) $totalCurrentMonthSales;
            $output['current_month_total_projected'] = (float) $projectedTotal;
            $output['current_month_avg_daily'] = (float) $avg_daily_sale;

            // Calculate yearly monthly sales and projection
            $currentYear = $today->year;
            $currentMonth = $today->month;
            $yearlyLabels = [];
            $yearlyActualSales = [];
            $yearlyProjectedSales = [];
            $lastYearSales = [];
            
            // Get sales for current year grouped by month
            $yearlySellsQuery = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereYear('transaction_date', $currentYear);
            
            if ($location_id) {
                $yearlySellsQuery->where('location_id', $location_id);
            }
            
            $yearlySells = $yearlySellsQuery->select(
                DB::raw("MONTH(transaction_date) as month"),
                DB::raw('SUM(final_total) as total_sells')
            )
            ->groupBy(DB::raw("MONTH(transaction_date)"))
            ->get()
            ->keyBy('month');
            
            // Get sales for last year grouped by month (for comparison)
            $lastYearSellsQuery = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereYear('transaction_date', $currentYear - 1);
            
            if ($location_id) {
                $lastYearSellsQuery->where('location_id', $location_id);
            }
            
            $lastYearSells = $lastYearSellsQuery->select(
                DB::raw("MONTH(transaction_date) as month"),
                DB::raw('SUM(final_total) as total_sells')
            )
            ->groupBy(DB::raw("MONTH(transaction_date)"))
            ->get()
            ->keyBy('month');
            
            // Calculate total sales for completed months this year
            $totalYearlySales = 0;
            $completedMonths = 0;
            
            // Calculate growth factor: compare current year's average to last year's average
            $lastYearTotal = 0;
            $lastYearMonthsWithData = 0;
            foreach ($lastYearSells as $monthData) {
                $lastYearTotal += (float) $monthData->total_sells;
                $lastYearMonthsWithData++;
            }
            $lastYearAvg = $lastYearMonthsWithData > 0 ? ($lastYearTotal / $lastYearMonthsWithData) : 0;
            
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            for ($month = 1; $month <= 12; $month++) {
                $monthName = $monthNames[$month - 1];
                $yearlyLabels[] = $monthName;
                
                if ($month < $currentMonth) {
                    // Past month - actual sales
                    $monthSales = $yearlySells->has($month) ? (float) $yearlySells->get($month)->total_sells : 0;
                    $yearlyActualSales[] = $monthSales;
                    $yearlyProjectedSales[] = null;
                    $totalYearlySales += $monthSales;
                    $completedMonths++;
                } elseif ($month == $currentMonth) {
                    // Current month - actual sales (up to today)
                    $monthSales = $yearlySells->has($month) ? (float) $yearlySells->get($month)->total_sells : 0;
                    $yearlyActualSales[] = $monthSales;
                    $yearlyProjectedSales[] = null;
                    $totalYearlySales += $monthSales;
                    $completedMonths++;
                } else {
                    // Future month - projected sales
                    $yearlyActualSales[] = null;
                    
                    // Smart projection: use last year's same month as baseline, adjusted by current year's growth
                    $lastYearMonthSales = $lastYearSells->has($month) ? (float) $lastYearSells->get($month)->total_sells : 0;
                    
                    if ($completedMonths > 0 && $lastYearAvg > 0) {
                        // Calculate growth rate: current year average vs last year average
                        $currentYearAvg = $totalYearlySales / $completedMonths;
                        $growthFactor = $currentYearAvg / $lastYearAvg;
                        
                        // Project based on last year's same month, adjusted by growth factor
                        // If last year's month had no data, use current year's average
                        $projectedSale = $lastYearMonthSales > 0 
                            ? ($lastYearMonthSales * $growthFactor)
                            : $currentYearAvg;
                    } elseif ($lastYearMonthSales > 0) {
                        // Use last year's same month if we have no current year data yet
                        $projectedSale = $lastYearMonthSales;
                    } elseif ($completedMonths > 0) {
                        // Fallback: use current year's average
                        $projectedSale = $totalYearlySales / $completedMonths;
                    } else {
                        // Last resort: use last year's average
                        $projectedSale = $lastYearAvg;
                    }
                    
                    $yearlyProjectedSales[] = $projectedSale;
                }
                
                // Last year's sales for comparison
                $lastYearMonthSales = $lastYearSells->has($month) ? (float) $lastYearSells->get($month)->total_sells : 0;
                $lastYearSales[] = $lastYearMonthSales;
            }
            
            // Calculate total projected for the year
            $totalYearlyProjected = $totalYearlySales;
            foreach ($yearlyProjectedSales as $proj) {
                if ($proj !== null) {
                    $totalYearlyProjected += $proj;
                }
            }
            
            // Calculate total last year sales
            $totalLastYearSales = array_sum($lastYearSales);
            
            $output['yearly_labels'] = $yearlyLabels;
            $output['yearly_actual_sales'] = $yearlyActualSales;
            $output['yearly_projected_sales'] = $yearlyProjectedSales;
            $output['last_year_sales'] = $lastYearSales;
            $output['yearly_total_actual'] = (float) $totalYearlySales;
            $output['yearly_total_projected'] = (float) $totalYearlyProjected;
            $output['last_year_total'] = (float) $totalLastYearSales;

            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $permitted_locations = auth()->user()->permitted_locations();
            $products = $this->productUtil->getProductAlert($business_id, $permitted_locations);

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product.' ('.$row->sku.')';
                    } else {
                        return $row->product.' - '.$row->product_variation.' - '.$row->variation.' ('.$row->sub_sku.')';
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>'.(float) $stock.'</span> '.$row->unit;
                })
                ->removeColumn('sku')
                ->removeColumn('sub_sku')
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->where('transactions.payment_status', '!=', 'paid')
                    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'c.supplier_business_name',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = ! empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">'.
                    $due.'</span>';
                })
                ->addColumn('action', '@can("purchase.create") <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endcan')
                ->removeColumn('supplier_business_name')
                ->editColumn('supplier', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$supplier}}')
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]).'"
                                    class="btn-modal" data-container=".view_modal">'.$row->ref_no.'</a>';
                    }

                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->whereIn('transactions.payment_status', ['due', 'partial']);

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as customer',
                'c.supplier_business_name',
                'transactions.invoice_no',
                'final_total',
                DB::raw('COALESCE(SUM(IF(tp.is_return = 0, tp.amount, -tp.amount)), 0) as total_paid')
            )
                        ->groupBy('transactions.id', 'c.name', 'c.supplier_business_name', 'transactions.invoice_no', 'final_total');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = ! empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">'.
                    $due.'</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]).'"
                                    class="btn-modal" data-container=".view_modal">'.$row->invoice_no.'</a>';
                    }

                    return $row->invoice_no;
                })
                ->addColumn('action', '@if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endif')
                ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
                ->removeColumn('supplier_business_name')
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        if (request()->input('page') == 1) {
            auth()->user()->unreadNotifications->markAsRead();
        }
        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (! empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html,
        ];
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && ! empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => ! empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF',
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF',
            ],
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
            'notifications' => [$notification],
        ]);
    }

    public function attachMediasToGivenModel(Request $request)
    {
        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $model_id = $request->input('model_id');
                $model = $request->input('model_type');
                $model_media_type = $request->input('model_media_type');

                DB::beginTransaction();

                //find model to which medias are to be attached
                $model_to_be_attached = $model::where('business_id', $business_id)
                                        ->findOrFail($model_id);

                Media::uploadMedia($business_id, $model_to_be_attached, $request, 'file', false, $model_media_type);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                DB::rollBack();

                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getUserLocation($latlng)
    {
        $latlng_array = explode(',', $latlng);

        $response = $this->moduleUtil->getLocationFromCoordinates($latlng_array[0], $latlng_array[1]);

        return ['address' => $response];
    }
}
