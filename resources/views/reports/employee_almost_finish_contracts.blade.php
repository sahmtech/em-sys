@extends('layouts.app')
@section('title', __('essentials::lang.employee_almost_finish_contracts'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.employee_almost_finish_contracts')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee_almost_finish_contracts">
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


                var employee_almost_finish_contracts;

                function reloadDataTable() {
                    employee_almost_finish_contracts.ajax.reload();
                }

                employee_almost_finish_contracts = $('#employee_almost_finish_contracts').DataTable({
                    processing: true,
                    serverSide: true,
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('essentials::lang.work_cards')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.employee_almost_finish_contracts')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
                    ajax: {
                        url: "{{ route('employee_almost_finish_contracts') }}",

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
