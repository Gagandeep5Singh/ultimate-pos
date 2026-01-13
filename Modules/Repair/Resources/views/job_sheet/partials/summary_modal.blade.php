<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="modalTitle">
                {{ __('repair::lang.job_sheet_summary') }} 
                (<b>{{ __('repair::lang.job_sheet_no') }}:</b> {{ $job->job_sheet_no }})
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-12">
                    <p class="pull-right">
                        <b>{{ __('messages.date') }}:</b> {{ @format_datetime($job->created_at) }}
                    </p>
                </div>
            </div>
            
            <div class="row">
                <!-- Left Column: Job Sheet Info -->
                <div class="col-sm-4">
                    <b>{{ __('repair::lang.job_sheet_no') }}:</b> #{{ $job->job_sheet_no }}<br>
                    <b>{{ __('repair::lang.status') }}:</b> 
                    @if(!empty($job->status))
                        <span class="label" style="background-color: {{ $job->status->color ?? '#337ab7' }}; color: white; padding: 3px 8px; border-radius: 3px;">
                            {{ $job->status->name }}
                        </span>
                    @else
                        --
                    @endif
                    <br>
                    <b>{{ __('repair::lang.service_type') }}:</b> 
                    {{ __('repair::lang.' . $job->service_type) }}<br>
                    <b>{{ __('lang_v1.due_date') }}:</b> 
                    @if(!empty($job->delivery_date))
                        {{ @format_datetime($job->delivery_date) }}
                    @else
                        --
                    @endif
                    <br>
                    @if(!empty($job->estimated_cost))
                        <b>{{ __('repair::lang.estimated_cost') }}:</b> 
                        <span class="display_currency" data-currency_symbol="true">{{ $job->estimated_cost }}</span><br>
                    @endif
                    @if(!empty($job->security_pwd))
                        <b>{{ __('lang_v1.password') }}:</b> 
                        <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">{{ $job->security_pwd }}</code><br>
                    @endif
                    @if(!empty($job->comment_by_ss))
                        <br>
                        <b>{{ __('repair::lang.note') }}:</b><br>
                        <p class="well well-sm no-shadow bg-gray" style="margin-top: 5px; margin-bottom: 0;">
                            {{ $job->comment_by_ss }}
                        </p>
                    @endif
                </div>

                <!-- Middle Column: Customer Info -->
                <div class="col-sm-4">
                    @if(!empty($job->customer))
                        <b>{{ __('sale.customer_name') }}:</b> {{ $job->customer->name }}<br>
                        @if(!empty($job->customer->mobile))
                            <b>{{ __('contact.mobile') }}:</b> {{ $job->customer->mobile }}<br>
                        @endif
                        @if(!empty($job->customer->alternate_number))
                            <b>{{ __('contact.alternate_contact_number') }}:</b> {{ $job->customer->alternate_number }}<br>
                        @endif
                        @if(!empty($job->customer->landline))
                            <b>{{ __('contact.landline') }}:</b> {{ $job->customer->landline }}<br>
                        @endif
                        @if(!empty($job->customer->email))
                            <b>{{ __('business.email') }}:</b> {{ $job->customer->email }}<br>
                        @endif
                        @if(!empty($job->customer->contact_address))
                            <b>{{ __('business.address') }}:</b><br>
                            {!! $job->customer->contact_address !!}
                        @endif
                    @endif
                </div>

                <!-- Right Column: Device & Location Info -->
                <div class="col-sm-4">
                    @if(!empty($job->Device))
                        <b>{{ __('repair::lang.device') }}:</b> {{ $job->Device->name }}<br>
                    @endif
                    @if(!empty($job->Brand))
                        <b>{{ __('repair::lang.brand') }}:</b> {{ $job->Brand->name }}<br>
                    @endif
                    @if(!empty($job->deviceModel))
                        <b>{{ __('repair::lang.model') }}:</b> {{ $job->deviceModel->name }}<br>
                    @endif
                    @if(!empty($job->serial_no))
                        <b>{{ __('repair::lang.serial_no') }}:</b> {{ $job->serial_no }}<br>
                    @endif
                    @if(!empty($job->businessLocation))
                        <br>
                        <b>{{ __('business.location') }}:</b> {{ $job->businessLocation->name }}<br>
                        @if(!empty($job->businessLocation->location_address))
                            {!! $job->businessLocation->location_address !!}
                        @endif
                    @endif
                    @if(!empty($job->technician))
                        <br>
                        <b>{{ __('repair::lang.technician') }}:</b> {{ $job->technician->user_full_name }}<br>
                    @endif
                    @if(!empty($job->createdBy))
                        <b>{{ __('lang_v1.created_by') }}:</b> {{ $job->createdBy->user_full_name }}<br>
                    @endif
                </div>
            </div>

            <br>

            <!-- Invoices Section -->
            @if($job->invoices && $job->invoices->count() > 0)
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <h4>{{ __('repair::lang.invoices') }}:</h4>
                    </div>
                    <div class="col-sm-12 col-xs-12">
                        <div class="table-responsive">
                            <table class="table bg-gray">
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th>{{ __('sale.invoice_no') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('sale.total') }}</th>
                                    <th>{{ __('sale.payment_status') }}</th>
                                </tr>
                                @foreach($job->invoices as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'show'], [$invoice->id]) }}" target="_blank">
                                                {{ $invoice->invoice_no }}
                                            </a>
                                        </td>
                                        <td>{{ @format_date($invoice->transaction_date) }}</td>
                                        <td>
                                            <span class="display_currency" data-currency_symbol="true">{{ $invoice->final_total }}</span>
                                        </td>
                                        <td>
                                            <span class="label @payment_status($invoice->payment_status)">
                                                {{ __('lang_v1.' . $invoice->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
                <br>
            @endif

            <!-- Parts Used Section -->
            @if(!empty($parts) && count($parts) > 0)
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <h4>{{ __('repair::lang.parts_used') }}:</h4>
                    </div>
                    <div class="col-sm-12 col-xs-12">
                        <div class="table-responsive">
                            <table class="table bg-gray">
                                <tr class="bg-green">
                                    <th>#</th>
                                    <th>{{ __('product.product') }}</th>
                                    <th>{{ __('sale.quantity') }}</th>
                                    <th>{{ __('lang_v1.unit') }}</th>
                                </tr>
                                @foreach($parts as $part)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $part['variation_name'] ?? '-' }}</td>
                                        <td>{{ $part['quantity'] ?? 0 }}</td>
                                        <td>{{ $part['unit'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
                <br>
            @endif

            <!-- Compact Checklist Section -->
            @if(!empty($checklists) && count($checklists) > 0)
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <h5 style="margin-top: 0; margin-bottom: 10px; font-weight: 600;">
                            <i class="fa fa-check-square-o"></i> {{ __('repair::lang.pre_repair_checklist') }}
                        </h5>
                    </div>
                    <div class="col-sm-12 col-xs-12">
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            @foreach($checklists as $check)
                                <div style="display: inline-flex; align-items: center; background: #f9f9f9; padding: 5px 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                                    <span style="margin-right: 8px; font-weight: 500;">{{ $check }}:</span>
                                    @if(isset($job->checklist[$check]))
                                        @if($job->checklist[$check] == 'yes')
                                            <span class="label label-success" style="padding: 2px 8px; font-size: 11px;">{{ __('repair::lang.yes') }}</span>
                                        @elseif($job->checklist[$check] == 'no')
                                            <span class="label label-danger" style="padding: 2px 8px; font-size: 11px;">{{ __('repair::lang.no') }}</span>
                                        @elseif($job->checklist[$check] == 'not_applicable')
                                            <span class="label label-default" style="padding: 2px 8px; font-size: 11px;">{{ __('repair::lang.not_applicable_key') }}</span>
                                        @else
                                            <span class="label label-warning" style="padding: 2px 8px; font-size: 11px;">{{ $job->checklist[$check] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">--</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <br>
            @endif

            <!-- Activities Section -->
            <div class="row">
                <div class="col-md-12">
                    <strong>{{ __('lang_v1.activities') }}:</strong><br>
                    <div class="table-responsive" style="margin-top: 10px;">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th>{{ __('messages.date') }}</th>
                                    <th>{{ __('repair::lang.action') }}</th>
                                    <th>{{ __('repair::lang.by') }}</th>
                                    <th>{{ __('repair::lang.note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td>{{ @format_datetime($activity->created_at) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $activity->description)) }}</td>
                                        <td>{{ $activity->causer->user_full_name ?? '-' }}</td>
                                        <td>
                                            @if($activity->getExtraProperty('note'))
                                                {{ $activity->getExtraProperty('note') }}
                                            @elseif($activity->getExtraProperty('update_note'))
                                                {{ $activity->getExtraProperty('update_note') }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">{{ __('messages.no_data_available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <a href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'show'], [$job->id]) }}" 
               class="tw-dw-btn tw-dw-btn-primary tw-text-white" target="_blank">
                <i class="fa fa-eye" aria-hidden="true"></i> {{ __('messages.view') }}
            </a>
            <a href="{{ action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'print'], [$job->id]) }}" 
               class="tw-dw-btn tw-dw-btn-success tw-text-white" target="_blank">
                <i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('repair::lang.print_format_2') }}
            </a>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">
                {{ __('messages.close') }}
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var element = $('div.modal-xl');
        __currency_convert_recursively(element);
    });
</script>
