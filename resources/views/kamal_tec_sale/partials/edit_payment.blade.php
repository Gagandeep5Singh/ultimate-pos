<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\KamalTecPaymentController::class, 'updatePayment'], [$payment->id]), 'method' => 'put', 'id' => 'edit_payment_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('purchase.edit_payment')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                @if(!empty($payment->sale->contact))
                    <div class="col-md-3">
                        <div class="well">
                            <strong>@lang('contact.contact'):</strong><br>
                            {{ $payment->sale->contact->name }}<br>
                            @if(!empty($payment->sale->contact->mobile))
                                <small>{{ $payment->sale->contact->mobile }}</small>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="well">
                        <strong>@lang('sale.total_amount'):</strong><br>
                        <span class="display_currency" data-currency_symbol="true">{{ $payment->sale->total_amount }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="well">
                        <strong>{{ __('Commission Amount') }}:</strong><br>
                        <span class="display_currency" data-currency_symbol="true">{{ $payment->sale->commission_amount }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="well">
                        <strong>@lang('purchase.payment_due'):</strong><br>
                        <span class="display_currency" data-currency_symbol="true">{{ $payment->sale->due_amount }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <div class="row payment_row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('method', __('purchase.payment_method') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::select('method', ['cash' => __('lang_v1.cash'), 'card' => __('lang_v1.card'), 'bank_transfer' => __('lang_v1.bank_transfer'), 'cheque' => __('lang_v1.cheque')], $payment->method, ['class' => 'form-control select2 payment_types_dropdown', 'required', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('paid_on', __('lang_v1.paid_on') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('paid_on', @format_date($payment->paid_on), ['class' => 'form-control', 'readonly', 'required', 'id' => 'paid_on']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('amount', __('sale.amount') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::text('amount', @num_format($payment->amount), ['class' => 'form-control input_number payment_amount', 'required', 'id' => 'payment_amount', 'placeholder' => __('lang_v1.enter_amount')]); !!}
                        </div>
                        <small class="help-block text-muted">@lang('lang_v1.max_payment_allowed'): <span class="display_currency" data-currency_symbol="true">{{ $payment->sale->commission_amount }}</span> ({{ __('Commission Amount') }})</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('lang_v1.payment_note') . ':') !!}
                        {!! Form::textarea('note', $payment->note, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.payment_note')]); !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.update')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var modal = $('.edit_payment_modal');
        
        // Initialize when modal is shown
        modal.on('shown.bs.modal', function() {
            // Initialize datepicker
            if ($('#paid_on').length) {
                $('#paid_on').datepicker({
                    autoclose: true,
                    format: datepicker_date_format
                });
            }

            // Initialize select2
            if ($('.select2').length) {
                $('.select2').each(function() {
                    var $p = $(this).closest('.modal');
                    $(this).select2({ dropdownParent: $p });
                });
            }

            // Set max amount validation (cannot exceed commission amount)
            var maxAmount = parseFloat('{{ $payment->sale->commission_amount }}');
            if ($('#payment_amount').length) {
                $('#payment_amount').off('input').on('input', function() {
                    var amount = parseFloat($(this).val()) || 0;
                    if (amount > maxAmount) {
                        $(this).val(maxAmount);
                        toastr.warning('@lang("lang_v1.max_payment_allowed"): ' + maxAmount.toFixed(2));
                    }
                });
            }
        });
    });
</script>
