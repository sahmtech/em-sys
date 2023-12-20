@extends('layouts.app')
@section('title', __('sales::lang.unqualified_contacts'))

@section('content')
@include('sales::layouts.nav_contact')

    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.unqualified_contacts')</span>
        </h1>
    </section>


   
    <section class="content">
      
        
        @component('components.widget', ['class' => 'box-primary'])
         

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cust_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('sales::lang.contact_number')</th>
                            <th>@lang('sales::lang.supplier_business_name')</th>
                            <th>@lang('sales::lang.commercial_register_no')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>
                </table>
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
                    url: "{{ route('unqualified_contacts') }}",

                },
                processing: true,
                serverSide: true,

                columns: [

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
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },

                ]
            });

        });


    </script>

@endsection
