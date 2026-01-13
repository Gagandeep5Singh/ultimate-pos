@extends('layouts.app')
@section('title', 'Add Kamal Tec Customer')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Add Kamal Tec Customer</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => route('kamal-tec-customers.store'), 'method' => 'post', 'id' => 'customer_add_form', 'files' => true]) !!}
    
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <!-- Personal Information Section -->
                    <div class="col-md-12">
                        <h3 class="tw-text-lg tw-font-semibold tw-mb-4 tw-text-gray-700 tw-border-b tw-pb-2">
                            <i class="fa fa-user"></i> Personal Information
                        </h3>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('first_name', 'First Name:*') !!}
                            {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => 'Enter first name'
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('last_name', 'Last Name:*') !!}
                            {!! Form::text('last_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => 'Enter last name'
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('dob', 'Date of Birth:') !!}
                            {!! Form::text('dob', null, [
                                'class' => 'form-control date-picker',
                                'placeholder' => 'Select date of birth',
                                'autocomplete' => 'off'
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('dob_country', 'DOB Country:') !!}
                            {!! Form::text('dob_country', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter country of birth'
                            ]) !!}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Contact Information Section -->
                    <div class="col-md-12">
                        <h3 class="tw-text-lg tw-font-semibold tw-mt-6 tw-mb-4 tw-text-gray-700 tw-border-b tw-pb-2">
                            <i class="fa fa-address-book"></i> Contact Information
                        </h3>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('number', 'Phone Number:') !!}
                            {!! Form::text('number', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter phone number'
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', 'Email:') !!}
                            {!! Form::email('email', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter email address'
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('address', 'Address:') !!}
                            {!! Form::textarea('address', null, [
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => 'Enter full address'
                            ]) !!}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Identification Section -->
                    <div class="col-md-12">
                        <h3 class="tw-text-lg tw-font-semibold tw-mt-6 tw-mb-4 tw-text-gray-700 tw-border-b tw-pb-2">
                            <i class="fa fa-id-card"></i> Identification
                        </h3>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('nif', 'NIF:') !!}
                            {!! Form::text('nif', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter NIF number'
                            ]) !!}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 text-center" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> @lang('messages.save')
                        </button>
                        <a href="{{ route('kamal-tec-customers.index') }}" class="btn btn-default btn-lg">
                            @lang('messages.cancel')
                        </a>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize date picker
        $('.date-picker').datepicker({
            autoclose: true,
            format: datepicker_date_format,
            endDate: 'today'
        });
    });
</script>
@endsection

