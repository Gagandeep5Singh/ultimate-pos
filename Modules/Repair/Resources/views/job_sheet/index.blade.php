@extends('layouts.app')

@section('title', __('repair::lang.job_sheets'))

@section('content')
@include('repair::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
    	@lang('repair::lang.job_sheets')
    </h1>
</section>
<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters'), 'closed' => false])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('contact_id',  __('role.customer') . ':') !!}
                {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @if(in_array('service_staff' ,$enabled_modules) && !$is_user_service_staff)
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('technician',  __('repair::lang.technician') . ':') !!}
                    {!! Form::select('technician', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('status_id',  __('sale.status') . ':') !!}
                {!! Form::select('status_id', $status_dropdown['statuses'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent
    
	<div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#pending_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-exclamation-circle text-orange"></i>
                            @lang('repair::lang.pending')
                            @show_tooltip(__('repair::lang.common_pending_status_tooltip'))
                        </a>
                    </li>
                    
                    <li>
                        <a href="#completed_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fa fas fa-check-circle text-success"></i>
                            @lang('repair::lang.completed')
                            @show_tooltip(__('repair::lang.common_completed_status_tooltip'))
                        </a>
                    </li>
                    <li>
        <a href="#delivered_job_sheet_tab" data-toggle="tab" aria-expanded="true">
            <i class="fas fa-truck text-primary"></i>
            Delivered
            @show_tooltip('Only job sheets with an invoice number are shown here.')
        </a>
    </li>
    

</ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="pending_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create'])}}" id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> @lang('messages.add')
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="pending_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.action')</th>
                                        <th>
                                            @lang('repair::lang.service_type')
                                        </th>
                                        <th>
                                            @lang('lang_v1.due_date')
                                        </th>
                                        <th>
                                            @lang('repair::lang.job_sheet_no')
                                        </th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('repair::lang.time_left')</th>
                                        
                                        <th>@lang('sale.status')</th>
                                        @if(in_array('service_staff' ,$enabled_modules))
                                            <th>@lang('repair::lang.technician')</th>
                                        @endif
                                        <th>
                                            @lang('role.customer')
                                        </th>
                                        <th>@lang('lang_v1.contact_id')</th>
                                        <th> @lang('repair::lang.customer_phone')</th>
                                        <th>@lang('business.location')</th>
                                        <th>@lang('product.brand')</th>
                                        <th>@lang('repair::lang.device')</th>
                                        <th>@lang('repair::lang.device_model')</th>
                                        <th>@lang('repair::lang.serial_no')</th>
                                        <th>@lang('repair::lang.estimated_cost')</th>
                                        @if(!empty($repair_settings['job_sheet_custom_field_1']))
                                            <th>{{$repair_settings['job_sheet_custom_field_1']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_2']))
                                            <th>{{$repair_settings['job_sheet_custom_field_2']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_3']))
                                            <th>{{$repair_settings['job_sheet_custom_field_3']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_4']))
                                            <th>{{$repair_settings['job_sheet_custom_field_4']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_5']))
                                            <th>{{$repair_settings['job_sheet_custom_field_5']}}</th>
                                        @endif
                                        <th>@lang('lang_v1.added_by')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="completed_job_sheet_tab">
                        <div class="row">
                            <div class="col-md-12 mb-12">
                                <a type="button" class="tw-dw-btn tw-dw-btn-sm tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                    href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'create'])}}" id="add_job_sheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg> @lang('messages.add')
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="completed_job_sheets_table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.action')</th>
                                        <th>
                                            @lang('repair::lang.service_type')
                                        </th>
                                        <th>
                                            @lang('lang_v1.due_date')
                                        </th>
                                        <th>
                                            @lang('repair::lang.job_sheet_no')
                                        </th>
                                        <th>@lang('sale.invoice_no')</th>
                                        <th>@lang('sale.status')</th>
                                        
                                        @if(in_array('service_staff' ,$enabled_modules))
                                            <th>@lang('repair::lang.technician')</th>
                                        @endif
                                        <th>
                                            @lang('role.customer')
                                        </th>
                                        <th>@lang('lang_v1.contact_id')</th>
                                        <th> @lang('repair::lang.customer_phone')</th>
                                        <th>@lang('business.location')</th>
                                        <th>@lang('product.brand')</th>
                                        <th>@lang('repair::lang.device')</th>
                                        <th>@lang('repair::lang.device_model')</th>
                                        <th>@lang('repair::lang.serial_no')</th>
                                        <th>@lang('repair::lang.estimated_cost')</th>
                                        @if(!empty($repair_settings['job_sheet_custom_field_1']))
                                            <th>{{$repair_settings['job_sheet_custom_field_1']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_2']))
                                            <th>{{$repair_settings['job_sheet_custom_field_2']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_3']))
                                            <th>{{$repair_settings['job_sheet_custom_field_3']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_4']))
                                            <th>{{$repair_settings['job_sheet_custom_field_4']}}</th>
                                        @endif
                                        @if(!empty($repair_settings['job_sheet_custom_field_5']))
                                            <th>{{$repair_settings['job_sheet_custom_field_5']}}</th>
                                        @endif
                                        <th>@lang('lang_v1.added_by')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="delivered_job_sheet_tab">
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="delivered_job_sheets_table">
            <thead>
                <tr>
                    <th>@lang('messages.action')</th>
                    <th>@lang('repair::lang.service_type')</th>
                    <th>@lang('lang_v1.due_date')</th>
                    <th>@lang('repair::lang.job_sheet_no')</th>
                    <th>@lang('sale.invoice_no')</th>
                    <th>@lang('sale.status')</th>
                    <th>@lang('role.customer')</th>
                    <th>@lang('lang_v1.contact_id')</th>
                    <th>@lang('repair::lang.customer_phone')</th>
                    <th>@lang('business.location')</th>
                    <th>@lang('product.brand')</th>
                    <th>@lang('repair::lang.device')</th>
                    <th>@lang('repair::lang.device_model')</th>
                    <th>@lang('repair::lang.serial_no')</th>
                    <th>@lang('repair::lang.estimated_cost')</th>
                    <th>@lang('lang_v1.added_by')</th>
                    <th>@lang('lang_v1.created_at')</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            pending_job_sheets_datatable = $("#pending_job_sheets_table").DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax:{
                        url: '/repair/job-sheet',
                        "data": function ( d ) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                            d.location_id = $('#location_id').val();
                            d.contact_id = $('#contact_id').val();
                            d.status_id = $('#status_id').val();
                            d.is_completed_status = 0;
                            @if(in_array('service_staff' ,$enabled_modules))
                                d.technician = $('#technician').val();
                            @endif
                        }
                    },
                    columnDefs: [{
                        targets: [0, 4],
                        orderable: false,
                        searchable: false
                    }],
                    aaSorting:[[2, 'asc']],
                    columns:[
                        { data: 'action', name: 'action' },
                        { data: 'service_type', name: 'service_type'},
                        {
                            data: 'delivery_date', name: 'delivery_date'
                        },
                        {
                            data: 'job_sheet_no', name: 'job_sheet_no'
                        },
                        {
                            data: 'repair_no', name: 'repair_no',
                             visible: false
                        },
                        { data: 'time_left', name: 'time_left', orderable: false, searchable: false },
                        
                        { data:'status', name: 'rs.name' },
                       
                        @if(in_array('service_staff' ,$enabled_modules))
                            { data: 'technecian', name: 'technecian', searchable: false},
                        @endif
                        { data: 'customer', name : 'contacts.name'},
                        { data: 'contact_id', name: 'contacts.contact_id'},
                        { data:'mobile', name: 'contacts.mobile' },
                        { data: 'location', name: 'bl.name' },
                        { data: 'brand', name: 'b.name' },
                        { data: 'device', name: 'device.name' },
                        { data: 'device_model', name: 'rdm.name' },
                        {
                            data: 'serial_no', name: 'serial_no'
                        },
                        {
                            data: 'estimated_cost', name: 'estimated_cost'
                        },
                        @if(!empty($repair_settings['job_sheet_custom_field_1']))
                            {
                                data: 'custom_field_1', name: 'repair_job_sheets.custom_field_1'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_2']))
                            {
                                data: 'custom_field_2', name: 'repair_job_sheets.custom_field_2'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_3']))
                            {
                                data: 'custom_field_3', name: 'repair_job_sheets.custom_field_3'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_4']))
                            {
                                data: 'custom_field_4', name: 'repair_job_sheets.custom_field_4'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_5']))
                            {
                                data: 'custom_field_5', name: 'repair_job_sheets.custom_field_5'
                            },
                        @endif
                        { data: 'added_by', name: 'added_by', searchable: false},
                        { data: 'created_at',
                            name: 'repair_job_sheets.created_at'
                        }
                    ],
                    "fnDrawCallback": function (oSettings) {
                        __currency_convert_recursively($('#pending_job_sheets_table'));
                    }
            });

            completed_job_sheets_datatable = $("#completed_job_sheets_table").DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax:{
                        url: '/repair/job-sheet',
                        "data": function ( d ) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                            d.location_id = $('#location_id').val();
                            d.contact_id = $('#contact_id').val();
                            d.status_id = $('#status_id').val();
                            d.is_completed_status = 1;
                             d.has_invoice = "false"; // ✅ send as string
                            @if(in_array('service_staff' ,$enabled_modules))
                                d.technician = $('#technician').val();
                            @endif
                        }
                    },
                    columnDefs: [{
                        targets: [0, 4],
                        orderable: false,
                        searchable: false
                    }],
                    aaSorting:[[2, 'asc']],
                    columns:[
                        { data: 'action', name: 'action' },
                        { data: 'service_type', name: 'service_type'},
                        {
                            data: 'delivery_date', name: 'delivery_date'
                        },
                        {
                            data: 'job_sheet_no', name: 'job_sheet_no'
                        },
                        {
                            data: 'repair_no', name: 'repair_no'
                        },
                        { data:'status', name: 'rs.name' },
                        @if(in_array('service_staff' ,$enabled_modules))
                            { data: 'technecian', name: 'technecian', searchable: false},
                        @endif
                        { data: 'customer', name : 'contacts.name'},
                        { data: 'contact_id', name: 'contacts.contact_id'},
                        { data:'customer', name: 'contacts.mobile' },
                        { data: 'location', name: 'bl.name' },
                        { data: 'brand', name: 'b.name' },
                        { data: 'device', name: 'device.name' },
                        { data: 'device_model', name: 'rdm.name' },
                        {
                            data: 'serial_no', name: 'serial_no'
                        },
                        {
                            data: 'estimated_cost', name: 'estimated_cost'
                        },
                        @if(!empty($repair_settings['job_sheet_custom_field_1']))
                            {
                                data: 'custom_field_1', name: 'repair_job_sheets.custom_field_1'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_2']))
                            {
                                data: 'custom_field_2', name: 'repair_job_sheets.custom_field_2'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_3']))
                            {
                                data: 'custom_field_3', name: 'repair_job_sheets.custom_field_3'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_4']))
                            {
                                data: 'custom_field_4', name: 'repair_job_sheets.custom_field_4'
                            },
                        @endif
                        @if(!empty($repair_settings['job_sheet_custom_field_5']))
                            {
                                data: 'custom_field_5', name: 'repair_job_sheets.custom_field_5'
                            },
                        @endif
                        { data: 'added_by', name: 'added_by', searchable: false},
                        { data: 'created_at',
                            name: 'repair_job_sheets.created_at'
                        }
                    ],
                    "fnDrawCallback": function (oSettings) {
                        __currency_convert_recursively($('#completed_job_sheets_table'));
                    }
            });
            // Delivered Job Sheets table
let delivered_job_sheets_datatable = $("#delivered_job_sheets_table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '/repair/job-sheet',
        data: function(d) {
            d.has_invoice = true;
            // Add any filters you already use
            d.location_id = $('#location_id').val();
            d.contact_id = $('#contact_id').val();
            d.status_id = $('#status_id').val();
             d.has_invoice = true; // ✅ this line only for delivered
        }
    },
    columns: [
        { data: 'action', name: 'action', orderable:false, searchable:false },
        { data: 'service_type', name: 'service_type' },
        { data: 'delivery_date', name: 'delivery_date' },
        { data: 'job_sheet_no', name: 'job_sheet_no' },
        { data: 'repair_no', name: 'repair_no' }, // Invoicenumber
        { data: 'status', name: 'rs.name' },
        { data: 'customer', name : 'contacts.name' },
        { data: 'contact_id', name: 'contacts.contact_id' },
        { data:'mobile', name: 'contacts.mobile' },
        { data: 'location', name: 'bl.name' },
        { data: 'brand', name: 'b.name' },
        { data: 'device', name: 'device.name' },
        { data: 'device_model', name: 'rdm.name' },
        { data: 'serial_no', name: 'serial_no' },
        { data: 'estimated_cost', name: 'estimated_cost' },
        { data: 'added_by', name: 'added_by', searchable: false },
        { data: 'created_at', name: 'repair_job_sheets.created_at' }
    ],
    fnDrawCallback: function() {
        __currency_convert_recursively($('#delivered_job_sheets_table'));
    }
});


            $(document).on('click', '#delete_job_sheet', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: url,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    pending_job_sheets_datatable.ajax.reload();
                                    completed_job_sheets_datatable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            @if(auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))
                $(document).on('click', '.edit_job_sheet_status', function () {
                    var url = $(this).data('href');
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'html',
                        success: function(result) {
                            $('#status_modal').html(result).modal('show');
                        }
                    });
                });
            @endif

            $('#status_modal').on('shown.bs.modal', function (e) {

                //initialize editor
                tinymce.init({
                    selector: 'textarea#email_body',
                });

                $('#send_sms').change(function() {
                    if ($(this). is(":checked")) {
                        $('div.sms_body').fadeIn();
                    } else {
                        $('div.sms_body').fadeOut();
                    }
                });

                $('#send_email').change(function() {
                    if ($(this). is(":checked")) {
                        $('div.email_template').fadeIn();
                    } else {
                        $('div.email_template').fadeOut();
                    }
                });

                if ($('#status_id_modal').length) {
                    ;
                    $("#sms_body").val($("#status_id_modal :selected").data('sms_template'));
                    $("#email_subject").val($("#status_id_modal :selected").data('email_subject'));
                    tinymce.activeEditor.setContent($("#status_id_modal :selected").data('email_body'));  
                }

                $('#status_id_modal').on('change', function() {
                    var sms_template = $(this).find(':selected').data('sms_template');
                    var email_subject = $(this).find(':selected').data('email_subject');
                    var email_body = $(this).find(':selected').data('email_body');

                    $("#sms_body").val(sms_template);
                    $("#email_subject").val(email_subject);
                    tinymce.activeEditor.setContent(email_body);

                    if ($('#status_modal .mark-as-complete-btn').length) {
                        if ($(this).find(':selected').data('is_completed_status') == 1) 
                        {
                            $('#status_modal').find('.mark-as-complete-btn').removeClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').addClass('hide');
                        } else {
                            $('#status_modal').find('.mark-as-complete-btn').addClass('hide');
                            $('#status_modal').find('.mark-as-incomplete-btn').removeClass('hide');
                        }
                    }
                });
            });
            
            $('#status_modal').on('hidden.bs.modal', function(){
                tinymce.remove("textarea#email_body");
            });
            
            $(document).on('click', '.update_status_button', function(){
                $('#status_form_redirect').val($(this).data('href'));
            })
            
            $(document).on('submit', 'form#update_status_form', function(e){
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector('.ladda-button'));
                ladda.start();
                $.ajax({
                    method: $(this).attr("method"),
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function(result){
                        ladda.stop();
                        if(result.success == true){
                            $('#status_modal').modal('hide');
                            if (result.msg) {
                                toastr.success(result.msg);
                            }

                            if ($('#status_form_redirect').val()) {
                                window.location = $('#status_form_redirect').val();
                            }
                            pending_job_sheets_datatable.ajax.reload();
                            completed_job_sheets_datatable.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change', '#location_id, #contact_id, #status_id, #technician',  function() {
                pending_job_sheets_datatable.ajax.reload();
                completed_job_sheets_datatable.ajax.reload();
            });

            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                        pending_job_sheets_datatable.ajax.reload();
                        completed_job_sheets_datatable.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                pending_job_sheets_datatable.ajax.reload();
                completed_job_sheets_datatable.ajax.reload();
            });
        });
     // When clicking the "Extend Time & Update Note" button for a specific job sheet:
$(document).on('click', '.extend_time_modal_btn', function() {
    let jobId = $(this).data('id');
let updateUrl = `/repair/job-sheet/${jobId}/update-delivery-note`; // Direct URL
$('#extend_time_form').attr('action', updateUrl);
    // (Optional: populate current values in modal fields here if needed)
    $('#extendTimeModal').modal('show');
});
document.querySelectorAll('[data-bs-target="#updateDueDateModal"]').forEach(btn => {
  btn.addEventListener('click', function () {
    const jobId = this.getAttribute('data-id');
    document.getElementById('modal_job_id').value = jobId;
  });
});

$(document).on('click', '.extend-time-btn', function(e) {
    const jobSheetId = $(this).data('id');
    $('#job_sheet_id').val(jobSheetId);
    const url = `/repair/job-sheet/${jobSheetId}/update-delivery-note`;
$('#extend_time_form').attr('action', url);

    // Fetch data for prefill
    $.get(`/repair/job-sheet/${jobSheetId}/get-delivery-note`, function(res) {
        $('#expected_delivery_date').val(res.expected_delivery_date);
        $('#update_note').val(res.update_note);
        $('#extendTimeModal').modal('show');
    }).fail(function() {
        toastr.error('Failed to fetch job sheet details.');
    });
});
$(document).ready(function() {
    $(document).on('click', '.job-summary-link', function(e) {
        e.preventDefault();
        const jobId = $(this).data('id');
        $('#jobSummaryModal').modal('show');
        $('#job-summary-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            url: '/repair/job-sheet/' + jobId + '/summary',
            method: 'GET',
            success: function(response) {
                $('#jobSummaryModal').html(response);
            },
            error: function() {
                $('#jobSummaryModal').html('<div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Failed to load data.</div></div></div></div>');
            }
        });
    });
});


    </script>
    <!-- Modal for Extend Time and Update Note -->
<div class="modal fade" id="extendTimeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
   {!! Form::open(['url' => route('jobsheet.updateDueDate'), 'method' => 'POST', 'id' => 'extend_time_form']) !!}
<div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title">Extend Time & Update Note</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="modal-body">
    {!! Form::hidden('job_id', null, ['id' => 'job_sheet_id']) !!}
    <div class="form-group">
      {!! Form::label('expected_delivery_date', 'New Delivery Date') !!}
      {!! Form::text('due_date', null, ['class' => 'form-control', 'id' => 'expected_delivery_date', 'required']) !!}
    </div>
    <div class="form-group">
      {!! Form::label('note', 'Job Sheet Note') !!}
      {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'update_note', 'required']) !!}
    </div>
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">Update</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  </div>
</div>
{!! Form::close() !!}
  </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
       flatpickr("#expected_delivery_date", {
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d H:i:S') }}"
});

flatpickr("#modal_due_date", {
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d H:i:S') }}"
});
</script>
@endsection
<!-- Update Due Date Modal -->
<div class="modal fade" id="updateDueDateModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="extend_time_form" method="POST" action="{{ route('jobsheet.updateDueDate') }}">
  @csrf
      <input type="hidden" name="job_id" id="modal_job_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Delivery Time</h5>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="due_date">New Due Date & Time</label>
            <input type="text" name="due_date" class="form-control" id="modal_due_date" required>

          </div>
          <div class="mb-3">
            <label for="note">Reason/Note</label>
            <textarea name="note" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Job Summary Modal -->
<div class="modal fade" id="jobSummaryModal" tabindex="-1" role="dialog" aria-labelledby="jobSummaryModalLabel" aria-hidden="true">
  <!-- Content will be loaded via AJAX -->
</div>