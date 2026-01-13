@extends('layouts.app')
@section('title', 'Edit Kamal Tec Phone Sale')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Edit Kamal Tec Phone Sale</h1>
</section>

<!-- Main content -->
<section class="content">
    @if(session('status'))
        <div class="alert alert-{{session('status')['success'] == 1 ? 'success' : 'danger'}} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>{{session('status')['success'] == 1 ? 'Success!' : 'Error!'}}</strong> {{session('status')['msg']}}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {!! Form::open(['url' => action([\App\Http\Controllers\KamalTecSaleController::class, 'update'], [$sale->id]), 'method' => 'PUT', 'id' => 'edit_sale_form']) !!}
    {{ method_field('PUT') }}
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        {!! Form::select('location_id', $business_locations, $sale->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('kamal_tec_customer_id', 'Kamal Tec Customer:*') !!}
                        <div class="input-group">
                            {!! Form::select('kamal_tec_customer_id', $kamal_tec_customers, $sale->customer_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'id' => 'kamal_tec_customer_id', 'required', 'style' => 'width:100%']); !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" id="create_new_customer_btn" title="Create New Customer">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </span>
                        </div>
                        <small class="help-block">Search by name, NIF, or number. Click + to create new customer</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sale_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('sale_date', @format_date($sale->sale_date), ['class' => 'form-control', 'readonly', 'required', 'id' => 'sale_date']); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('kt_invoice_no', 'KT Invoice Number:') !!}
                        {!! Form::text('kt_invoice_no', $sale->kt_invoice_no, ['class' => 'form-control', 'placeholder' => 'Enter Kamal Tec invoice number']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('floa_ref', 'Floa Ref:') !!}
                        {!! Form::text('floa_ref', $sale->floa_ref, ['class' => 'form-control', 'placeholder' => 'Enter installment reference']); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <h4>Products</h4>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            {!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => 'Search product by name or SKU...']); !!}
                        </div>
                    </div>
                    <table class="table table-bordered" id="products_table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>IMEI/Serial</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleLines as $line)
                            <tr data-product-id="{{ $line->product_id }}">
                                <td>
                                    {{ $line->product_name_snapshot }}
                                    <input type="hidden" name="products[]" value="{{ $line->product_id }}">
                                </td>
                                <td class="sku-display">{{ $line->sku_snapshot }}</td>
                                <td>
                                    {!! Form::text('quantities[]', @num_format($line->qty), ['class' => 'form-control input_number qty', 'placeholder' => 'Qty']); !!}
                                </td>
                                <td>
                                    {!! Form::text('unit_prices[]', @num_format($line->unit_price), ['class' => 'form-control input_number unit_price', 'placeholder' => 'Price']); !!}
                                </td>
                                <td>
                                    {!! Form::text('imei_serials[]', $line->imei_serial, ['class' => 'form-control', 'placeholder' => 'IMEI/Serial']); !!}
                                </td>
                                <td class="line_total">{{ number_format($line->line_total, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                <td><strong id="grand_total">{{ number_format($sale->total_amount, 2) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('commission_type', 'Commission Type:*') !!}
                        {!! Form::select('commission_type', ['percent' => 'Percent', 'fixed' => 'Fixed'], $sale->commission_type, ['class' => 'form-control', 'required', 'id' => 'commission_type']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('commission_value', 'Commission Value:*') !!}
                        {!! Form::text('commission_value', @num_format($sale->commission_value), ['class' => 'form-control input_number', 'required', 'id' => 'commission_value']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('commission_amount', 'Commission Amount:') !!}
                        {!! Form::text('commission_amount_display', @num_format($sale->commission_amount), ['class' => 'form-control', 'readonly', 'id' => 'commission_amount_display']); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('notes', 'Notes:') !!}
                        {!! Form::textarea('notes', $sale->notes, ['class' => 'form-control', 'rows' => 3]); !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 text-center">
        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.update')</button>
    </div>
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#sale_date').datepicker({
            autoclose: true,
            format: datepicker_date_format
        });

        // Initialize Select2 with AJAX search for customers
        $('#kamal_tec_customer_id').select2({
            placeholder: 'Search by name, NIF, or number...',
            allowClear: true,
            ajax: {
                url: '{{ action([\App\Http\Controllers\KamalTecCustomerController::class, 'getCustomers']) }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data,
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        // Pre-select current customer if exists
        @if($sale->customer_id)
            var customerId = {{ $sale->customer_id }};
            // Fetch customer details and set in select2
            $.ajax({
                url: '{{ action([\App\Http\Controllers\KamalTecCustomerController::class, 'getCustomers']) }}',
                data: { search: '' },
                dataType: 'json',
                success: function(data) {
                    var customer = data.find(function(c) { return c.id == customerId; });
                    if (customer) {
                        var newOption = new Option(customer.text, customer.id, true, true);
                        $('#kamal_tec_customer_id').append(newOption).trigger('change');
                    }
                }
            });
        @endif

        // Create new customer button click
        $('#create_new_customer_btn').on('click', function() {
            window.location.href = '{{ action([\App\Http\Controllers\KamalTecCustomerController::class, 'create']) }}?return_to=sale_edit&sale_id={{ $sale->id }}';
        });

        // Product autocomplete search
        $('#search_product').autocomplete({
            delay: 1000,
            source: function(request, response) {
                $.getJSON(
                    '/products/list',
                    {
                        term: request.term,
                        not_for_selling: 0,
                        search_fields: ['name', 'sku']
                    },
                    function(data) {
                        // Data is already JSON, no need to parse
                        response(data);
                    }
                );
            },
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                addProductRow(ui.item);
                $(this).val('');
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            var label = item.variation ? item.name + ' - ' + item.variation + ' (' + item.sub_sku + ')' : (item.text || item.name + ' - ' + item.sku);
            return $("<li>")
                .append("<div>" + label + "</div>")
                .appendTo(ul);
        };

        function addProductRow(product) {
            var productName = product.name || '';
            var sku = product.sub_sku || product.sku || '-';
            var productId = product.product_id || product.id;
            
            var row = '<tr data-product-id="' + productId + '">' +
                '<td>' + productName + '<input type="hidden" name="products[]" value="' + productId + '"></td>' +
                '<td class="sku-display">' + sku + '</td>' +
                '<td><input type="text" name="quantities[]" class="form-control input_number qty" value="1"></td>' +
                '<td><input type="text" name="unit_prices[]" class="form-control input_number unit_price" value="0"></td>' +
                '<td><input type="text" name="imei_serials[]" class="form-control" placeholder="IMEI/Serial"></td>' +
                '<td class="line_total">0.00</td>' +
                '<td><button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            $('#products_table tbody').append(row);
        }

        // Remove row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });

        // Helper function to parse formatted numbers using system's number unformat function
        function parseFormattedNumber(value) {
            if (!value) return 0;
            
            // Use system's __number_uf function if available
            if (typeof __number_uf !== 'undefined') {
                return __number_uf(value.toString(), false);
            }
            
            // Fallback: manual parsing
            var str = value.toString().trim();
            var decimalSep = typeof __currency_decimal_separator !== 'undefined' ? __currency_decimal_separator : '.';
            var thousandSep = typeof __currency_thousand_separator !== 'undefined' ? __currency_thousand_separator : ',';
            
            if (thousandSep) {
                str = str.replace(new RegExp('\\' + thousandSep, 'g'), '');
            }
            
            if (decimalSep && decimalSep !== '.') {
                str = str.replace(decimalSep, '.');
            }
            
            return parseFloat(str) || 0;
        }

        // Calculate line total
        $(document).on('input', '.qty, .unit_price', function() {
            var row = $(this).closest('tr');
            var qty_val = row.find('.qty').val();
            var price_val = row.find('.unit_price').val();
            
            // Parse formatted numbers properly
            var qty = parseFormattedNumber(qty_val);
            var price = parseFormattedNumber(price_val);
            
            var total = qty * price;
            row.find('.line_total').text(total.toFixed(2));
            calculateTotals();
        });

        // Calculate commission
        $(document).on('input change', '#commission_type, #commission_value', function() {
            calculateCommission();
        });

        function calculateTotals() {
            var grandTotal = 0;
            $('#products_table tbody tr').each(function() {
                var totalText = $(this).find('.line_total').text();
                var total = parseFormattedNumber(totalText);
                grandTotal += total;
            });
            $('#grand_total').text(grandTotal.toFixed(2));
            calculateCommission();
        }

        function calculateCommission() {
            var grandTotalText = $('#grand_total').text();
            var grandTotal = parseFormattedNumber(grandTotalText);
            var commissionType = $('#commission_type').val();
            var commissionValueText = $('#commission_value').val();
            var commissionValue = parseFormattedNumber(commissionValueText);
            var commissionAmount = 0;

            if (commissionType == 'percent') {
                commissionAmount = (grandTotal * commissionValue) / 100;
            } else {
                commissionAmount = commissionValue;
            }

            // Format the commission amount for display
            if (typeof __currency_decimal_separator !== 'undefined' && __currency_decimal_separator === ',') {
                $('#commission_amount_display').val(commissionAmount.toFixed(2).replace('.', ','));
            } else {
                $('#commission_amount_display').val(commissionAmount.toFixed(2));
            }
        }

        // Initialize totals
        calculateTotals();

        // Form submission handler
        $('#edit_sale_form').on('submit', function(e) {
            console.log('Form submit triggered');
            console.log('KT Invoice No value:', $('#kt_invoice_no').val());
            
            // Validate that at least one product exists
            if ($('#products_table tbody tr').length === 0) {
                e.preventDefault();
                toastr.error('Please add at least one product');
                return false;
            }

            // Validate all required fields
            var hasErrors = false;
            if (!$('#kamal_tec_customer_id').val()) {
                toastr.error('Please select a Kamal Tec customer');
                hasErrors = true;
            }
            if (!$('#sale_date').val()) {
                toastr.error('Please select a sale date');
                hasErrors = true;
            }
            if (!$('#commission_type').val()) {
                toastr.error('Please select a commission type');
                hasErrors = true;
            }
            if (!$('#commission_value').val() || parseFormattedNumber($('#commission_value').val()) <= 0) {
                toastr.error('Please enter a valid commission value');
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
                return false;
            }

            // Show loading indicator
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
            
            console.log('Form validation passed, submitting...');
            console.log('Form action:', $('#edit_sale_form').attr('action'));
            console.log('Form method:', $('#edit_sale_form').attr('method'));
            
            // Set a timeout to re-enable button if form doesn't submit (30 seconds)
            var timeoutId = setTimeout(function() {
                console.error('Form submission timeout - re-enabling button');
                submitBtn.prop('disabled', false).html(originalText);
                toastr.error('Request timed out. Please try again.');
            }, 30000);
            
            // Clear timeout when page unloads (successful redirect)
            $(window).on('beforeunload', function() {
                clearTimeout(timeoutId);
            });
            
            // Allow form to submit normally (not AJAX)
            // The form will redirect on success or show errors on failure
            return true;
        });
    });
</script>
@endsection
