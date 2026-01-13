@extends('layouts.app')
@section('title', 'Add Kamal Tec Phone Sale')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Add Kamal Tec Phone Sale</h1>
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
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {!! Form::open(['url' => action([\App\Http\Controllers\KamalTecSaleController::class, 'store']), 'method' => 'post', 'id' => 'add_sale_form']) !!}
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('contact.contact').':*') !!}
                        {!! Form::select('contact_id', $customers, old('contact_id'), ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sale_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('sale_date', old('sale_date', @format_date('now')), ['class' => 'form-control', 'readonly', 'required', 'id' => 'sale_date']); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('kt_invoice_no', 'KT Invoice Number:') !!}
                        {!! Form::text('kt_invoice_no', old('kt_invoice_no'), ['class' => 'form-control', 'placeholder' => 'Enter Kamal Tec invoice number']); !!}
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
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                <td><strong id="grand_total">0.00</strong></td>
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
                        {!! Form::select('commission_type', ['percent' => 'Percent', 'fixed' => 'Fixed'], 'percent', ['class' => 'form-control', 'required', 'id' => 'commission_type']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('commission_value', 'Commission Value:*') !!}
                        {!! Form::text('commission_value', 0, ['class' => 'form-control input_number', 'required', 'id' => 'commission_value']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('commission_amount', 'Commission Amount:') !!}
                        {!! Form::text('commission_amount_display', 0, ['class' => 'form-control', 'readonly', 'id' => 'commission_amount_display']); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('notes', 'Notes:') !!}
                        {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('initial_payment_amount', 'Initial Payment (Optional):') !!}
                        {!! Form::text('initial_payment_amount', 0, ['class' => 'form-control input_number']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('initial_payment_method', 'Payment Method:') !!}
                        {!! Form::select('initial_payment_method', ['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque'], 'cash', ['class' => 'form-control']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('initial_payment_note', 'Payment Note:') !!}
                        {!! Form::text('initial_payment_note', null, ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 text-center">
        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white">@lang('messages.save')</button>
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

        var rowIndex = 0;

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
            rowIndex++;
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
            var qty = parseFormattedNumber(row.find('.qty').val());
            var price = parseFormattedNumber(row.find('.unit_price').val());
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

        // Form validation before submit
        $('#add_sale_form').validate({
            rules: {
                contact_id: {
                    required: true
                },
                sale_date: {
                    required: true
                },
                commission_type: {
                    required: true
                },
                commission_value: {
                    required: true,
                    min: 0
                }
            },
            messages: {
                contact_id: {
                    required: "Please select a customer"
                },
                sale_date: {
                    required: "Please select a sale date"
                },
                commission_type: {
                    required: "Please select commission type"
                },
                commission_value: {
                    required: "Please enter commission value",
                    min: "Commission value must be 0 or greater"
                }
            },
            submitHandler: function(form) {
                // Check if at least one product is added
                var productCount = $('#products_table tbody tr').length;
                if (productCount === 0) {
                    toastr.error('Please add at least one product to the sale');
                    return false;
                }

                // Validate all products have quantity and price
                var isValid = true;
                $('#products_table tbody tr').each(function() {
                    var qty = parseFloat($(this).find('.qty').val()) || 0;
                    var price = parseFloat($(this).find('.unit_price').val()) || 0;
                    
                    if (qty <= 0) {
                        toastr.error('Please enter a valid quantity for all products');
                        isValid = false;
                        return false;
                    }
                    if (price <= 0) {
                        toastr.error('Please enter a valid price for all products');
                        isValid = false;
                        return false;
                    }
                });

                if (!isValid) {
                    return false;
                }

                // Show loading
                $('button[type="submit"]').prop('disabled', true).text('Saving...');
                
                // Submit form
                form.submit();
            }
        });
    });
</script>
@endsection
