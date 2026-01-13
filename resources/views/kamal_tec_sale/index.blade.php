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
                        {!! Form::label('kamal_tec_customer_id', 'Kamal Tec Customer:') !!}
                        {!! Form::select('kamal_tec_customer_id', $kamal_tec_customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all'), 'id' => 'kamal_tec_customer_id']); !!}
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
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('floa_ref', 'Floa Ref:') !!}
                        {!! Form::text('floa_ref', null, ['placeholder' => 'Search by Floa Ref', 'class' => 'form-control', 'id' => 'floa_ref']); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    
    <!-- Status Tabs -->
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="{{ ($status_tab ?? 'pending') == 'pending' ? 'active' : '' }}">
                        <a href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'pending']) }}">
                            <i class="fa fa-hourglass-half text-info"></i> @lang('lang_v1.pending')
                        </a>
                    </li>
                    <li class="{{ ($status_tab ?? '') == 'open' ? 'active' : '' }}">
                        <a href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'open']) }}">
                            <i class="fa fa-clock-o text-warning"></i> @lang('lang_v1.open')
                        </a>
                    </li>
                    <li class="{{ ($status_tab ?? '') == 'closed' ? 'active' : '' }}">
                        <a href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'closed']) }}">
                            <i class="fa fa-check-circle text-success"></i> @lang('lang_v1.closed')
                        </a>
                    </li>
                    <li class="{{ ($status_tab ?? '') == 'cancelled' ? 'active' : '' }}">
                        <a href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'index'], ['status_tab' => 'cancelled']) }}">
                            <i class="fa fa-times-circle text-danger"></i> @lang('lang_v1.cancelled')
                        </a>
                    </li>
                </ul>
            </div>
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
                                'kamal_tec_customer_id' => request()->kamal_tec_customer_id ?? '',
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
                                <th>Floa Ref</th>
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
                    d.kamal_tec_customer_id = $('#kamal_tec_customer_id').val();
                    d.product_id = $('#product_id').val();
                    d.status_tab = '{{ $status_tab ?? "pending" }}';
                    d.paid_status = $('#paid_status').val();
                    d.commission_type = $('#commission_type').val();
                    d.floa_ref = $('#floa_ref').val();
                }
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'sale_date', name: 'sale_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'kt_invoice_no', name: 'kt_invoice_no', orderable: false, searchable: true },
                { data: 'floa_ref', name: 'floa_ref', orderable: false, searchable: true },
                { data: 'customer_name', name: 'customer_name', searchable: true },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'commission_amount', name: 'commission_amount' },
                { data: 'status', name: 'status' }
            ],
            fnDrawCallback: function(oSettings) {
                __currency_convert_recursively($('#kamal_tec_sale_table'));
                
                // Make rows clickable (cursor pointer)
                $('#kamal_tec_sale_table tbody tr').css('cursor', 'pointer');
            },
        });

        $(document).on('change', '#kamal_tec_customer_id, #product_id, #paid_status, #commission_type', function() {
            kamal_tec_sale_table.ajax.reload();
        });

        // Floa Ref search with debounce
        var floa_ref_timeout;
        $('#floa_ref').on('keyup', function() {
            clearTimeout(floa_ref_timeout);
            var $this = $(this);
            floa_ref_timeout = setTimeout(function() {
                kamal_tec_sale_table.ajax.reload();
            }, 500);
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

        // Handle status change (from dropdown or badge click)
        function changeSaleStatus(saleId, newStatus) {
            var statusLabels = {
                'open': 'Open',
                'closed': 'Closed',
                'cancelled': 'Cancelled'
            };

            swal({
                title: LANG.sure || 'Are you sure?',
                text: 'Change status to ' + statusLabels[newStatus] + '?',
                icon: "warning",
                buttons: true,
                dangerMode: false,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        url: '{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'updateStatus'], ['id' => '__ID__']) }}'.replace('__ID__', saleId),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: newStatus
                        },
                        success: function(result) {
                            if (result.success) {
                                toastr.success(result.msg || 'Status updated successfully');
                                kamal_tec_sale_table.ajax.reload();
                            } else {
                                toastr.error(result.msg || 'Something went wrong');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong');
                            console.error(xhr);
                        }
                    });
                }
            });
        }

        // Handle status change from dropdown
        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var saleId = $(this).data('id');
            var newStatus = $(this).data('status');
            changeSaleStatus(saleId, newStatus);
        });

        // Handle status change from badge click
        $(document).on('click', '.change-status-badge', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var saleId = $(this).data('id');
            var currentStatus = $(this).data('status');
            
            // Show options to change to other statuses
            var options = [];
            if (currentStatus != 'open') {
                options.push({text: 'Open', value: 'open', icon: 'fa-clock-o'});
            }
            if (currentStatus != 'closed') {
                options.push({text: 'Closed', value: 'closed', icon: 'fa-check-circle'});
            }
            if (currentStatus != 'cancelled') {
                options.push({text: 'Cancelled', value: 'cancelled', icon: 'fa-times-circle'});
            }

            if (options.length > 0) {
                var optionsHtml = options.map(function(opt) {
                    return '<button class="btn btn-default btn-sm change-status-option" data-status="' + opt.value + '" style="margin: 5px;"><i class="fa ' + opt.icon + '"></i> ' + opt.text + '</button>';
                }).join('');

                swal({
                    title: 'Change Status',
                    html: '<p>Select new status:</p>' + optionsHtml,
                    icon: "info",
                    buttons: false,
                });

                // Handle option clicks - use setTimeout to ensure buttons are rendered
                setTimeout(function() {
                    $('.change-status-option').on('click', function() {
                        var newStatus = $(this).data('status');
                        swal.close();
                        changeSaleStatus(saleId, newStatus);
                    });
                }, 100);
            }
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

        // Handle row click to navigate to sale details page
        $(document).on('click', '#kamal_tec_sale_table tbody tr', function(e) {
            // Don't navigate if clicking on action buttons, links, or dropdowns
            if (
                $(e.target).is('a') ||
                $(e.target).is('button') ||
                $(e.target).is('i') ||
                $(e.target).closest('a').length > 0 ||
                $(e.target).closest('button').length > 0 ||
                $(e.target).closest('.btn-group').length > 0 ||
                $(e.target).closest('.dropdown-menu').length > 0
            ) {
                return;
            }
            
            var href = $(this).data('href');
            if (href) {
                window.location.href = href;
            }
        });

        // Handle form submission for Floa Ref edit (after modal is loaded)
        $(document).on('submit', '#edit_floa_ref_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var formUrl = form.attr('action');
            var formData = form.serialize();
            var container = $('.view_modal');

            $.ajax({
                method: 'POST',
                url: formUrl,
                data: formData,
                dataType: 'json',
                success: function(result) {
                    if (result.success == 1 || result.success == true) {
                        container.modal('hide');
                        toastr.success(result.msg);
                        // Reload the DataTable
                        if (typeof kamal_tec_sale_table !== 'undefined') {
                            kamal_tec_sale_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Something went wrong';
                    if (xhr.responseJSON && xhr.responseJSON.msg) {
                        errorMsg = xhr.responseJSON.msg;
                    }
                    toastr.error(errorMsg);
                }
            });
        });
    });
</script>
@endsection
