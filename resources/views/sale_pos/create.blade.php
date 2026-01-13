@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        @if (session('status'))
            @php $status = session('status'); @endphp
            <div class="alert alert-{{ $status['success'] == 1 ? 'success' : 'danger' }}">
                {{ $status['msg'] ?? '' }}
            </div>
        @endif
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif

    {{-- Copy Product Info Script --}}
    <script type="text/javascript">
        // Repair module translations
        @if(isset($sub_type) && $sub_type === 'repair')
            var repair_shipping_must_be_delivered = '{{ __("repair::lang.shipping_must_be_delivered_to_create_invoice") }}';
        @endif
        
        $(document).on('click', '.copy-product-info', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productName = $button.data('product-name');
            var productSku = $button.data('product-sku');
            var rowIndex = $button.data('row-index');
            
            // Get IMEI if selected
            var lotNumber = '';
            var $lotSelect = $button.closest('tr').find('select.lot_number');
            if ($lotSelect.length > 0) {
                var selectedLotText = $lotSelect.find('option:selected').text();
                if (selectedLotText && selectedLotText !== $lotSelect.find('option:first').text()) {
                    // Extract IMEI from the option text (format: "IMEI123 - Exp Date: ...")
                    lotNumber = selectedLotText.split(' - ')[0].trim();
                }
            }
            
            // Get serial number if available (check for serial number input/display in the row)
            var serialNumber = '';
            var $serialNoCell = $button.closest('tr').find('td.serial_no');
            if ($serialNoCell.length > 0) {
                var serialText = $serialNoCell.text().trim();
                if (serialText) {
                    serialNumber = serialText;
                }
            }
            
            // Build copy text
            var copyText = productName;
            if (productSku) {
                copyText += ' | SKU: ' + productSku;
            }
            if (lotNumber) {
                copyText += ' | IMEI: ' + lotNumber;
            }
            if (serialNumber) {
                copyText += ' | Serial: ' + serialNumber;
            }
            
            // Copy to clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(copyText).then(function() {
                    toastr.success('Copied: ' + copyText);
                    // Visual feedback
                    $button.find('i').removeClass('fa-copy').addClass('fa-check');
                    setTimeout(function() {
                        $button.find('i').removeClass('fa-check').addClass('fa-copy');
                    }, 2000);
                }).catch(function(err) {
                    // Fallback for older browsers
                    fallbackCopyTextToClipboard(copyText, $button);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(copyText, $button);
            }
        });
        
        // Fallback copy function for older browsers
        function fallbackCopyTextToClipboard(text, $button) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    toastr.success('Copied: ' + text);
                    $button.find('i').removeClass('fa-copy').addClass('fa-check');
                    setTimeout(function() {
                        $button.find('i').removeClass('fa-check').addClass('fa-copy');
                    }, 2000);
                } else {
                    toastr.error('Failed to copy');
                }
            } catch (err) {
                toastr.error('Failed to copy: ' + err);
            }
            
            document.body.removeChild(textArea);
        }

        // Handle NIF (Key Invoice Number) functionality
        $(document).on('change', '#with_nif', function() {
            var withNif = $(this).val();
            var $errorBlock = $('#with_nif_error');
            
            // Clear previous errors
            $errorBlock.hide().text('');
            $(this).closest('.form-group').removeClass('has-error');
            
            if (withNif == '') {
                // No selection - show error
                $errorBlock.text('{{ __("lang_v1.please_select_with_nif_first") }}').show();
                $(this).closest('.form-group').addClass('has-error');
                $('#key_invoice_no_wrapper').hide();
                $('#key_invoice_no').removeAttr('required').val('');
            } else if (withNif == '1') {
                // With NIF selected - show field
                $errorBlock.hide();
                $(this).closest('.form-group').removeClass('has-error');
                $('#key_invoice_no_wrapper').show();
                $('#key_invoice_no').attr('required', true).focus();
            } else if (withNif == '0') {
                // Not selected - hide field, no error (allowed)
                $errorBlock.hide();
                $(this).closest('.form-group').removeClass('has-error');
                $('#key_invoice_no_wrapper').hide();
                $('#key_invoice_no').removeAttr('required').val('');
            }
        });

        // Validate NIF selection before form submission
        function validateNIFSelection() {
            var withNif = $('#with_nif').val();
            var $errorBlock = $('#with_nif_error');
            var $withNifGroup = $('#with_nif').closest('.form-group');
            
            // Clear previous errors
            $errorBlock.hide().text('');
            $withNifGroup.removeClass('has-error');
            
            // Check if nothing is selected
            if (withNif == '') {
                var errorMsg = '{{ __("lang_v1.please_select_with_nif_first") }}';
                $errorBlock.text(errorMsg).show();
                $withNifGroup.addClass('has-error');
                $('#with_nif').focus();
                toastr.error(errorMsg);
                return false;
            }
            
            // Validate key invoice number only if "With NIF" is selected
            // Users can select "Not" (0) for both single and multiple payments
            if (withNif == '1') {
                // "With NIF" selected - validate key invoice number is required
                var keyInvoiceNo = $('#key_invoice_no').val();
                if (!keyInvoiceNo || keyInvoiceNo.trim() == '') {
                    toastr.error('{{ __("lang_v1.key_invoice_no_required") }}');
                    $('#key_invoice_no').focus();
                    return false;
                }
            }
            // If "Not" (0) is selected (single or multiple payments), it's allowed - no validation needed
            
            return true;
        }

        // Trigger validation before form submission
        $(document).on('click', '#pos-save', function(e) {
            if (!validateNIFSelection()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Also validate when modal is shown
        $(document).on('shown.bs.modal', '#modal_payment', function() {
            // Reset validation state when modal opens
            $('#with_nif_error').hide().text('');
            $('#with_nif').closest('.form-group').removeClass('has-error');
            
            // If no value selected, trigger change to show error
            if ($('#with_nif').val() == '') {
                $('#with_nif').trigger('change');
            } else {
                // If value is selected, just trigger change to update UI
                $('#with_nif').trigger('change');
            }
        });

        // Initialize on page load and when modal is shown
        $(document).ready(function() {
            // Initialize on page load
            if ($('#with_nif').length) {
                $('#with_nif').trigger('change');
            }
        });

        // Also initialize when payment modal is shown (for edit mode)
        $(document).on('shown.bs.modal', '#modal_payment', function() {
            // Initialize the NIF field visibility based on current selection
            var withNif = $('#with_nif').val();
            if (withNif == '1') {
                $('#key_invoice_no_wrapper').show();
                $('#key_invoice_no').attr('required', true);
            } else if (withNif == '0') {
                $('#key_invoice_no_wrapper').hide();
                $('#key_invoice_no').removeAttr('required');
            } else {
                // Empty - show error if needed
                $('#with_nif').trigger('change');
            }
        });
    </script>
@endsection
