@extends('layouts.app')
@section('title', 'Customer Details')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Customer Details - {{ $customer->first_name }} {{ $customer->last_name }}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-4">
            @component('components.widget', ['class' => 'box-solid'])
                <h3 class="tw-text-lg tw-font-semibold tw-mb-4">Personal Information</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>First Name:</th>
                        <td>{{ $customer->first_name }}</td>
                    </tr>
                    <tr>
                        <th>Last Name:</th>
                        <td>{{ $customer->last_name }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth:</th>
                        <td>{{ $customer->dob ? @format_date($customer->dob) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>DOB Country:</th>
                        <td>{{ $customer->dob_country ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>NIF:</th>
                        <td>{{ $customer->nif ?? '-' }}</td>
                    </tr>
                </table>
            @endcomponent
        </div>
        
        <div class="col-md-4">
            @component('components.widget', ['class' => 'box-solid'])
                <h3 class="tw-text-lg tw-font-semibold tw-mb-4">Contact Information</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Phone Number:</th>
                        <td>{{ $customer->number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td>{{ $customer->address ?? '-' }}</td>
                    </tr>
                </table>
            @endcomponent
        </div>
        
        <div class="col-md-4">
            @component('components.widget', ['class' => 'box-solid'])
                <h3 class="tw-text-lg tw-font-semibold tw-mb-4">Sales Summary</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Total Sales:</th>
                        <td><strong>{{ $sales->count() }}</strong></td>
                    </tr>
                    <tr>
                        <th>Total Amount:</th>
                        <td><strong><span class="display_currency" data-currency_symbol="true">{{ $sales->sum('total_amount') }}</span></strong></td>
                    </tr>
                </table>
                <div class="text-center" style="margin-top: 15px;">
                    <a href="{{ route('kamal-tec-customers.edit', $customer->id) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Edit Customer
                    </a>
                </div>
            @endcomponent
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid', 'title' => 'Purchase History'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>KT Invoice No</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ @format_date($sale->sale_date) }}</td>
                                    <td>{{ $sale->invoice_no }}</td>
                                    <td>{{ $sale->kt_invoice_no ?? '-' }}</td>
                                    <td><span class="display_currency" data-currency_symbol="true">{{ $sale->total_amount }}</span></td>
                                    <td>
                                        <span class="label label-{{ $sale->status == 'open' ? 'warning' : ($sale->status == 'closed' ? 'success' : 'danger') }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('kamal-tec-sales.show', $sale->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No sales found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection

