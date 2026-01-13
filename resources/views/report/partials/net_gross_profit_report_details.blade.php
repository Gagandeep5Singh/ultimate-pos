<h3 class="text-muted mb-0">
    @lang('lang_v1.cogs') <span class="display_currency" data-currency_symbol="true"> {{ (($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock']) }}</span>
</h3>
<small class="help-block">
    @lang('lang_v1.cogs_help_text')
</small>

<h3 class="text-muted mb-0">
    {{ __('lang_v1.gross_profit') }}: 
    <span class="display_currency" data-currency_symbol="true">{{$data['gross_profit']}}</span>
</h3>
<small class="help-block">
    (@lang('lang_v1.total_sell_price') - @lang('lang_v1.total_purchase_price'))
    @if(!empty($data['gross_profit_label']))
        @foreach ($data['gross_profit_label'] as $val)
            + {{$val}}
        @endforeach
    @endif
</small>

<div class="table-responsive" style="margin-top: 20px;">
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <th style="width: 50%;">{{ __('report.net_profit_before_klarna_fees') }}</th>
                <td style="text-align: right;">
                    <span class="display_currency" data-currency_symbol="true">
                        {{ $data['net_profit_before_klarna'] ?? 0 }}
                    </span>
                </td>
            </tr>
            <tr class="klarna-fees-row" style="background-color: #fff3cd;">
                <th>
                    <i class="fa fa-info-circle" style="color: #856404;"></i>
                    {{ __('report.klarna_fees') }} <small class="text-muted">(4.99% + €0.35/trans)</small>
                </th>
                <td style="text-align: right; color: #d32f2f; font-weight: 600;">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ -($data['total_klarna_fees'] ?? 0) }}">
                        {{ -($data['total_klarna_fees'] ?? 0) }}
                    </span>
                </td>
            </tr>
            <tr class="bg-success" style="background-color: #d4edda !important;">
                <th style="font-size: 16px; font-weight: bold;">
                    <strong>{{ __('report.net_profit_after_klarna_fees') }}</strong>
                </th>
                <td style="text-align: right; font-size: 16px; font-weight: bold;">
                    <strong>
                        <span class="display_currency" data-currency_symbol="true">
                            {{ $data['net_profit'] ?? 0 }}
                        </span>
                    </strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<h3 class="text-muted mb-0" style="margin-top: 20px;">
    {{ __('report.net_profit') }}: 
    <span class="display_currency" data-currency_symbol="true">{{$data['net_profit']}}</span>
</h3>
<small class="help-block">
    @lang('lang_v1.gross_profit') + (@lang('lang_v1.total_sell_shipping_charge') + @lang('lang_v1.sell_additional_expense') + @lang('report.total_stock_recovered') + @lang('lang_v1.total_purchase_discount') + @lang('lang_v1.total_sell_round_off') 
    @foreach($data['right_side_module_data'] as $module_data)
        @if(!empty($module_data['add_to_net_profit']))
            + {{$module_data['label']}} 
        @endif
    @endforeach
    ) <br> - ( @lang('report.total_stock_adjustment') + @lang('report.total_expense') + @lang('lang_v1.total_purchase_shipping_charge') + @lang('lang_v1.total_transfer_shipping_charge') + @lang('lang_v1.purchase_additional_expense') + @lang('lang_v1.total_sell_discount') + @lang('lang_v1.total_reward_amount') 
    @foreach($data['left_side_module_data'] as $module_data)
        @if(!empty($module_data['add_to_net_profit']))
            + {{$module_data['label']}}
        @endif 
    @endforeach )
</small>

<style>
    .klarna-fees-row th {
        color: #856404;
        font-weight: 600;
    }
    .klarna-fees-row:hover {
        background-color: #ffeaa7 !important;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
        padding: 12px;
    }
</style>