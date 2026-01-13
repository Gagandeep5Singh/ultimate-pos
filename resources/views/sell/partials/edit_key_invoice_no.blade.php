<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\SellController::class, 'updateKeyInvoiceNo'], [$transaction->id]), 'method' => 'put', 'id' => 'edit_key_invoice_no_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('lang_v1.update_key_invoice_no') - @lang('sale.invoice_no'): {{$transaction->invoice_no}}</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('with_nif', __('lang_v1.with_nif') . ':*') !!}
                        {!! Form::select('with_nif', [
                            '0' => __('lang_v1.not'),
                            '1' => __('lang_v1.with_nif')
                        ], !empty($transaction->key_invoice_no) ? '1' : '0', [
                            'class' => 'form-control select2',
                            'id' => 'edit_with_nif',
                            'style' => 'width:100%',
                            'required' => true,
                        ]) !!}
                        <span class="help-block text-danger" id="edit_with_nif_error" style="display: none;"></span>
                    </div>
                </div>
                <div class="col-md-12" id="edit_key_invoice_no_wrapper" style="display: {{!empty($transaction->key_invoice_no) ? 'block' : 'none'}};">
                    <div class="form-group">
                        {!! Form::label('key_invoice_no', __('lang_v1.key_invoice_no') . ':') !!}
                        {!! Form::text('key_invoice_no', $transaction->key_invoice_no, [
                            'class' => 'form-control',
                            'id' => 'edit_key_invoice_no',
                            'placeholder' => __('lang_v1.key_invoice_no'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Handle NIF dropdown change
        $('#edit_with_nif').on('change', function() {
            var withNif = $(this).val();
            var $errorBlock = $('#edit_with_nif_error');
            var $wrapper = $('#edit_key_invoice_no_wrapper');
            var $input = $('#edit_key_invoice_no');
            
            // Clear previous errors
            $errorBlock.hide().text('');
            $(this).closest('.form-group').removeClass('has-error');
            
            if (withNif == '1') {
                // With NIF selected - show field and make it required
                $wrapper.show();
                $input.attr('required', true).focus();
            } else if (withNif == '0') {
                // Not selected - hide field and clear value
                $wrapper.hide();
                $input.removeAttr('required').val('');
            }
        });

        // Form submission
        $('#edit_key_invoice_no_form').on('submit', function(e) {
            e.preventDefault();
            
            var withNif = $('#edit_with_nif').val();
            var keyInvoiceNo = $('#edit_key_invoice_no').val();
            var $errorBlock = $('#edit_with_nif_error');
            var $withNifGroup = $('#edit_with_nif').closest('.form-group');
            
            // Clear previous errors
            $errorBlock.hide().text('');
            $withNifGroup.removeClass('has-error');
            
            // Validate
            if (!withNif || withNif == '') {
                var errorMsg = '{{ __("lang_v1.please_select_with_nif_first") }}';
                $errorBlock.text(errorMsg).show();
                $withNifGroup.addClass('has-error');
                $('#edit_with_nif').focus();
                toastr.error(errorMsg);
                return false;
            }
            
            if (withNif == '1' && (!keyInvoiceNo || keyInvoiceNo.trim() == '')) {
                toastr.error('{{ __("lang_v1.key_invoice_no_required") }}');
                $('#edit_key_invoice_no').focus();
                return false;
            }
            
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function(result) {
                    if (result.success == 1) {
                        $('#edit_key_invoice_no_form').closest('.modal').modal('hide');
                        toastr.success(result.msg);
                        // Reload the DataTable
                        if (typeof sell_table !== 'undefined') {
                            sell_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr) {
                    var errorMsg = '{{ __("messages.something_went_wrong") }}';
                    if (xhr.responseJSON && xhr.responseJSON.msg) {
                        errorMsg = xhr.responseJSON.msg;
                    }
                    toastr.error(errorMsg);
                }
            });
        });
    });
</script>

