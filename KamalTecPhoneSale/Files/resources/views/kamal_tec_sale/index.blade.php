@extends('layouts.app')
@section('title', 'Kamal Tec Phone Sale')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Kamal Tec Phone Sale</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date_range', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('contact.contact') . ':') !!}
                        {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('product_id', __('product.product') . ':') !!}
                        {!! Form::select('product_id', $products->pluck('name', 'id'), null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('status', __('lang_v1.status') . ':') !!}
                        {!! Form::select('status', ['open' => __('lang_v1.open'), 'closed' => __('lang_v1.closed'), 'cancelled' => __('lang_v1.cancelled')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('paid_status', __('purchase.payment_status') . ':') !!}
                        {!! Form::select('paid_status', ['paid' => __('lang_v1.paid'), 'partial' => __('lang_v1.partial'), 'due' => __('lang_v1.due')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('commission_type', 'Commission Type:') !!}
                        {!! Form::select('commission_type', ['percent' => 'Percent', 'fixed' => 'Fixed'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Kamal Tec Phone Sales'])
                @slot('tool')
                    <div class="box-tools">
                        <a class="btn btn-success btn-sm pull-right" style="margin-right: 5px;"
                            href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'export'], array_merge(request()->all(), [
                                'start_date' => request()->start_date ?? '',
                                'end_date' => request()->end_date ?? '',
                                'contact_id' => request()->contact_id ?? '',
                                'product_id' => request()->product_id ?? '',
                                'status' => request()->status ?? '',
                                'commission_type' => request()->commission_type ?? ''
                            ])) }}"
                            title="Export all sales to Excel with product IMEI and payment status">
                            <i class="fa fa-file-excel-o"></i> Export All Sales
                        </a>
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right tw-m-2"
                            href="{{action([\App\Http\Controllers\KamalTecSaleController::class, 'create'])}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="kamal_tec_sale_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>Invoice No</th>
                                <th>KT Invoice No</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Due Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        //Date range as a button
        $('#date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                kamal_tec_sale_table.ajax.reload();
            }
        );
        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#date_range').val('');
            kamal_tec_sale_table.ajax.reload();
        });

        var kamal_tec_sale_table = $('#kamal_tec_sale_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{action([\App\Http\Controllers\KamalTecSaleController::class, 'index'])}}',
                data: function(d) {
                    d.start_date = $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD') : '';
                    d.end_date = $('#date_range').data('daterangepicker') ? $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD') : '';
                    d.contact_id = $('#contact_id').val();
                    d.product_id = $('#product_id').val();
                    d.status = $('#status').val();
                    d.paid_status = $('#paid_status').val();
                    d.commission_type = $('#commission_type').val();
                }
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'sale_date', name: 'sale_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'kt_invoice_no', name: 'kt_invoice_no', orderable: false, searchable: true },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'commission_amount', name: 'commission_amount' },
                { data: 'status', name: 'status' }
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#kamal_tec_sale_table'));
            },
        });

        $(document).on('change', '#contact_id, #product_id, #status, #paid_status, #commission_type', function() {
            kamal_tec_sale_table.ajax.reload();
        });

        $(document).on('click', '.delete-sale', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: "DELETE",
                        url: url,
                        dataType: "json",
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                kamal_tec_sale_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error('Failed to delete sale. Please try again.');
                        }
                    });
                }
            });
        });

        // Handle Add Payment from Actions dropdown
        $(document).on('click', '.add_payment_modal', function(e) {
            e.preventDefault();
            var url = $(this).attr('href') || $(this).data('href');
            var container = $('.payment_modal');
            
            $.ajax({
                url: url,
                dataType: 'html',
                success: function(result) {
                    container.html(result).modal('show');
                    
                    // Convert currency display
                    if (typeof __currency_convert_recursively === 'function') {
                        __currency_convert_recursively(container);
                    }
                    
                    // Initialize select2
                    container.find('.select2').each(function() {
                        var $p = $(this).closest('.modal');
                        $(this).select2({ dropdownParent: $p });
                    });
                    
                    // Initialize datepicker
                    container.find('#paid_on').datepicker({
                        autoclose: true,
                        format: datepicker_date_format
                    });
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load payment form');
                }
            });
        });
    });
</script>
@endsection
