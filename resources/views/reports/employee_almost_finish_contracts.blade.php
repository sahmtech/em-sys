@extends('layouts.app')
@section('title', __('essentials::lang.employee_almost_finish_contracts'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.employee_almost_finish_contracts')
        </h1>

        <head>
<style>
    .bg-green {
        background-color: #28a745; 
        color: #ffffff; 
    }
</style>
</head>
        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee_almost_finish_contracts">
                                <thead class="bg-green">
                                    <tr>
                                        <th>@lang('essentials::lang.name')</th>
                                        <th>@lang('followup::lang.customer_name')</th>
                                        <th>@lang('followup::lang.project')</th>  
                                        <th>@lang('followup::lang.end_date')</th>
                                       
    
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


                var employee_almost_finish_contracts;

                function reloadDataTable() {
                    employee_almost_finish_contracts.ajax.reload();
                }

                employee_almost_finish_contracts = $('#employee_almost_finish_contracts').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('employee_almost_finish_contracts') }}",

                    },

                    columns: [

                    {
                            data: 'worker_name'
                        },
                         {
                            data: 'customer_name'
                        },
                       
                        {
                            data: 'project'
                        },
                       
                        {
                            data: 'end_date'
                        },
                    
                    ],
                });




            });
        </script>
    @endsection
