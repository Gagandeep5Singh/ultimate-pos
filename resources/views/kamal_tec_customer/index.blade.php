@extends('layouts.app')
@section('title', 'Kamal Tec Customers')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Kamal Tec Customers</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Customers'])
                @slot('tool')
                    <div class="box-tools">
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right tw-m-2"
                            href="{{route('kamal-tec-customers.create')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="kamal_tec_customer_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('contact.contact')</th>
                                <th>DOB</th>
                                <th>NIF</th>
                                <th>@lang('contact.mobile')</th>
                                <th>@lang('business.email')</th>
                                <th>Total Sales</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var kamal_tec_customer_table = $('#kamal_tec_customer_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('kamal-tec-customers.index') }}',
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Error loading customer data. Please check the console for details.');
                }
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'full_name', name: 'full_name', orderable: false },
                { data: 'dob', name: 'kamal_tec_customers.dob' },
                { data: 'nif', name: 'kamal_tec_customers.nif' },
                { data: 'number', name: 'kamal_tec_customers.number' },
                { data: 'email', name: 'kamal_tec_customers.email' },
                { data: 'total_sales', name: 'total_sales', orderable: false },
                { data: 'total_amount', name: 'total_amount', orderable: false },
            ],
        });

        $(document).on('click', '.delete-customer', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: "DELETE",
                        url: url,
                        dataType: "json",
                        success: function(result) {
                            if (result.success == 1) {
                                toastr.success(result.msg);
                                kamal_tec_customer_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error('Failed to delete customer. Please try again.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection

