@extends('layouts.app')
@section('title', 'Kamal Tec Phone Sale Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="row">
        <div class="col-md-6">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Kamal Tec Phone Sale Report</h1>
        </div>
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-success" id="export_excel_btn" title="Export to Excel">
                <i class="fa fa-file-excel-o"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger" id="export_pdf_btn" title="Export to PDF">
                <i class="fa fa-file-pdf-o"></i> Export PDF
            </button>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', $start_date && $end_date ? $start_date . ' ~ ' . $end_date : null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_range', 'readonly']); !!}
                        <div class="btn-group" style="margin-top: 5px;">
                            <button type="button" class="btn btn-xs btn-default date-preset" data-days="7">Last 7 Days</button>
                            <button type="button" class="btn btn-xs btn-default date-preset" data-days="30">Last 30 Days</button>
                            <button type="button" class="btn btn-xs btn-default date-preset" data-days="90">Last 90 Days</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('business.business_location') . ':') !!}
                        {!! Form::select('location_id', $locations, $location_id ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('kamal_tec_customer_id', 'Kamal Tec Customer:') !!}
                        {!! Form::select('kamal_tec_customer_id', $kamal_tec_customers, $kamal_tec_customer_id ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('contact.contact') . ':') !!}
                        {!! Form::select('contact_id', $customers, $contact_id ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_id', __('product.product') . ':') !!}
                        {!! Form::select('product_id', $products->pluck('name', 'id'), $product_id ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('status', __('lang_v1.status') . ':') !!}
                        {!! Form::select('status', ['pending' => __('lang_v1.pending'), 'open' => __('lang_v1.open'), 'closed' => __('lang_v1.closed'), 'cancelled' => __('lang_v1.cancelled')], $status ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('paid_status', 'Payment Status:') !!}
                        {!! Form::select('paid_status', ['paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due'], $paid_status ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('commission_type', 'Commission Type:') !!}
                        {!! Form::select('commission_type', ['percent' => 'Percent', 'fixed' => 'Fixed'], $commission_type ?? null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('group_by', 'Group By:') !!}
                        {!! Form::select('group_by', ['none' => 'None', 'customer' => 'Customer', 'day' => 'Day', 'month' => 'Month', 'product' => 'Product', 'location' => 'Location'], $group_by ?? 'none', ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-primary" id="filter_btn">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-default" id="reset_btn">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Sales (Open & Closed)</span>
                    <span class="info-box-number">{{ number_format($summary->total_sales_count ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-mobile"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Devices</span>
                    <span class="info-box-number">{{ number_format($summary->total_devices ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Sales Amount</span>
                    <span class="info-box-number"><span class="display_currency" data-currency_symbol="true">{{ $summary->total_sales ?? 0 }}</span></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Commission Paid</span>
                    <span class="info-box-number"><span class="display_currency" data-currency_symbol="true">{{ $summary->total_commission_paid ?? 0 }}</span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Due Commission</span>
                    <span class="info-box-number"><span class="display_currency" data-currency_symbol="true">{{ $summary->total_commission_due ?? 0 }}</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Sales Trend</h3>
                </div>
                <div class="box-body">
                    <canvas id="salesTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Status Breakdown</h3>
                </div>
                <div class="box-body">
                    <canvas id="statusChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Details -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Report Details</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="report_table">
                            <thead>
                                <tr>
                                    <th>{{ $group_by != 'none' ? 'Group' : '#' }}</th>
                                    @if($group_by != 'none')
                                    <th>Sales Count</th>
                                    @endif
                                    <th>Total Devices</th>
                                    <th>Total Sales</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Commission Paid</th>
                                    <th>Due Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $index => $result)
                                <tr>
                                    <td>{{ $group_by == 'none' ? ($index + 1) : ($result->group_name ?? '-') }}</td>
                                    @if($group_by != 'none')
                                    <td>{{ number_format($result->total_sales_count ?? 0) }}</td>
                                    @endif
                                    <td>{{ number_format($result->total_devices ?? 0) }}</td>
                                    <td><span class="display_currency" data-currency_symbol="true">{{ $result->total_commission_paid ?? 0 }}</span></td>
                                    <td><span class="display_currency" data-currency_symbol="true">{{ $result->total_commission_due ?? 0 }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $group_by != 'none' ? '5' : '4' }}" class="text-center">No data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray">
                                    <th>Total</th>
                                    @if($group_by != 'none')
                                    <th>{{ number_format($summary->total_sales_count ?? 0) }}</th>
                                    @endif
                                    <th>{{ number_format($summary->total_devices ?? 0) }}</th>
                                    <th><span class="display_currency" data-currency_symbol="true">{{ $summary->total_sales ?? 0 }}</span></th>
                                    <th><span class="display_currency" data-currency_symbol="true">{{ $summary->total_paid ?? 0 }}</span></th>
                                    <th><span class="display_currency" data-currency_symbol="true">{{ $summary->total_due ?? 0 }}</span></th>
                                    <th><span class="display_currency" data-currency_symbol="true">{{ $summary->total_commission_paid ?? 0 }}</span></th>
                                    <th><span class="display_currency" data-currency_symbol="true">{{ $summary->total_commission_due ?? 0 }}</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize date range picker
        $('#date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            }
        );

        // Date presets
        $('.date-preset').click(function() {
            var days = $(this).data('days');
            var end = moment();
            var start = moment().subtract(days, 'days');
            $('#date_range').data('daterangepicker').setStartDate(start);
            $('#date_range').data('daterangepicker').setEndDate(end);
            $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        });

        // Filter button
        $('#filter_btn').click(function() {
            var url = '{{ route('kamal-tec-sale-report') }}';
            var params = {
                start_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '',
                end_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '',
                location_id: $('#location_id').val(),
                kamal_tec_customer_id: $('#kamal_tec_customer_id').val(),
                contact_id: $('#contact_id').val(),
                product_id: $('#product_id').val(),
                status: $('#status').val(),
                paid_status: $('#paid_status').val(),
                commission_type: $('#commission_type').val(),
                group_by: $('#group_by').val()
            };
            window.location.href = url + '?' + $.param(params);
        });

        // Reset button
        $('#reset_btn').click(function() {
            window.location.href = '{{ route('kamal-tec-sale-report') }}';
        });

        // Export buttons
        $('#export_excel_btn').click(function() {
            var url = '{{ route('kamal-tec-sale-report') }}';
            var params = {
                start_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '',
                end_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '',
                location_id: $('#location_id').val(),
                kamal_tec_customer_id: $('#kamal_tec_customer_id').val(),
                contact_id: $('#contact_id').val(),
                product_id: $('#product_id').val(),
                status: $('#status').val(),
                paid_status: $('#paid_status').val(),
                commission_type: $('#commission_type').val(),
                group_by: $('#group_by').val(),
                export: 'excel'
            };
            window.location.href = url + '?' + $.param(params);
        });

        $('#export_pdf_btn').click(function() {
            var url = '{{ route('kamal-tec-sale-report') }}';
            var params = {
                start_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '',
                end_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '',
                location_id: $('#location_id').val(),
                kamal_tec_customer_id: $('#kamal_tec_customer_id').val(),
                contact_id: $('#contact_id').val(),
                product_id: $('#product_id').val(),
                status: $('#status').val(),
                paid_status: $('#paid_status').val(),
                commission_type: $('#commission_type').val(),
                group_by: $('#group_by').val(),
                export: 'pdf'
            };
            window.open(url + '?' + $.param(params), '_blank');
        });

        // Initialize DataTable
        $('#report_table').DataTable({
            "pageLength": 25,
            "order": [[0, "asc"]],
            "dom": 'Bfrtip',
            "buttons": [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // Sales Trend Chart
        var chartData = @json($chart_data);
        var salesDates = chartData.map(item => item.date);
        var salesCounts = chartData.map(item => item.count);
        var salesTotals = chartData.map(item => parseFloat(item.total_sales || 0));
        var commissionTotals = chartData.map(item => parseFloat(item.total_commission || 0));

        var ctx1 = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: salesDates,
                datasets: [{
                    label: 'Sales Count',
                    data: salesCounts,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: false
                }, {
                    label: 'Total Sales Amount',
                    data: salesTotals,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false,
                    yAxisID: 'y-axis-1'
                }, {
                    label: 'Commission Amount',
                    data: commissionTotals,
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    fill: false,
                    yAxisID: 'y-axis-1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    yAxes: [{
                        id: 'y-axis-0',
                        type: 'linear',
                        position: 'left',
                        ticks: {
                            beginAtZero: true
                        }
                    }, {
                        id: 'y-axis-1',
                        type: 'linear',
                        position: 'right',
                        ticks: {
                            beginAtZero: true
                        },
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }]
                }
            }
        });

        // Status Breakdown Chart (Shows Total Sales Amount)
        var statusData = @json($status_breakdown);
        var statusLabels = statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
        var statusCounts = statusData.map(item => item.count);
        var statusTotals = statusData.map(item => parseFloat(item.total_sales || 0));

        var ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusTotals,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    position: 'bottom'
                }
            }
        });
    });
</script>
@endsection
