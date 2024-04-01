@extends('layouts.app')
@section('title', __('essentials::lang.employee_finish_contracts'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.employee_finish_contracts')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee_finish_contracts">
                                <thead>
                                    <tr>
                                        <th>@lang('essentials::lang.emp_name')</th>
                                
                                        <th>@lang('followup::lang.project')</th>
                                        <th>@lang('followup::lang.customer_name')</th>
                                        <th>@lang('followup::lang.end_date')</th>
                                        <th></th>
    
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


                var employee_finish_contracts;

                function reloadDataTable() {
                    employee_finish_contracts.ajax.reload();
                }

                employee_finish_contracts = $('#employee_finish_contracts').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('employee_finish_contracts') }}",

                    },

                    columns: [

                    {
                            data: 'worker_name'
                        },
                       
                        {
                            data: 'project'
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'end_date'
                        },
                    
                    ],
                });




            });
        </script>
    @endsection
