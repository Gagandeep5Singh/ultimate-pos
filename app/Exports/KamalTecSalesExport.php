<?php

namespace App\Exports;

use App\KamalTecSale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class KamalTecSalesExport implements FromArray, WithHeadings, WithTitle
{
    protected $business_id;
    protected $filters;

    public function __construct($business_id, $filters = [])
    {
        $this->business_id = $business_id;
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'KT Invoice No',
            'Date',
            'Customer',
            'Product Name',
            'SKU',
            'Qty',
            'Unit Price',
            'IMEI/Serial',
            'Line Total',
            'Total Amount',
            'Commission Type',
            'Commission Value',
            'Commission Amount',
            'Commission Paid',
            'Due Commission',
            'Status',
            'Notes'
        ];
    }

    public function array(): array
    {
        $query = KamalTecSale::where('kamal_tec_sales.business_id', $this->business_id)
            ->leftJoin('contacts AS c', 'kamal_tec_sales.contact_id', '=', 'c.id')
            ->select(
                'kamal_tec_sales.*',
                'c.name as customer_name'
            );

        // Apply filters
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereDate('kamal_tec_sales.sale_date', '>=', $this->filters['start_date'])
                ->whereDate('kamal_tec_sales.sale_date', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['contact_id'])) {
            $query->where('kamal_tec_sales.contact_id', $this->filters['contact_id']);
        }

        if (!empty($this->filters['product_id'])) {
            $query->whereHas('saleLines', function ($q) {
                $q->where('product_id', $this->filters['product_id']);
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('kamal_tec_sales.status', $this->filters['status']);
        }

        if (!empty($this->filters['commission_type'])) {
            $query->where('kamal_tec_sales.commission_type', $this->filters['commission_type']);
        }

        $sales = $query->with(['saleLines.product'])->get();

        $export_data = [];

        foreach ($sales as $sale) {
            // Calculate commission paid and due
            $commission_paid = $sale->payments()->sum('amount');
            $due_commission = max(0, $sale->commission_amount - $commission_paid);
            $payment_status = $due_commission <= 0 ? 'Paid' : 'Not Paid';

            // If sale has multiple products, create a row for each product
            if ($sale->saleLines->count() > 0) {
                foreach ($sale->saleLines as $index => $line) {
                    $row = [
                        $sale->invoice_no,
                        $sale->kt_invoice_no ?? '',
                        \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y'),
                        $sale->customer_name ?? '',
                        $line->product_name_snapshot ?? ($line->product->name ?? ''),
                        $line->sku_snapshot ?? ($line->product->sku ?? ''),
                        number_format($line->qty, 2),
                        number_format($line->unit_price, 2),
                        $line->imei_serial ?? '',
                        number_format($line->line_total, 2),
                        // Only show total amount and commission info in first row
                        $index === 0 ? number_format($sale->total_amount, 2) : '',
                        $index === 0 ? ucfirst($sale->commission_type) : '',
                        $index === 0 ? number_format($sale->commission_value, 2) : '',
                        $index === 0 ? number_format($sale->commission_amount, 2) : '',
                        $index === 0 ? number_format($commission_paid, 2) : '',
                        $index === 0 ? number_format($due_commission, 2) : '',
                        $index === 0 ? $payment_status : '',
                        $index === 0 ? ($sale->notes ?? '') : '',
                    ];
                    $export_data[] = $row;
                }
            } else {
                // Sale with no products (shouldn't happen, but handle it)
                $row = [
                    $sale->invoice_no,
                    $sale->kt_invoice_no ?? '',
                    \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y'),
                    $sale->customer_name ?? '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    number_format($sale->total_amount, 2),
                    ucfirst($sale->commission_type),
                    number_format($sale->commission_value, 2),
                    number_format($sale->commission_amount, 2),
                    number_format($commission_paid, 2),
                    number_format($due_commission, 2),
                    $payment_status,
                    $sale->notes ?? '',
                ];
                $export_data[] = $row;
            }
        }

        return $export_data;
    }

    public function title(): string
    {
        return 'Kamal Tec Sales';
    }
}
