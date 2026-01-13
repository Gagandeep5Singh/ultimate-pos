@extends('layouts.app')
@section('title',  __('business.business_locations'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.select_location')</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => route('login.store_location'), 'method' => 'post', 'id' => 'select_location_form']) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.business_location') . ':*') !!}
                            {!! Form::select('location_id', $business_locations, $selected_location, ['class' => 'form-control select2', 'required', 'placeholder' => __('lang_v1.select_location')]) !!}
                            <small class="help-block">@lang('lang_v1.select_location')</small>
                        </div>
                    </div>
                    <div class="col-sm-8 col-sm-offset-2">
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">
                            @lang('messages.continue')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</section>
<!-- /.content -->
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#select_location_form').submit(function(e) {
            var location = $('select[name="location_id"]').val();
            if (!location) {
                e.preventDefault();
                toastr.error('@lang("lang_v1.please_select_location")');
            }
        });
    });
</script>
@endsection

