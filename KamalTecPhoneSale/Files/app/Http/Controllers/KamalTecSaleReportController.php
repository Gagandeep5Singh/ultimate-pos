<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\KamalTecSale;
use App\KamalTecSaleLine;
use App\Product;
use DB;
use Illuminate\Http\Request;

class KamalTecSaleReportController extends Controller
{
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
        $product_id = request()->product_id;
        $status = request()->status;
        $commission_type = request()->commission_type;
        $group_by = request()->group_by ?? 'none';

        // Base query
        $query = KamalTecSale::where('kamal_tec_sales.business_id', $business_id)
            ->leftJoin('contacts AS c', 'kamal_tec_sales.contact_id', '=', 'c.id')
            ->leftJoin('kamal_tec_sale_lines AS sl', 'kamal_tec_sales.id', '=', 'sl.kamal_tec_sale_id')
            ->leftJoin('products AS p', 'sl.product_id', '=', 'p.id');

        // Apply filters
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('kamal_tec_sales.sale_date', '>=', $start_date)
                ->whereDate('kamal_tec_sales.sale_date', '<=', $end_date);
        }

        if (!empty($contact_id)) {
            $query->where('kamal_tec_sales.contact_id', $contact_id);
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

        // Group by options
        if ($group_by == 'customer') {
            $query->select(
                'c.name as group_name',
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission')
            )
            ->groupBy('c.id', 'c.name');
        } elseif ($group_by == 'month') {
            $query->select(
                DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m") as group_name'),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission')
            )
            ->groupBy(DB::raw('DATE_FORMAT(kamal_tec_sales.sale_date, "%Y-%m")'));
        } elseif ($group_by == 'product') {
            $query->select(
                'p.name as group_name',
                'p.sku',
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission')
            )
            ->groupBy('p.id', 'p.name', 'p.sku');
        } else {
            // No grouping - overall totals
            $query->select(
                DB::raw('"Overall" as group_name'),
                DB::raw('SUM(sl.qty) as total_devices'),
                DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
                DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission')
            );
        }

        $results = $query->get();

        // Get summary totals
        $summary_query = KamalTecSale::where('business_id', $business_id)
            ->leftJoin('kamal_tec_sale_lines AS sl', 'kamal_tec_sales.id', '=', 'sl.kamal_tec_sale_id');

        if (!empty($start_date) && !empty($end_date)) {
            $summary_query->whereDate('kamal_tec_sales.sale_date', '>=', $start_date)
                ->whereDate('kamal_tec_sales.sale_date', '<=', $end_date);
        }

        if (!empty($contact_id)) {
            $summary_query->where('kamal_tec_sales.contact_id', $contact_id);
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

        $summary = $summary_query->select(
            DB::raw('SUM(sl.qty) as total_devices'),
            DB::raw('SUM(kamal_tec_sales.total_amount) as total_sales'),
            DB::raw('SUM(kamal_tec_sales.paid_amount) as total_paid'),
            DB::raw('SUM(kamal_tec_sales.due_amount) as total_due'),
            DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0)) as total_commission_paid'),
            DB::raw('SUM(GREATEST(0, kamal_tec_sales.commission_amount - COALESCE((SELECT SUM(amount) FROM kamal_tec_payments WHERE kamal_tec_payments.kamal_tec_sale_id = kamal_tec_sales.id), 0))) as total_commission')
        )->first();

        // Get filter options
        $customers = Contact::where('business_id', $business_id)
            ->whereIn('type', ['customer', 'both'])
            ->pluck('name', 'id');

        $products = Product::where('business_id', $business_id)
            ->select('id', 'name', 'sku')
            ->get();

        return view('kamal_tec_sale.report', compact('results', 'summary', 'customers', 'products', 'start_date', 'end_date', 'contact_id', 'product_id', 'status', 'commission_type', 'group_by'));
    }
}
