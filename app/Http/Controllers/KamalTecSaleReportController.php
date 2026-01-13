<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\KamalTecCustomer;
use App\KamalTecSale;
use App\KamalTecSaleLine;
use App\Product;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KamalTecSaleReportController extends Controller
{
    protected $businessUtil;

    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }
    /**
     * Display the report
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        // Get filter data
        $start_date = request()->start_date;
        $end_date = request()->end_date;
        $contact_id = request()->contact_id;
        $kamal_tec_customer_id = request()->kamal_tec_customer_id;
        $product_id = request()->product_id;
        $status = request()->status;
        $commission_type = request()->commission_type;
        $location_id = request()->location_id;
        $paid_status = request()->paid_status;
        $group_by = request()->group_by ?? 'none';

        // Base query
        $query = KamalTecSale::where('kamal_tec_sales.business_id', $business_id)
            ->leftJoin('contacts AS c', 'kamal_tec_sales.contact_id', '=', 'c.id')
            ->leftJoin('kamal_tec_customers AS ktc', 'kamal_tec_sales.customer_id', '=', 'ktc.id')
            ->leftJoin('business_locations AS bl', 'kamal_tec_sales.location_id', '=', 'bl.id')
            ->leftJoin('kamal_tec_sale_lines AS sl', 'kamal_tec_sales.id', '=', 'sl.kamal_tec_sale_id')
            ->leftJoin('products AS p', 'sl.product_id', '=', 'p.id');

        // In reports, only count 'open' and 'closed' sales (exclude 'cancelled' and 'pending')
        // Unless status filter is explicitly set
        if (empty($status)) {
            $query->whereIn('kamal_tec_sales.status', ['open', 'closed']);
        } elseif ($status == 'cancelled' || $status == 'pending') {
            // If explicitly filtering for cancelled or pending, show them
            $query->where('kamal_tec_sales.status', $status);
        } else {
            $query->where('kamal_tec_sales.status', $status);
        }

        // Apply filters
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('kamal_tec_sales.sale_date', '>=', $start_date)
                ->whereDate('kamal_tec_sales.sale_date', '<=', $end_date);
        }

        if (!empty($contact_id)) {
            $query->where('kamal_tec_sales.contact_id', $contact_id);
        }

        if (!empty($kamal_tec_customer_id)) {
            $query->where('kamal_tec_sales.customer_id', $kamal_tec_customer_id);
        }

        if (!empty($location_id)) {
            $query->where('kamal_tec_sales.location_id', $location_id);
        }

        if (!empty($product_id)) {
            $query->where('sl.product_id', $product_id);
        }

        if (!empty($status)) {
            $query->where('kamal_tec_sales.status', $status);
        }

        if (!empty($commission_type)) {
            $query->where('kamal_tec_sales.commission_type', $commission_type);
        }

        // Paid status filter
        if (!empty($paid_status)) {
            if ($paid_status == 'paid') {
                $query->whereRaw('kamal_tec_sales.due_amount <= 0');
            } elseif ($paid_status == 'partial') {
                $query->whereRaw('kamal_tec_sales.paid_amount > 0 AND kamal_tec_sales.due_amount > 0');
            } elseif ($paid_status == 'due') {
                $query->whereRaw('kamal_tec_sales.due_amount > 0');
            }
        }

        // Group by options
        // Reports show both total sale amounts AND commission amounts
        if ($group_by == 'customer') {
            $query->select(
                DB::raw("COALESCE(CONCAT(ktc.first_name, ' ', ktc.last_name), c.name, 'Unknown') as group_name"),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            )
            ->groupBy(DB::raw("COALESCE(CONCAT(ktc.first_name, ' ', ktc.last_name), c.name, 'Unknown')"));
        } elseif ($group_by == 'month') {
            $query->select(
                DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m") as group_name'),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            )
            ->groupBy(DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m")'))
            ->orderBy('group_name');
        } elseif ($group_by == 'day') {
            $query->select(
                DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m-%d") as group_name'),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            )
            ->groupBy(DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m-%d")'))
            ->orderBy('group_name');
        } elseif ($group_by == 'product') {
            $query->select(
                'p.name as group_name',
                'p.sku',
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            )
            ->groupBy('p.id', 'p.name', 'p.sku');
        } elseif ($group_by == 'location') {
            $query->select(
                'bl.name as group_name',
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            )
            ->groupBy('bl.id', 'bl.name');
        } else {
            // No grouping - overall totals
            $query->select(
                DB::raw('"Overall" as group_name'),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
            );
        }

        $results = $query->get();

        // Get summary totals
        $summary_query = KamalTecSale::where('business_id', $business_id)
            ->leftJoin('kamal_tec_sale_lines AS sl', 'kamal_tec_sales.id', '=', 'sl.kamal_tec_sale_id');

        // In reports, only count 'open' and 'closed' sales (exclude 'cancelled' and 'pending')
        // Unless status filter is explicitly set
        if (empty($status)) {
            $summary_query->whereIn('kamal_tec_sales.status', ['open', 'closed']);
        } elseif ($status == 'cancelled' || $status == 'pending') {
            // If explicitly filtering for cancelled or pending, show them
            $summary_query->where('kamal_tec_sales.status', $status);
        } else {
            $summary_query->where('kamal_tec_sales.status', $status);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $summary_query->whereDate('kamal_tec_sales.sale_date', '>=', $start_date)
                ->whereDate('kamal_tec_sales.sale_date', '<=', $end_date);
        }

        if (!empty($contact_id)) {
            $summary_query->where('kamal_tec_sales.contact_id', $contact_id);
        }

        if (!empty($kamal_tec_customer_id)) {
            $summary_query->where('kamal_tec_sales.customer_id', $kamal_tec_customer_id);
        }

        if (!empty($location_id)) {
            $summary_query->where('kamal_tec_sales.location_id', $location_id);
        }

        if (!empty($product_id)) {
            $summary_query->where('sl.product_id', $product_id);
        }

        if (!empty($status)) {
            $summary_query->where('kamal_tec_sales.status', $status);
        }

        if (!empty($commission_type)) {
            $summary_query->where('kamal_tec_sales.commission_type', $commission_type);
        }

        // Paid status filter
        if (!empty($paid_status)) {
            if ($paid_status == 'paid') {
                $summary_query->whereRaw('kamal_tec_sales.due_amount <= 0');
            } elseif ($paid_status == 'partial') {
                $summary_query->whereRaw('kamal_tec_sales.paid_amount > 0 AND kamal_tec_sales.due_amount > 0');
            } elseif ($paid_status == 'due') {
                $summary_query->whereRaw('kamal_tec_sales.due_amount > 0');
            }
        }

        $summary = $summary_query->select(
            DB::raw('COUNT(DISTINCT kamal_tec_sales.id) as total_sales_count'),
            DB::raw('SUM(sl.qty) as total_devices'),
            DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
            DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
            DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission_due')
        )->first();

        // Get chart data for sales trend (only open and closed sales)
        $chart_start_date = $start_date ? Carbon::parse($start_date) : Carbon::now()->subDays(30);
        $chart_end_date = $end_date ? Carbon::parse($end_date) : Carbon::now();
        
        $chart_query = KamalTecSale::where('business_id', $business_id)
            ->whereIn('status', ['open', 'closed'])
            ->whereDate('sale_date', '>=', $chart_start_date)
            ->whereDate('sale_date', '<=', $chart_end_date);
        
        if (!empty($location_id)) {
            $chart_query->where('location_id', $location_id);
        }
        
        $chart_data = $chart_query->select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission')
        )
        ->groupBy(DB::raw('DATE(sale_date)'))
        ->orderBy('date')
        ->get();

        // Get status breakdown (only open and closed)
        $status_breakdown = KamalTecSale::where('business_id', $business_id)
            ->whereIn('status', ['open', 'closed'])
            ->where(function($q) use ($start_date, $end_date) {
                if (!empty($start_date) && !empty($end_date)) {
                    $q->whereDate('sale_date', '>=', $start_date)
                      ->whereDate('sale_date', '<=', $end_date);
                }
            })
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission')
            )
            ->groupBy('status')
            ->get();

        // Get filter options
        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->pluck('name', 'id');

        $kamal_tec_customers = KamalTecCustomer::where('business_id', $business_id)
            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) as name"))
            ->get()
            ->pluck('name', 'id');

        $locations = BusinessLocation::forDropdown($business_id, false, true);

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.report', compact(
            'results', 'summary', 'customers', 'kamal_tec_customers', 'locations', 'products',
            'start_date', 'end_date', 'contact_id', 'kamal_tec_customer_id', 'product_id',
            'status', 'commission_type', 'location_id', 'paid_status', 'group_by',
            'chart_data', 'status_breakdown'
        ));
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Similar logic to index but export to Excel
        // Implementation will be added
        return redirect()->back()->with('error', 'Excel export coming soon');
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        // Similar logic to index but export to PDF
        // Implementation will be added
        return redirect()->back()->with('error', 'PDF export coming soon');
    }
}
