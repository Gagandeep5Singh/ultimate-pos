<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\CashRegisterController::class, 'postCloseRegister']), 'method' => 'post' ]) !!}

    {!! Form::hidden('user_id', is_object($register_details) ? $register_details->user_id : ($register_details['user_id'] ?? null)); !!}
    {!! Form::hidden('register_id', is_object($register_details) ? ($register_details->id ?? null) : ($register_details['id'] ?? null)); !!}
    {!! Form::hidden('location_id', is_object($register_details) ? ($register_details->location_id ?? null) : ($register_details['location_id'] ?? null)); !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      @php
        $open_time = is_object($register_details) ? ($register_details->open_time ?? \Carbon::now()) : ($register_details['open_time'] ?? \Carbon::now());
        if (is_string($open_time)) {
            $open_time = \Carbon::createFromFormat('Y-m-d H:i:s', $open_time);
        }
      @endphp
      <h3 class="modal-title">@lang( 'cash_register.current_register' ) ( {{ $open_time->format('jS M, Y h:i A') }} - {{ \Carbon::now()->format('jS M, Y h:i A') }})</h3>
    </div>

    <div class="modal-body">
        {{-- Custom Payment Summary Table --}}
        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <tr>
                        <th>@lang('lang_v1.payment_method'):</th>
                        <th class="text-right">@lang('sale.sale')</th>
                        <th class="text-right">@lang('lang_v1.expense')</th>
                    </tr>
                    <tr>
                        <td>@lang('cash_register.cash_in_hand'):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Total Cash Payment:</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash ?? 0 }}</span>
                        </td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_expense ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Total Card Payment:</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card ?? 0 }}</span>
                        </td>
                         <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_expense ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Multibanco ({{$payment_types['custom_pay_1'] ?? ''}}):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>MD ({{$payment_types['custom_pay_2'] ?? ''}}):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Gs Pay ({{$payment_types['custom_pay_3'] ?? ''}}):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @if(!empty($payment_types['custom_pay_4']))
                    <tr>
                        <td>{{$payment_types['custom_pay_4']}}:</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @endif
                    @if(!empty($payment_types['custom_pay_5']))
                    <tr>
                        <td>{{$payment_types['custom_pay_5']}}:</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @endif
                    @if(!empty($payment_types['custom_pay_6']))
                    <tr>
                        <td>{{$payment_types['custom_pay_6']}} (Klarna):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @endif
                    @if(!empty($payment_types['custom_pay_7']))
                    <tr>
                        <td>{{$payment_types['custom_pay_7']}}:</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7 ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @endif
                    @if(!empty($payment_types['bank_transfer']))
                    <tr>
                        <td>@lang('lang_v1.bank_transfer'):</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer ?? 0 }}</span>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    @endif
                    <tr class="info">
                        <th colspan="3" class="text-center"><strong>@lang('cash_register.refunds') - @lang('lang_v1.payment_method'):</strong></th>
                    </tr>
                    @if(($register_details->total_cash_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;@lang('lang_v1.cash') @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_cash_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_card_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;@lang('lang_v1.card') @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_card_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_1_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_1'] ?? 'Custom Pay 1'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_1_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_2_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_2'] ?? 'Custom Pay 2'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_2_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_3_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_3'] ?? 'Custom Pay 3'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_3_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_4_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_4'] ?? 'Custom Pay 4'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_4_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_5_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_5'] ?? 'Custom Pay 5'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_5_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_6_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_6'] ?? 'Klarna'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_6_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_custom_pay_7_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{$payment_types['custom_pay_7'] ?? 'Custom Pay 7'}} @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_custom_pay_7_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_bank_transfer_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;@lang('lang_v1.bank_transfer') @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_bank_transfer_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    @if(($register_details->total_advance_refund ?? 0) > 0)
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;@lang('lang_v1.advance') @lang('cash_register.refund'):</td>
                        <td>&nbsp;</td>
                        <td class="text-right text-danger">
                            <span class="display_currency" data-currency_symbol="true">-{{ $register_details->total_advance_refund }}</span>
                        </td>
                    </tr>
                    @endif
                    <tr class="success">
                        <th>@lang('cash_register.total_refund'):</th>
                        <td>&nbsp;</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_refund ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr class="success">
                        <th>@lang('cash_register.total_payment'):</th>
                        <td>&nbsp;</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ ($register_details->cash_in_hand ?? 0) + ($register_details->total_sale ?? 0) - ($register_details->total_refund ?? 0) }}</span>
                        </td>
                    </tr>
                    <tr class="success">
                        <th>@lang('cash_register.total_sales'):</th>
                        <td>&nbsp;</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr class="danger">
                        <th>@lang('expense.total_expense'):</th>
                        <td>&nbsp;</td>
                        <td class="text-right">
                            <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_expense ?? 0 }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        
        {{-- Input fields at the bottom --}}
        <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('closing_amount', __( 'cash_register.total_cash' ) . ':*') !!}
                  {!! Form::text('closing_amount', @num_format(($register_details->cash_in_hand ?? 0) + ($register_details->total_cash ?? 0)), ['class' => 'form-control input_number', 'required', 'placeholder' => __( 'cash_register.total_cash' ) ]); !!}
              </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('total_card_payment_closed', 'Card Payment:*') !!}
                    {!! Form::text('total_card_payment_closed', @num_format($register_details->total_card ?? 0), ['class' => 'form-control input_number', 'required', 'placeholder' => 'Card Payment' ]); !!}
                </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('total_cheques', __( 'cash_register.total_cheques' ) . ':*') !!} @show_tooltip(__('tooltip.total_cheques'))
                  {!! Form::number('total_cheques', $register_details->total_cheques ?? 0, ['class' => 'form-control', 'required', 'placeholder' => __( 'cash_register.total_cheques' ), 'min' => 0 ]); !!}
              </div>
            </div> 
        </div>
        <hr>
        
        {{-- Cash Denominations and Closing Note --}}
        <div class="row">
            <div class="col-md-8 col-sm-12">
              <h3>@lang( 'lang_v1.cash_denominations' )</h3>
              @if(!empty($pos_settings['cash_denominations']))
                <table class="table table-slim">
                  <thead>
                    <tr>
                      <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                      <th width="20%">&nbsp;</th>
                      <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                      <th width="20%">&nbsp;</th>
                      <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach(explode(',', $pos_settings['cash_denominations']) as $dnm)
                    <tr>
                      <td class="text-right">{{$dnm}}</td>
                      <td class="text-center" >X</td>
                      <td>{!! Form::number("denominations[$dnm]", null, ['class' => 'form-control cash_denomination input-sm', 'min' => 0, 'data-denomination' => $dnm, 'style' => 'width: 100px; margin:auto;' ]); !!}</td>
                      <td class="text-center">=</td>
                      <td class="text-left">
                        <span class="denomination_subtotal">0</span>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="4" class="text-center">@lang('sale.total')</th>
                      <td><span class="denomination_total">0</span></td>
                    </tr>
                  </tfoot>
                </table>
              @else
                <p class="help-block">@lang('lang_v1.denomination_add_help_text')</p>
              @endif
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                {!! Form::label('closing_note', __( 'cash_register.closing_note' ) . ':') !!}
                  {!! Form::textarea('closing_note', null, ['class' => 'form-control', 'placeholder' => __( 'cash_register.closing_note' ), 'rows' => 3 ]); !!}
              </div>
            </div>
        </div> 

        <div class="row">
            <div class="col-xs-6">
              <b>@lang('report.user'):</b> {{ $register_details->user_name ?? ''}}<br>
              <b>@lang('business.email'):</b> {{ $register_details->email ?? ''}}<br>
              <b>@lang('business.business_location'):</b> {{ $register_details->location_name ?? ''}}<br>
            </div>
            @if(!empty($register_details->closing_note))
              <div class="col-xs-6">
                <strong>@lang('cash_register.closing_note'):</strong><br>
                {{$register_details->closing_note}}
              </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.cancel' )</button>
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'cash_register.close_register' )</button>
    </div>
    {!! Form::close() !!}
  </div></div>```

After you replace the entire content of the file with this code, save it and do a **hard refresh** of your browser (`Ctrl + Shift + R`). The modal should now look correct and work properly.