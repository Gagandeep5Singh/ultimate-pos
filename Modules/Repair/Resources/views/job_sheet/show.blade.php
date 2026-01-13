@extends('layouts.app')

@section('title', __('repair::lang.view_job_sheet'))

@section('content')
@include('repair::layouts.nav')

@php
$custom_labels = json_decode(session('business.custom_labels'), true);
$contact_custom_fields = !empty($jobsheet_settings['contact_custom_fields']) ? 
$jobsheet_settings['contact_custom_fields'] : [];
@endphp
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('repair::lang.job_sheet')
        (<code>{{$job_sheet->job_sheet_no}}</code>)
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header no-print">
                    <div class="box-tools">
                        @if(auth()->user()->can("job_sheet.edit"))
                        <a href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'edit'], [$job_sheet->id])}}" class="tw-dw-btn tw-dw-btn-info tw-text-white tw-dw-btn-sm cursor-pointer">
                            <i class="fa fa-edit"></i>
                            @lang("messages.edit")
                        </a>
                        @endif
                        <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" aria-label="Print" id="print_jobsheet">
                            <i class="fa fa-print"></i>
                            @lang( 'repair::lang.print_format_1' )
                        </button>

                        <a class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm" href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'print'], [$job_sheet->id])}}" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                            @lang( 'repair::lang.print_format_2' )
                        </a>

                        <a class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm" href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'printLabel'], [$job_sheet->id])}}" target="_blank">
                            <i class="fas fa-barcode"></i>
                            @lang( 'repair::lang.print_label' )
                        </a>
                    </div>
                </div>
                <div class="box-body" id="job_sheet">
                    @php
                        $product_configuration = !empty($job_sheet->product_configuration) ? json_decode($job_sheet->product_configuration, true) : [];
                        $product_condition = !empty($job_sheet->product_condition) ? json_decode($job_sheet->product_condition, true) : [];
                        $defects = !empty($job_sheet->defects) ? json_decode($job_sheet->defects, true) : [];
                        $checklists = [];
                        if (!empty($job_sheet->deviceModel) && !empty($job_sheet->deviceModel->repair_checklist)) {
                            $checklists = explode('|', $job_sheet->deviceModel->repair_checklist);
                        }
                        if (!empty($repair_settings['default_repair_checklist'])) {
                            $checklists = array_merge(explode('|', $repair_settings['default_repair_checklist']), $checklists);
                        }
                    @endphp
                    {{-- Copy 1 (Customer) --}}
                    <table class="table table-bordered">
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                @if(!empty(Session::get('business.logo')))
                                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo" style="max-height: 90px;"><br>
                                @endif
                                <strong class="font-23">{{$job_sheet->customer->business->name}}</strong><br>
                                @if(!empty($job_sheet->businessLocation))
                                    {!! $job_sheet->businessLocation->location_address !!}<br>
                                @endif
                                @if(!empty($job_sheet->businessLocation->mobile))
                                    @lang('business.mobile'): {{$job_sheet->businessLocation->mobile}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->alternate_number))
                                    @lang('invoice.show_alternate_number'): {{$job_sheet->businessLocation->alternate_number}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->email))
                                    @lang('business.email'): {{$job_sheet->businessLocation->email}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->website))
                                    @lang('lang_v1.website'): {{$job_sheet->businessLocation->website}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <small>(Customer Copy)</small><br>
                                @lang('receipt.date'): <span>{{@format_datetime($job_sheet->created_at)}}</span>
                            </td>
                            <td style="text-align: center;">
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($job_sheet->job_sheet_no, 'C128', 2, 30) }}" alt="{{$job_sheet->job_sheet_no}}"><br>
                                <strong>{{$job_sheet->job_sheet_no}}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.service_type'):</b> @lang('repair::lang.'.$job_sheet->service_type)</td>
                            <td><b>@lang('lang_v1.due_date'):</b>
                                @if(!empty($job_sheet->delivery_date))
                                    <span>{{@format_datetime($job_sheet->delivery_date)}}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if(!empty($jobsheet_settings['customer_label']))
                                    <b>{{$jobsheet_settings['customer_label']}}:</b><br>
                                @endif
                                <p>
                                    {!! $job_sheet->customer->contact_address !!}
                                    @if(!empty($jobsheet_settings['show_client_id']))
                                        <br>{{$jobsheet_settings['client_id_label'] ?? ''}}: {{$job_sheet->customer->contact_id}}
                                    @endif
                                    @if(!empty($job_sheet->customer->email))
                                        <br>@lang('business.email'): {{$job_sheet->customer->email}}
                                    @endif
                                    <br>@lang('contact.mobile'): {{$job_sheet->customer->mobile}}
                                    @if(!empty($job_sheet->customer->tax_number))
                                        <br>{{$jobsheet_settings['client_tax_label'] ?? ''}}: {{$job_sheet->customer->tax_number}}
                                    @endif
                                    @if(in_array('custom_field1', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}: {{$job_sheet->customer->custom_field1}}
                                    @endif
                                    @if(in_array('custom_field2', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}: {{$job_sheet->customer->custom_field2}}
                                    @endif
                                    @if(in_array('custom_field3', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}: {{$job_sheet->customer->custom_field3}}
                                    @endif
                                    @if(in_array('custom_field4', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}: {{$job_sheet->customer->custom_field4}}
                                    @endif
                                </p>
                            </td>
                            <td>
                                <b>@lang('product.brand'):</b> {{$job_sheet->brand?->name}}<br>
                                <b>@lang('repair::lang.device'):</b> {{$job_sheet->device?->name}}<br>
                                <b>@lang('repair::lang.device_model'):</b> {{$job_sheet->deviceModel?->name}}<br>
                                <b>@lang('repair::lang.serial_no'):</b> {{$job_sheet->serial_no}}<br>
                                <b>@lang('lang_v1.password'):</b> {{$job_sheet->security_pwd}}<br>
                                <b>@lang('repair::lang.security_pattern_code'):</b> {{$job_sheet->security_pattern}}
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('sale.invoice_no'):</b></td>
                            <td>
                                @if($job_sheet->invoices->count() > 0)
                                    @foreach($job_sheet->invoices as $invoice)
                                        {{$invoice->invoice_no}}{{ !$loop->last ? ', ' : ''}}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.estimated_cost'):</b></td>
                            <td><span class="display_currency" data-currency_symbol="true">{{$job_sheet->estimated_cost}}</span></td>
                        </tr>
                        <tr>
                            <td><b>@lang('sale.status'):</b></td>
                            <td>{{$job_sheet->status?->name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('business.location'):</b></td>
                            <td>{{$job_sheet->businessLocation?->name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.technician'):</b></td>
                            <td>{{$job_sheet->technician?->user_full_name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.comment_by_ss'):</b></td>
                            <td>{{$job_sheet->comment_by_ss}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.pre_repair_checklist'):</b></td>
                            <td>
                                @if(!empty($checklists))
                                    <div class="row">
                                        @foreach($checklists as $check)
                                            @if(isset($job_sheet->checklist[$check]))
                                                <div class="col-xs-6">
                                                    @if($job_sheet->checklist[$check] == 'yes')
                                                        <i class="fas fa-check-square text-success fa-lg"></i>
                                                    @elseif($job_sheet->checklist[$check] == 'no')
                                                        <i class="fas fa-window-close text-danger fa-lg"></i>
                                                    @elseif($job_sheet->checklist[$check] == 'not_applicable')
                                                        <i class="fas fa-square fa-lg"></i>
                                                    @endif
                                                    {{$check}}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if($job_sheet->service_type == 'pick_up' || $job_sheet->service_type == 'on_site')
                        <tr>
                            <td><b>@lang('repair::lang.pick_up_on_site_addr'):</b></td>
                            <td>{!! $job_sheet->pick_up_on_site_addr !!}</td>
                        </tr>
                        @endif
                        @if(!empty($product_configuration))
                        <tr>
                            <td><b>@lang('repair::lang.product_configuration'):</b></td>
                            <td>
                                @foreach($product_configuration as $product_conf)
                                    {{$product_conf['value']}}{{ !$loop->last ? ', ' : ''}}
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(!empty($product_condition))
                        <tr>
                            <td><b>@lang('repair::lang.condition_of_product'):</b></td>
                            <td>
                                @foreach($product_condition as $product_cond)
                                    {{$product_cond['value']}}{{ !$loop->last ? ', ' : ''}}
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_1))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_1'] ?? __('lang_v1.custom_field', ['number' => 1])}}:</b></td>
                            <td>{{$job_sheet->custom_field_1}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><b>@lang('repair::lang.parts_used'):</b></td>
                            <td>
                                @if(!empty($parts))
                                    <table>
                                        @foreach($parts as $part)
                                            <tr>
                                                <td>{{$part['variation_name']}}: &nbsp;</td>
                                                <td>{{$part['quantity']}} {{$part['unit']}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($job_sheet->custom_field_2))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_2'] ?? __('lang_v1.custom_field', ['number' => 2])}}:</b></td>
                            <td>{{$job_sheet->custom_field_2}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_3))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_3'] ?? __('lang_v1.custom_field', ['number' => 3])}}:</b></td>
                            <td>{{$job_sheet->custom_field_3}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_4))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_4'] ?? __('lang_v1.custom_field', ['number' => 4])}}:</b></td>
                            <td>{{$job_sheet->custom_field_4}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_5))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5])}}:</b></td>
                            <td>{{$job_sheet->custom_field_5}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><b>@lang('repair::lang.problem_reported_by_customer'):</b></td>
                            <td>
                                @if(!empty($defects))
                                    @foreach($defects as $product_defect)
                                        {{$product_defect['value']}}{{ !$loop->last ? ', ' : ''}}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>@lang("lang_v1.terms_conditions"):</strong><br>
                                @if(!empty($repair_settings['repair_tc_condition']))
                                    {!! $repair_settings['repair_tc_condition'] !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>@lang('repair::lang.customer_signature'):</strong></td>
                            <td>
                                <strong>@lang('repair::lang.authorized_signature'):</strong><br>
                                
                            </td>
                        </tr>
                    </table>
                    <br>
                    {{-- Copy 2 (Office) --}}
                    <table class="table table-bordered">
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                @if(!empty(Session::get('business.logo')))
                                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo" style="max-height: 90px;"><br>
                                @endif
                                <strong class="font-23">{{$job_sheet->customer->business->name}}</strong><br>
                                @if(!empty($job_sheet->businessLocation))
                                    {!! $job_sheet->businessLocation->location_address !!}<br>
                                @endif
                                @if(!empty($job_sheet->businessLocation->mobile))
                                    @lang('business.mobile'): {{$job_sheet->businessLocation->mobile}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->alternate_number))
                                    @lang('invoice.show_alternate_number'): {{$job_sheet->businessLocation->alternate_number}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->email))
                                    @lang('business.email'): {{$job_sheet->businessLocation->email}},
                                @endif
                                @if(!empty($job_sheet->businessLocation->website))
                                    @lang('lang_v1.website'): {{$job_sheet->businessLocation->website}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <small>(Office Copy)</small><br>
                                @lang('receipt.date'): <span>{{@format_datetime($job_sheet->created_at)}}</span>
                            </td>
                            <td style="text-align: center;">
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($job_sheet->job_sheet_no, 'C128', 2, 30) }}" alt="{{$job_sheet->job_sheet_no}}"><br>
                                <strong>{{$job_sheet->job_sheet_no}}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.service_type'):</b> @lang('repair::lang.'.$job_sheet->service_type)</td>
                            <td><b>@lang('lang_v1.due_date'):</b>
                                @if(!empty($job_sheet->delivery_date))
                                    <span>{{@format_datetime($job_sheet->delivery_date)}}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if(!empty($jobsheet_settings['customer_label']))
                                    <b>{{$jobsheet_settings['customer_label']}}:</b><br>
                                @endif
                                <p>
                                    {!! $job_sheet->customer->contact_address !!}
                                    @if(!empty($jobsheet_settings['show_client_id']))
                                        <br>{{$jobsheet_settings['client_id_label'] ?? ''}}: {{$job_sheet->customer->contact_id}}
                                    @endif
                                    @if(!empty($job_sheet->customer->email))
                                        <br>@lang('business.email'): {{$job_sheet->customer->email}}
                                    @endif
                                    <br>@lang('contact.mobile'): {{$job_sheet->customer->mobile}}
                                    @if(!empty($job_sheet->customer->tax_number))
                                        <br>{{$jobsheet_settings['client_tax_label'] ?? ''}}: {{$job_sheet->customer->tax_number}}
                                    @endif
                                    @if(in_array('custom_field1', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}: {{$job_sheet->customer->custom_field1}}
                                    @endif
                                    @if(in_array('custom_field2', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}: {{$job_sheet->customer->custom_field2}}
                                    @endif
                                    @if(in_array('custom_field3', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}: {{$job_sheet->customer->custom_field3}}
                                    @endif
                                    @if(in_array('custom_field4', $contact_custom_fields))
                                        <br>{{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}: {{$job_sheet->customer->custom_field4}}
                                    @endif
                                </p>
                            </td>
                            <td>
                                <b>@lang('product.brand'):</b> {{$job_sheet->brand?->name}}<br>
                                <b>@lang('repair::lang.device'):</b> {{$job_sheet->device?->name}}<br>
                                <b>@lang('repair::lang.device_model'):</b> {{$job_sheet->deviceModel?->name}}<br>
                                <b>@lang('repair::lang.serial_no'):</b> {{$job_sheet->serial_no}}<br>
                                <b>@lang('lang_v1.password'):</b> {{$job_sheet->security_pwd}}<br>
                                <b>@lang('repair::lang.security_pattern_code'):</b> {{$job_sheet->security_pattern}}
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('sale.invoice_no'):</b></td>
                            <td>
                                @if($job_sheet->invoices->count() > 0)
                                    @foreach($job_sheet->invoices as $invoice)
                                        {{$invoice->invoice_no}}{{ !$loop->last ? ', ' : ''}}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.estimated_cost'):</b></td>
                            <td><span class="display_currency" data-currency_symbol="true">{{$job_sheet->estimated_cost}}</span></td>
                        </tr>
                        <tr>
                            <td><b>@lang('sale.status'):</b></td>
                            <td>{{$job_sheet->status?->name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('business.location'):</b></td>
                            <td>{{$job_sheet->businessLocation?->name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.technician'):</b></td>
                            <td>{{$job_sheet->technician?->user_full_name}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.comment_by_ss'):</b></td>
                            <td>{{$job_sheet->comment_by_ss}}</td>
                        </tr>
                        <tr>
                            <td><b>@lang('repair::lang.pre_repair_checklist'):</b></td>
                            <td>
                                @if(!empty($checklists))
                                    <div class="row">
                                        @foreach($checklists as $check)
                                            @if(isset($job_sheet->checklist[$check]))
                                                <div class="col-xs-6">
                                                    @if($job_sheet->checklist[$check] == 'yes')
                                                        <i class="fas fa-check-square text-success fa-lg"></i>
                                                    @elseif($job_sheet->checklist[$check] == 'no')
                                                        <i class="fas fa-window-close text-danger fa-lg"></i>
                                                    @elseif($job_sheet->checklist[$check] == 'not_applicable')
                                                        <i class="fas fa-square fa-lg"></i>
                                                    @endif
                                                    {{$check}}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if($job_sheet->service_type == 'pick_up' || $job_sheet->service_type == 'on_site')
                        <tr>
                            <td><b>@lang('repair::lang.pick_up_on_site_addr'):</b></td>
                            <td>{!! $job_sheet->pick_up_on_site_addr !!}</td>
                        </tr>
                        @endif
                        @if(!empty($product_configuration))
                        <tr>
                            <td><b>@lang('repair::lang.product_configuration'):</b></td>
                            <td>
                                @foreach($product_configuration as $product_conf)
                                    {{$product_conf['value']}}{{ !$loop->last ? ', ' : ''}}
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(!empty($product_condition))
                        <tr>
                            <td><b>@lang('repair::lang.condition_of_product'):</b></td>
                            <td>
                                @foreach($product_condition as $product_cond)
                                    {{$product_cond['value']}}{{ !$loop->last ? ', ' : ''}}
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_1))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_1'] ?? __('lang_v1.custom_field', ['number' => 1])}}:</b></td>
                            <td>{{$job_sheet->custom_field_1}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><b>@lang('repair::lang.parts_used'):</b></td>
                            <td>
                                @if(!empty($parts))
                                    <table>
                                        @foreach($parts as $part)
                                            <tr>
                                                <td>{{$part['variation_name']}}: &nbsp;</td>
                                                <td>{{$part['quantity']}} {{$part['unit']}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($job_sheet->custom_field_2))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_2'] ?? __('lang_v1.custom_field', ['number' => 2])}}:</b></td>
                            <td>{{$job_sheet->custom_field_2}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_3))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_3'] ?? __('lang_v1.custom_field', ['number' => 3])}}:</b></td>
                            <td>{{$job_sheet->custom_field_3}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_4))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_4'] ?? __('lang_v1.custom_field', ['number' => 4])}}:</b></td>
                            <td>{{$job_sheet->custom_field_4}}</td>
                        </tr>
                        @endif
                        @if(!empty($job_sheet->custom_field_5))
                        <tr>
                            <td><b>{{$repair_settings['job_sheet_custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5])}}:</b></td>
                            <td>{{$job_sheet->custom_field_5}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><b>@lang('repair::lang.problem_reported_by_customer'):</b></td>
                            <td>
                                @if(!empty($defects))
                                    @foreach($defects as $product_defect)
                                        {{$product_defect['value']}}{{ !$loop->last ? ', ' : ''}}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                        
                            </td>
                        </tr>
                        <tr>
                            <td><strong>@lang('repair::lang.customer_signature'):</strong></td>
                            <td>
                                <strong>@lang('repair::lang.authorized_signature'):</strong><br>
                              
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @if($job_sheet->media->count() > 0)
        <div class="col-md-6">
            <div class="box box-solid no-print">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        @lang('repair::lang.uploaded_image_for', ['job_sheet_no' => $job_sheet->job_sheet_no])
                    </h4>
                </div>
                <div class="box-body">
                    @includeIf('repair::job_sheet.partials.document_table_view',  ['medias' => $job_sheet->media])
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-6">
            <div class="box box-solid box-solid no-print">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('repair::lang.activities') }}:</h3>
                </div>
                <!-- /.box-header -->
                @include('repair::repair.partials.activities')
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@stop
@section('css')
<style type="text/css">
    .table-bordered {
        width: 100%; /* Ensure the table takes full width */
        table-layout: fixed; /* Use fixed table layout */
    }
    .table-bordered>thead>tr>th,
    .table-bordered>tbody>tr>th,
    .table-bordered>tfoot>tr>th,
    .table-bordered>thead>tr>td,
    .table-bordered>tbody>tr>td,
    .table-bordered>tfoot>tr>td {
        border: 1px solid #1d1a1a;
        word-wrap: break-word; /* Allow text to wrap */
        padding: 5px; /* Add some padding */
    }
   @media print {
    .no-print {
        display: none; /* Hide elements that should not appear in print */
    }
    .content {
        width: 80mm; /* Set print content width */
    }
}
</style>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#print_jobsheet').click(function() {
            $('#job_sheet').printThis();
        });
        $(document).on('click', '.delete_media', function(e) {
            e.preventDefault();
            var url = $(this).data('href');
            var this_btn = $(this);
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: 'GET',
                        url: url,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                this_btn.closest('tr').remove();
                                toastr.success(result.msg);
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@stop
