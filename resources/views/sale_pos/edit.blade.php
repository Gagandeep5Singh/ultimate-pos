@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'update'], [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form' ]) !!}
	{{ method_field('PUT') }}
	<div class="row mb-12">
		<div class="col-md-12 tw-pt-0 tw-mb-14">
			<div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
				<div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">
					<div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">
						<div class="box-body pb-0">
							{!! Form::hidden('location_id', $transaction->location_id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($location_printer_type) ? $location_printer_type : 'browser', 'data-default_payment_accounts' => $transaction->location->default_payment_accounts]); !!}
							<!-- sub_type -->
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
								@include('sale_pos.partials.pos_form_edit')

								@include('sale_pos.partials.pos_form_totals', ['edit' => true])

								@include('sale_pos.partials.payment_modal')

								@if(empty($pos_settings['disable_suspend']))
									@include('sale_pos.partials.suspend_note_modal')
								@endif

								@if(empty($pos_settings['disable_recurring_invoice']))
									@include('sale_pos.partials.recurring_invoice_modal')
								@endif
							</div>
							@if(!empty($only_payment))
								<div class="overlay"></div>
							@endif
						</div>
					</div>
				@if(empty($pos_settings['hide_product_suggestion'])  && !isMobile() && empty($only_payment))
					<div class="col-md-5 no-padding">
						@include('sale_pos.partials.pos_sidebar')
					</div>
				@endif
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions', ['edit' => true])
	{!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
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

		// Initialize NIF fields on page load and when payment modal is shown
		$(document).ready(function() {
			// Initialize on page load
			if ($('#with_nif').length) {
				var withNif = $('#with_nif').val();
				if (withNif == '1') {
					$('#key_invoice_no_wrapper').show();
					$('#key_invoice_no').attr('required', true);
				} else {
					$('#key_invoice_no_wrapper').hide();
					$('#key_invoice_no').removeAttr('required');
				}
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
			}
		});
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
	</script>
	
@endsection

@section('css')
	<style type="text/css">
		/*CSS to print receipts*/
		.print_section{
		    display: none;
		}
		@media print{
		    .print_section{
		        display: block !important;
		    }
		}
		@page {
		    size: 3.1in auto;/* width height */
		    height: auto !important;
		    margin-top: 0mm;
		    margin-bottom: 0mm;
		}
		.overlay {
			background: rgba(255,255,255,0) !important;
			cursor: not-allowed;
		}
	</style>
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@endsection