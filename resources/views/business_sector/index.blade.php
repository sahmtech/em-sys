@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.draft_contacts')</span>
        </h1>
    </section>

    <!-- Main content -->
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
                            <th>@lang('sales::lang.supplier_business_name')</th>
                            <th>@lang('sales::lang.contact_name')</th>
                            <th>@lang('sales::lang.created_by')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('sales::lang.message')</th>
                            <th>@lang('sales::lang.created_at')</th>


                        </tr>
                    </thead>
                </table>

            </div>
        @endcomponent





    </section>
    <!-- /.content -->
@stop


@section('javascript')



    <script>
        $(document).ready(function() {


            var customers_table = $('#cust_table').DataTable({
                ajax: {
                    url: "{{ route('businessSector') }}",

                },
                processing: true,
                serverSide: true,
                info: false,


                columns: [

                    {
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },



                    {
                        data: 'created_by',
                        name: 'created_by'
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
                        data: 'note_draft',
                        name: 'note_draft'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },


                ]
            });






        });
    </script>

@endsection
