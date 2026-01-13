@extends('layouts.app')
@section('title', 'Kamal Tec Phone Sale Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Kamal Tec Phone Sale Report</h1>
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
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('contact.contact') . ':') !!}
                        {!! Form::select('contact_id', $customers, $contact_id, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_id', __('product.product') . ':') !!}
                        {!! Form::select('product_id', $products->pluck('name', 'id'), $product_id, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('status', __('lang_v1.status') . ':') !!}
                        {!! Form::select('status', ['open' => __('lang_v1.open'), 'closed' => __('lang_v1.closed'), 'cancelled' => __('lang_v1.cancelled')], $status, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('commission_type', 'Commission Type:') !!}
                        {!! Form::select('commission_type', ['percent' => 'Percent', 'fixed' => 'Fixed'], $commission_type, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('group_by', 'Group By:') !!}
                        {!! Form::select('group_by', ['none' => 'None', 'customer' => 'Customer', 'month' => 'Month', 'product' => 'Product'], $group_by, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-primary" id="filter_btn">Filter</button>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Summary</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-blue"><i class="fa fa-mobile"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Devices Sold</span>
                                    <span class="info-box-number">{{ number_format($summary->total_devices ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Sales</span>
                                    <span class="info-box-number"><span class="display_currency">{{ $summary->total_sales ?? 0 }}</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-yellow"><i class="fa fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Commission Paid</span>
                                    <span class="info-box-number"><span class="display_currency">{{ $summary->total_commission_paid ?? 0 }}</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-purple"><i class="fa fa-percent"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Due Commission</span>
                                    <span class="info-box-number"><span class="display_currency">{{ $summary->total_commission ?? 0 }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Report Details</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ $group_by != 'none' ? 'Group' : '' }}</th>
                                <th>Total Devices</th>
                                <th>Total Sales</th>
                                <th>Commission Paid</th>
                                <th>Due Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $result)
                            <tr>
                                <td>{{ $result->group_name ?? '-' }}</td>
                                <td>{{ number_format($result->total_devices ?? 0) }}</td>
                                <td><span class="display_currency">{{ $result->total_sales ?? 0 }}</span></td>
                                <td><span class="display_currency">{{ $result->total_commission_paid ?? 0 }}</span></td>
                                <td><span class="display_currency">{{ $result->total_commission ?? 0 }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            }
        );

        $('#filter_btn').click(function() {
            var url = '{{ action([\App\Http\Controllers\KamalTecSaleReportController::class, 'index']) }}';
            var params = {
                start_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '',
                end_date: $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '',
                contact_id: $('#contact_id').val(),
                product_id: $('#product_id').val(),
                status: $('#status').val(),
                commission_type: $('#commission_type').val(),
                group_by: $('#group_by').val()
            };
            window.location.href = url + '?' + $.param(params);
        });
    });
</script>
@endsection
