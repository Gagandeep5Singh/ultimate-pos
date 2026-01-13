<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\KamalTecSaleController::class, 'updateFloaRef'], [$sale->id]), 'method' => 'post', 'id' => 'edit_floa_ref_form', 'class' => 'edit_floa_ref_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Update Floa Ref & KT Invoice No - Invoice No: {{ $sale->invoice_no }}</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('floa_ref', 'Floa Ref:') !!}
                        {!! Form::text('floa_ref', $sale->floa_ref, [
                            'class' => 'form-control',
                            'id' => 'floa_ref',
                            'placeholder' => 'Enter Floa Ref',
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('kt_invoice_no', 'KT Invoice No:') !!}
                        {!! Form::text('kt_invoice_no', $sale->kt_invoice_no, [
                            'class' => 'form-control',
                            'id' => 'kt_invoice_no',
                            'placeholder' => 'Enter KT Invoice No',
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

