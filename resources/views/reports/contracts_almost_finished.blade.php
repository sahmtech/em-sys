@extends('layouts.app')
@section('title', __('essentials::lang.contracts_almost_finished'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.contracts_almost_finished')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="contracts_almost_finished">
                                <thead>
                                    <tr>

                                        <th>@lang('sales::lang.number_of_contract')</th>
                                        <th>@lang('sales::lang.customer_name')</th>
                                        <th>@lang('sales::lang.contract_status')</th>

                                        <th>@lang('sales::lang.start_date')</th>
                                        <th>@lang('sales::lang.contract_duration')</th>
                                        <th>@lang('sales::lang.end_date')</th>
                                        <th>@lang('sales::lang.contract_form')</th>
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


                var contracts_almost_finished;

                function reloadDataTable() {
                    contracts_almost_finished.ajax.reload();
                }

                contracts_almost_finished = $('#contracts_almost_finished').DataTable({
                    processing: true,
                    serverSide: true,
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('sales::lang.sales')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.contracts_almost_finished')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
                    ajax: {
                        url: "{{ route('contracts_almost_finished') }}",

                    },

                    columns: [

                        {
                            data: 'number_of_contract'
                        },
                        {
                            data: 'sales_project_id'
                        },

                        {
                            data: 'status',
                            render: function(data, type, row) {
                                if (data === 'valid') {
                                    return '@lang('sales::lang.valid')';

                                } else {
                                    return '@lang('sales::lang.finished')';
                                }
                            }
                        },


                        {
                            data: 'start_date'
                        },
                        {
                            data: 'contract_duration',
                            render: function(data, type, row) {
                                var unit = row.contract_per_period;
                                if (data !== null && data !== undefined) {
                                    var translatedUnit = (unit === 'years') ? '@lang('sales::lang.years')' :
                                        '@lang('sales::lang.months')';
                                    return data + ' ' + translatedUnit;
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            data: 'end_date'
                        },

                        {
                            data: 'contract_form',
                            render: function(data, type, row) {
                                if (data === 'monthly_cost') {
                                    return '@lang('sales::lang.monthly_cost')';

                                } else if (data === 'operating_fees') {
                                    return '@lang('sales::lang.operating_fees')';
                                } else {
                                    return ' ';
                                }

                            }
                        },

                    ],
                });




            });
        </script>
    @endsection
