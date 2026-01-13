@extends('layouts.app')
@section('title', 'Kamal Tec Phone Sale Details')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Kamal Tec Phone Sale Details</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Sale Information</h3>
                    <div class="box-tools pull-right">
                        @if($sale->customer)
                        <button type="button" class="btn btn-info btn-sm" id="copy_customer_info_btn" style="margin-right: 5px;">
                            <i class="fa fa-copy"></i> Copy Info
                        </button>
                        @endif
                        <a href="{{ action([\App\Http\Controllers\KamalTecSaleController::class, 'edit'], [$sale->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Invoice No:</th>
                                    <td>{{ $sale->invoice_no }}</td>
                                </tr>
                                @if(!empty($sale->kt_invoice_no))
                                <tr>
                                    <th>KT Invoice Number:</th>
                                    <td>{{ $sale->kt_invoice_no }}</td>
                                </tr>
                                @endif
                                @if(!empty($sale->floa_ref))
                                <tr>
                                    <th>Floa Ref:</th>
                                    <td>{{ $sale->floa_ref }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ @format_date($sale->sale_date) }}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>
                                        @if($sale->customer)
                                            <a href="{{ route('kamal-tec-customers.show', $sale->customer->id) }}">
                                                {{ $sale->customer->first_name }} {{ $sale->customer->last_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">No customer assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $sale->location ? $sale->location->name : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($sale->status == 'open')
                                            <span class="label label-warning">Open</span>
                                        @elseif($sale->status == 'closed')
                                            <span class="label label-success">Closed</span>
                                        @else
                                            <span class="label label-danger">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Total Amount:</th>
                                    <td><span class="display_currency">{{ $sale->total_amount }}</span></td>
                                </tr>
                                <tr>
                                    <th>Paid Amount:</th>
                                    <td><span class="display_currency">{{ $sale->paid_amount }}</span></td>
                                </tr>
                                <tr>
                                    <th>Due Amount:</th>
                                    <td><span class="display_currency">{{ $sale->due_amount }}</span></td>
                                </tr>
                                <tr>
                                    <th>Commission Type:</th>
                                    <td>{{ ucfirst($sale->commission_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Commission Amount:</th>
                                    <td><span class="display_currency">{{ $sale->commission_amount }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($sale->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Notes:</strong>
                            <p>{{ $sale->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Products</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>IMEI/Serial</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleLines as $line)
                            <tr>
                                <td>{{ $line->product_name_snapshot }}</td>
                                <td>{{ $line->sku_snapshot }}</td>
                                <td>{{ $line->qty }}</td>
                                <td><span class="display_currency">{{ $line->unit_price }}</span></td>
                                <td>{{ $line->imei_serial ?? '-' }}</td>
                                <td><span class="display_currency">{{ $line->line_total }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total:</th>
                                <th><span class="display_currency">{{ $sale->total_amount }}</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Payments</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary add_payment_btn" data-href="{{ action([\App\Http\Controllers\KamalTecPaymentController::class, 'addPayment'], [$sale->id]) }}">
                            <i class="fa fa-plus" aria-hidden="true"></i> @lang('purchase.add_payment')
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sale->payments as $payment)
                            <tr>
                                <td>{{ @format_date($payment->paid_on) }}</td>
                                <td><span class="display_currency">{{ $payment->amount }}</span></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                <td>{{ $payment->note ?? '-' }}</td>
                                <td>
                                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info edit_payment_btn" data-href="{{ action([\App\Http\Controllers\KamalTecPaymentController::class, 'editPayment'], [$payment->id]) }}" title="@lang('messages.edit')">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form action="{{ action([\App\Http\Controllers\KamalTecPaymentController::class, 'destroy'], [$payment->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-danger" onclick="return confirm('@lang('messages.are_you_sure')')" title="@lang('messages.delete')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No payments recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="1" class="text-right">Total Paid:</th>
                                <th><span class="display_currency">{{ $sale->paid_amount }}</span></th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Payment Modal -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.add_payment_btn', function(e) {
            e.preventDefault();
            var url = $(this).data('href');
            var container = $('.payment_modal');
            
            $.ajax({
                url: url,
                dataType: 'html',
                success: function(result) {
                    container.html(result).modal('show');
                    
                    // Convert currency display
                    if (typeof __currency_convert_recursively === 'function') {
                        __currency_convert_recursively(container);
                    }
                    
                    // Initialize select2
                    container.find('.select2').each(function() {
                        var $p = $(this).closest('.modal');
                        $(this).select2({ dropdownParent: $p });
                    });
                    
                    // Initialize datepicker
                    container.find('#paid_on').datepicker({
                        autoclose: true,
                        format: datepicker_date_format
                    });
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load payment form');
                }
            });
        });

        $(document).on('click', '.edit_payment_btn', function(e) {
            e.preventDefault();
            var url = $(this).data('href');
            var container = $('.edit_payment_modal');
            
            $.ajax({
                url: url,
                dataType: 'html',
                success: function(result) {
                    container.html(result).modal('show');
                    
                    // Convert currency display
                    if (typeof __currency_convert_recursively === 'function') {
                        __currency_convert_recursively(container);
                    }
                    
                    // Initialize select2
                    container.find('.select2').each(function() {
                        var $p = $(this).closest('.modal');
                        $(this).select2({ dropdownParent: $p });
                    });
                    
                    // Initialize datepicker
                    container.find('#paid_on').datepicker({
                        autoclose: true,
                        format: datepicker_date_format
                    });
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load payment form');
                }
            });
        });

        // Copy all sale data button functionality
        $('#copy_customer_info_btn').on('click', function() {
            @if($sale)
            // Format customer and sale information
            var customer = @json($sale->customer);
            var sale = @json($sale);
            var saleLines = @json($sale->saleLines);
            var payments = @json($sale->payments);
            var location = @json($sale->location);
            
            // Format date: 01-06-1979 - 01 june
            var dobFormatted = '';
            if (customer && customer.dob) {
                var dobDate = new Date(customer.dob);
                var day = String(dobDate.getDate()).padStart(2, '0');
                var month = String(dobDate.getMonth() + 1).padStart(2, '0');
                var year = dobDate.getFullYear();
                var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                 'July', 'August', 'September', 'October', 'November', 'December'];
                var monthName = monthNames[dobDate.getMonth()];
                dobFormatted = day + '-' + month + '-' + year + ' - ' + day + ' ' + monthName.toLowerCase();
            }
            
            // Format sale date
            var saleDateFormatted = '';
            if (sale.sale_date) {
                var saleDate = new Date(sale.sale_date);
                var day = String(saleDate.getDate()).padStart(2, '0');
                var month = String(saleDate.getMonth() + 1).padStart(2, '0');
                var year = saleDate.getFullYear();
                saleDateFormatted = day + '-' + month + '-' + year;
            }
            
            // Build message in exact format requested
            var message = '';
            
            // Always show all fields in exact format
            if (customer) {
                message += 'Name --' + (customer.first_name || '') + ' ' + (customer.last_name || '') + '\n';
                message += 'Dob -' + (dobFormatted || '') + '\n';
                message += 'NIF - ' + (customer.nif || '') + '\n';
                message += 'Number --' + (customer.number || '') + '\n';
                message += 'Email -' + (customer.email || '') + '\n';
                
                var countryText = customer.dob_country || '';
                if (customer.address) {
                    var addressParts = customer.address.split(',');
                    if (addressParts.length > 1) {
                        countryText += (countryText ? ' - ' : '') + addressParts[addressParts.length - 2].trim();
                    }
                }
                message += 'Country --' + countryText + '\n';
                
                message += 'Address --' + (customer.address || '') + '\n';
            } else {
                message += 'Name --\n';
                message += 'Dob -\n';
                message += 'NIF - \n';
                message += 'Number --\n';
                message += 'Email -\n';
                message += 'Country --\n';
                message += 'Address --\n';
            }
            
            // Add all products
            if (saleLines && saleLines.length > 0) {
                saleLines.forEach(function(line, index) {
                    if (index > 0) {
                        message += '\n';
                    }
                    message += 'Product -- ' + (line.product_name_snapshot || '') + '\n';
                    message += 'Price - ' + parseFloat(line.line_total || line.unit_price || 0).toFixed(2).replace('.', ',');
                });
            } else {
                message += 'Product -- \n';
                message += 'Price - ';
            }
            
            // Add installment if found
            var installment = '';
            if (sale.floa_ref) {
                var match = sale.floa_ref.match(/(\d+)\s*x/i) || sale.floa_ref.match(/x\s*(\d+)/i);
                if (match) {
                    installment = match[1] + 'x';
                    message += '\nInstalmrnt - ' + installment;
                }
            }
            
            // Copy to clipboard
            var tempTextarea = document.createElement('textarea');
            tempTextarea.value = message;
            tempTextarea.style.position = 'fixed';
            tempTextarea.style.left = '-999999px';
            document.body.appendChild(tempTextarea);
            tempTextarea.select();
            
            try {
                document.execCommand('copy');
                toastr.success('All sale data copied to clipboard!');
            } catch (err) {
                // Fallback for modern browsers
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(message).then(function() {
                        toastr.success('All sale data copied to clipboard!');
                    }).catch(function() {
                        toastr.error('Failed to copy. Please select and copy manually.');
                        // Show message in prompt as fallback
                        prompt('Copy this information:', message);
                    });
                } else {
                    toastr.error('Failed to copy. Please select and copy manually.');
                    // Show message in prompt as fallback
                    prompt('Copy this information:', message);
                }
            }
            
            document.body.removeChild(tempTextarea);
            @else
            toastr.error('Sale information not available');
            @endif
        });
    });
</script>
@endsection
