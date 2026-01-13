{{-- Classic invoice layout (simple / default-style) --}}

<div class="row">
    {{-- Logo / Letterhead --}}
    @if(empty($receipt_details->letter_head))
        @if(!empty($receipt_details->logo))
            <div class="col-xs-12 text-center">
                <img src="{{$receipt_details->logo}}" class="img img-responsive center-block" style="max-height: 120px;">
            </div>
        @endif

        {{-- Header text --}}
        @if(!empty($receipt_details->header_text))
            <div class="col-xs-12">
                {!! $receipt_details->header_text !!}
            </div>
        @endif

        {{-- Business information --}}
        <div class="col-xs-12 text-center">
            <h3 style="margin-bottom: 0;">
                @if(!empty($receipt_details->display_name))
                    {{$receipt_details->display_name}}
                @endif
            </h3>

            @if(!empty($receipt_details->address))
                <p style="margin-bottom: 0;">
                    {!! $receipt_details->address !!}
                </p>
            @endif

            @if(!empty($receipt_details->contact) || !empty($receipt_details->website))
                <p style="margin-bottom: 0;">
                    @if(!empty($receipt_details->contact))
                        {!! $receipt_details->contact !!}
                    @endif
                    @if(!empty($receipt_details->contact) && !empty($receipt_details->website))
                        ,
                    @endif
                    @if(!empty($receipt_details->website))
                        {{$receipt_details->website}}
                    @endif
                </p>
            @endif

            @if(!empty($receipt_details->location_custom_fields))
                <p style="margin-bottom: 0;">
                    {{ $receipt_details->location_custom_fields }}
                </p>
            @endif

            @if(!empty($receipt_details->sub_heading_line1) ||
                !empty($receipt_details->sub_heading_line2) ||
                !empty($receipt_details->sub_heading_line3) ||
                !empty($receipt_details->sub_heading_line4) ||
                !empty($receipt_details->sub_heading_line5))
                <p style="margin-bottom: 0;">
                    @if(!empty($receipt_details->sub_heading_line1))
                        {{ $receipt_details->sub_heading_line1 }}<br>
                    @endif
                    @if(!empty($receipt_details->sub_heading_line2))
                        {{ $receipt_details->sub_heading_line2 }}<br>
                    @endif
                    @if(!empty($receipt_details->sub_heading_line3))
                        {{ $receipt_details->sub_heading_line3 }}<br>
                    @endif
                    @if(!empty($receipt_details->sub_heading_line4))
                        {{ $receipt_details->sub_heading_line4 }}<br>
                    @endif
                    @if(!empty($receipt_details->sub_heading_line5))
                        {{ $receipt_details->sub_heading_line5 }}
                    @endif
                </p>
            @endif

            @if(!empty($receipt_details->tax_info1) || !empty($receipt_details->tax_info2))
                <p style="margin-bottom: 0;">
                    @if(!empty($receipt_details->tax_info1))
                        <strong>{{ $receipt_details->tax_label1 }}</strong> {{ $receipt_details->tax_info1 }}
                    @endif
                    @if(!empty($receipt_details->tax_info2))
                        &nbsp;&nbsp;
                        <strong>{{ $receipt_details->tax_label2 }}</strong> {{ $receipt_details->tax_info2 }}
                    @endif
                </p>
            @endif
        </div>
    @else
        <div class="col-xs-12 text-center">
            <img src="{{$receipt_details->letter_head}}" style="width: 100%; margin-bottom: 10px;">
        </div>
    @endif
</div>

{{-- Invoice heading --}}
@if(!empty($receipt_details->invoice_heading))
    <div class="row">
        <div class="col-xs-12 text-center">
            <h4 style="margin-top: 10px; margin-bottom: 10px;">
                {!! $receipt_details->invoice_heading !!}
            </h4>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-xs-12">
        <p style="margin-bottom: 5px;">
            <span class="pull-left text-left">
                {{-- Invoice number --}}
                @if(!empty($receipt_details->invoice_no_prefix))
                    <strong>{!! $receipt_details->invoice_no_prefix !!}</strong>
                @endif
                <strong>{{$receipt_details->invoice_no}}</strong>

                {{-- Types of service --}}
                @if(!empty($receipt_details->types_of_service))
                    <br>
                    <strong>{!! $receipt_details->types_of_service_label !!}:</strong>
                    {{$receipt_details->types_of_service}}
                    @if(!empty($receipt_details->types_of_service_custom_fields))
                        @foreach($receipt_details->types_of_service_custom_fields as $key => $value)
                            <br><strong>{{$key}}:</strong> {{$value}}
                        @endforeach
                    @endif
                @endif

                {{-- Table --}}
                @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
                    <br>
                    @if(!empty($receipt_details->table_label))
                        <strong>{!! $receipt_details->table_label !!}</strong>
                    @endif
                    {{$receipt_details->table}}
                @endif

                {{-- Customer --}}
                @if(!empty($receipt_details->customer_info))
                    <br>
                    <strong>{{ $receipt_details->customer_label }}</strong><br>
                    {!! $receipt_details->customer_info !!}
                @endif

                @if(!empty($receipt_details->client_id_label))
                    <br>
                    <strong>{{ $receipt_details->client_id_label }}</strong> {{ $receipt_details->client_id }}
                @endif

                @if(!empty($receipt_details->customer_tax_label))
                    <br>
                    <strong>{{ $receipt_details->customer_tax_label }}</strong> {{ $receipt_details->customer_tax_number }}
                @endif

                @if(!empty($receipt_details->customer_custom_fields))
                    <br>{!! $receipt_details->customer_custom_fields !!}
                @endif

                @if(!empty($receipt_details->sales_person_label))
                    <br>
                    <strong>{{ $receipt_details->sales_person_label }}</strong> {{ $receipt_details->sales_person }}
                @endif

                @if(!empty($receipt_details->commission_agent_label))
                    <br>
                    <strong>{{ $receipt_details->commission_agent_label }}</strong> {{ $receipt_details->commission_agent }}
                @endif

                @if(!empty($receipt_details->customer_rp_label))
                    <br>
                    <strong>{{ $receipt_details->customer_rp_label }}</strong> {{ $receipt_details->customer_total_rp }}
                @endif
            </span>

            <span class="pull-right text-left">
                {{-- Date --}}
                <strong>{{$receipt_details->date_label}}</strong> {{$receipt_details->invoice_date}}

                @if(!empty($receipt_details->due_date_label))
                    <br><strong>{{$receipt_details->due_date_label}}</strong> {{$receipt_details->due_date ?? ''}}
                @endif

                {{-- Repair fields (shown only if present) --}}
                @if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                    <br>
                    @if(!empty($receipt_details->brand_label))
                        <strong>{!! $receipt_details->brand_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_brand}}
                @endif

                @if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                    <br>
                    @if(!empty($receipt_details->device_label))
                        <strong>{!! $receipt_details->device_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_device}}
                @endif

                @if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                    <br>
                    @if(!empty($receipt_details->model_no_label))
                        <strong>{!! $receipt_details->model_no_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_model_no}}
                @endif

                @if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                    <br>
                    @if(!empty($receipt_details->serial_no_label))
                        <strong>{!! $receipt_details->serial_no_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_serial_no}}
                @endif

                @if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                    <br>
                    @if(!empty($receipt_details->repair_status_label))
                        <strong>{!! $receipt_details->repair_status_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_status}}
                @endif

                @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                    <br>
                    @if(!empty($receipt_details->repair_warranty_label))
                        <strong>{!! $receipt_details->repair_warranty_label !!}</strong>
                    @endif
                    {{$receipt_details->repair_warranty}}
                @endif

                {{-- Service staff --}}
                @if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                    <br>
                    @if(!empty($receipt_details->service_staff_label))
                        <strong>{!! $receipt_details->service_staff_label !!}</strong>
                    @endif
                    {{$receipt_details->service_staff}}
                @endif
            </span>
        </p>
    </div>
</div>

{{-- Common repair partial (if any) --}}
<div class="row">
    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>

<hr>

{{-- Items table --}}
<div class="row">
    <div class="col-xs-12">
        @php
            $p_width = 45;
        @endphp
        @if(!empty($receipt_details->item_discount_label))
            @php $p_width -= 10; @endphp
        @endif
        @if(!empty($receipt_details->discounted_unit_price_label))
            @php $p_width -= 10; @endphp
        @endif

        <table class="table table-responsive table-slim">
            <thead>
                <tr>
                    <th width="{{$p_width}}%">{{$receipt_details->table_product_label}}</th>
                    <th class="text-right" width="15%">{{$receipt_details->table_qty_label}}</th>
                    <th class="text-right" width="15%">{{$receipt_details->table_unit_price_label}}</th>
                    @if(!empty($receipt_details->discounted_unit_price_label))
                        <th class="text-right" width="10%">{{$receipt_details->discounted_unit_price_label}}</th>
                    @endif
                    @if(!empty($receipt_details->item_discount_label))
                        <th class="text-right" width="10%">{{$receipt_details->item_discount_label}}</th>
                    @endif
                    <th class="text-right" width="15%">{{$receipt_details->table_subtotal_label}}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipt_details->lines as $line)
                    <tr>
                        <td>
                            {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}}
                            @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif
                            @if(!empty($line['brand'])), {{$line['brand']}} @endif
                            @if(!empty($line['cat_code'])), {{$line['cat_code']}} @endif

                            @if(!empty($line['product_description']))
                                <br><small>{!! $line['product_description'] !!}</small>
                            @endif

                            @if(!empty($line['sell_line_note']))
                                <br><small>{!! $line['sell_line_note'] !!}</small>
                            @endif

                            @if(!empty($line['lot_number']))
                                <br>{{$line['lot_number_label']}}: {{$line['lot_number']}}
                            @endif

                            @if(!empty($line['product_expiry']))
                                , {{$line['product_expiry_label']}}: {{$line['product_expiry']}}
                            @endif
                        </td>
                        <td class="text-right">
                            {{$line['quantity']}} {{$line['units']}}
                        </td>
                        <td class="text-right">{{$line['unit_price_before_discount']}}</td>
                        @if(!empty($receipt_details->discounted_unit_price_label))
                            <td class="text-right">{{$line['unit_price_inc_tax']}}</td>
                        @endif
                        @if(!empty($receipt_details->item_discount_label))
                            <td class="text-right">
                                {{$line['total_line_discount'] ?? '0.00'}}
                                @if(!empty($line['line_discount_percent']))
                                    ({{$line['line_discount_percent']}}%)
                                @endif
                            </td>
                        @endif
                        <td class="text-right">{{$line['line_total']}}</td>
                    </tr>

                    @if(!empty($line['modifiers']))
                        @foreach($line['modifiers'] as $modifier)
                            <tr>
                                <td>
                                    {{$modifier['name']}} {{$modifier['variation']}}
                                    @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif
                                    @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}} @endif
                                    @if(!empty($modifier['sell_line_note']))
                                        ({!!$modifier['sell_line_note']!!})
                                    @endif
                                </td>
                                <td class="text-right">{{$modifier['quantity']}} {{$modifier['units']}}</td>
                                <td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
                                @if(!empty($receipt_details->discounted_unit_price_label))
                                    <td class="text-right">{{$modifier['unit_price_exc_tax']}}</td>
                                @endif
                                @if(!empty($receipt_details->item_discount_label))
                                    <td class="text-right">0.00</td>
                                @endif
                                <td class="text-right">{{$modifier['line_total']}}</td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<hr>

{{-- Totals / payments --}}
<div class="row">
    <div class="col-xs-6">
        <table class="table table-slim">
            <tbody>
                {{-- Total paid --}}
                @if(!empty($receipt_details->total_paid))
                    <tr>
                        <th>{!! $receipt_details->total_paid_label !!}</th>
                        <td class="text-right">{{$receipt_details->total_paid}}</td>
                    </tr>
                @endif

                {{-- Total due --}}
                @if(!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                    <tr>
                        <th>{!! $receipt_details->total_due_label !!}</th>
                        <td class="text-right">{{$receipt_details->total_due}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->all_due))
                    <tr>
                        <th>{!! $receipt_details->all_bal_label !!}</th>
                        <td class="text-right">{{$receipt_details->all_due}}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- Change Return --}}
        @if(!empty($receipt_details->change_return))
            <tr>
                <th>@lang('lang_v1.change_return'):</th>
                <td class="text-right">{{$receipt_details->change_return['amount']}}</td>
            </tr>
        @endif

        {{-- Payments list --}}
        @if(!empty($receipt_details->payments))
            <table class="table table-slim">
                <thead>
                    <tr>
                        <th>@lang('lang_v1.payment_method')</th>
                        <th class="text-right">@lang('sale.amount')</th>
                        <th class="text-right">@lang('sale.date')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt_details->payments as $payment)
                        <tr>
                            <td>{{$payment['method']}}</td>
                            <td class="text-right">{{$payment['amount']}}</td>
                            <td class="text-right">{{$payment['date']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="col-xs-6">
        <table class="table table-slim">
            <tbody>
                <tr>
                    <th>{!! $receipt_details->subtotal_label !!}</th>
                    <td class="text-right">{{$receipt_details->subtotal}}</td>
                </tr>

                @if(!empty($receipt_details->discount))
                    <tr>
                        <th>{!! $receipt_details->discount_label !!}</th>
                        <td class="text-right">(-) {{$receipt_details->discount}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->total_line_discount))
                    <tr>
                        <th>{!! $receipt_details->line_discount_label !!}</th>
                        <td class="text-right">(-) {{$receipt_details->total_line_discount}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->shipping_charges))
                    <tr>
                        <th>{!! $receipt_details->shipping_charges_label !!}</th>
                        <td class="text-right">{{$receipt_details->shipping_charges}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->packing_charge))
                    <tr>
                        <th>{!! $receipt_details->packing_charge_label !!}</th>
                        <td class="text-right">{{$receipt_details->packing_charge}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->additional_expenses))
                    @foreach($receipt_details->additional_expenses as $key => $val)
                        <tr>
                            <td>{{$key}}:</td>
                            <td class="text-right">(+) {{$val}}</td>
                        </tr>
                    @endforeach
                @endif

                @if(!empty($receipt_details->reward_point_label))
                    <tr>
                        <th>{!! $receipt_details->reward_point_label !!}</th>
                        <td class="text-right">(-) {{$receipt_details->reward_point_amount}}</td>
                    </tr>
                @endif

                @if(!empty($receipt_details->tax))
                    <tr>
                        <th>{!! $receipt_details->tax_label !!}</th>
                        <td class="text-right">(+) {{$receipt_details->tax}}</td>
                    </tr>
                @endif

                @if($receipt_details->round_off_amount > 0)
                    <tr>
                        <th>{!! $receipt_details->round_off_label !!}</th>
                        <td class="text-right">{{$receipt_details->round_off}}</td>
                    </tr>
                @endif

                <tr>
                    <th>{!! $receipt_details->total_label !!}</th>
                    <td class="text-right">
                        {{$receipt_details->total}}
                        @if(!empty($receipt_details->total_in_words))
                            <br><small>({{$receipt_details->total_in_words}})</small>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Tax summary --}}
@if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) && !empty($receipt_details->taxes))
    <hr>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-slim">
                <tr>
                    <th colspan="2" class="text-center">{{$receipt_details->tax_summary_label}}</th>
                </tr>
                @foreach($receipt_details->taxes as $key => $val)
                    <tr>
                        <td class="text-center"><strong>{{$key}}</strong></td>
                        <td class="text-center">{{$val}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endif

{{-- Notes --}}
@if(!empty($receipt_details->additional_notes))
    <hr>
    <div class="row">
        <div class="col-xs-12">
            {!! nl2br($receipt_details->additional_notes) !!}
        </div>
    </div>
@endif

{{-- Footer / Barcode / QR --}}
<hr>
<div class="row">
    @if(!empty($receipt_details->footer_text))
        <div class="@if($receipt_details->show_barcode || $receipt_details->show_qr_code) col-xs-8 @else col-xs-12 @endif">
            {!! $receipt_details->footer_text !!}
        </div>
    @endif

    @if($receipt_details->show_barcode || $receipt_details->show_qr_code)
        <div class="@if(!empty($receipt_details->footer_text)) col-xs-4 @else col-xs-12 @endif text-center">
            @if($receipt_details->show_barcode)
                <img class="center-block"
                     src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
            @endif

            @if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                <img class="center-block"
                     src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
            @endif
        </div>
    @endif
</div>
