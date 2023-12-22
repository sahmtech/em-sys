@extends('layouts.app')
@section('title', __('sales::lang.qualified_contacts'))

@section('content')
@include('sales::layouts.nav_contact')

    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.qualified_contacts')</span>
        </h1>
    </section>


   
    <section class="content">
      

        @component('components.widget', ['class' => 'box-primary'])
       


            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cust_table">
                    <thead>
                        <tr>
                            {{-- <th>
                                <input type="checkbox" id="select-all">
                            </th> --}}
                            <th>#</th>
                            <th>@lang('sales::lang.contact_number')</th>
                            <th>@lang('sales::lang.supplier_business_name')</th>
                            <th>@lang('sales::lang.commercial_register_no')</th>
                            <th>@lang('sales::lang.qualified_by')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('sales::lang.qualified_on')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>
                </table>
                {{-- <div style="margin-bottom: 10px;">
                    <button type="button" class="btn btn-success btn-sm custom-btn" id="converted_client-selected">
                        @lang('sales::lang.change_to_converted_client')
                    </button>
                   

                </div> --}}
            </div>
        @endcomponent


      
    </section>
    <!-- /.content -->

@endsection

@section('javascript')


    <script type="text/javascript">
       
        $(document).ready(function() {
            var customers_table = $('#cust_table').DataTable({
         
                ajax: {
                    url: "{{ route('qualified_contacts') }}",

                },
                processing: true,
                serverSide: true,

                columns: [
                    // {
                    //     data: null,
                    //     render: function(data, type, row, meta) {
                    //         return '<input type="checkbox" class="select-row" data-id="' + row.id + '">';
                    //     },
                    //     orderable: false,
                    //     searchable: false,
                    // },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'contact_id',
                        name: 'contact_id'
                    },
                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },

                    {
                        data: 'commercial_register_no',
                        name: 'commercial_register_no'
                    },
                    {
                        data: 'qualified_by',
                        name: 'qualified_by'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'qualified_on',
                        name: 'qualified_on'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },

                ]
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#cust_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === customers_table.rows()
                    .count());
            });

            $('#converted_client-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return $(this).data('id');
                }).get();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('change_to_converted_client') }}',
                    data: {
                        selectedRows: selectedRows,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            customers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });
            });

        });


    </script>

@endsection
