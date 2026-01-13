@extends('layouts.app')
@section('title', __('lang_v1.update_product_details'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'lang_v1.update_product_details' )
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif

    @if (session('status'))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty(session('status')['msg']))
                        {{session('status')['msg']}}
                    @endif
                </div>
            </div>  
        </div>     
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.import_export_product_details')])
            <div class="row">
                <div class="col-sm-6">
                   <a href="{{ route('products.export_product_details') }}" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
    @lang('lang_v1.export_product_details_template')
</a>
                </div>
                <div class="col-sm-6">
                    {!! Form::open(['url' => route('products.import_product_details'), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="form-group">
                        {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                        {!! Form::file('product_details_file', ['required' => 'required']); !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.submit')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="col-sm-12">
                    <h4>@lang('lang_v1.instructions'):</h4>
                    <ol>
                        <li>@lang('lang_v1.details_import_instruction_1')</li>
                        <li>@lang('lang_v1.details_import_instruction_2')</li>
                        <li>@lang('lang_v1.details_import_instruction_3')</li>
                        <li>@lang('lang_v1.details_import_instruction_4')</li>
                    </ol>
                    
                </div>
            </div>
    @endcomponent
    

</section>
<!-- /.content -->
@stop
