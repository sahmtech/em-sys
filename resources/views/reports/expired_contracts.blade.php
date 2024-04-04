@extends('layouts.app')
@section('title', __('essentials::lang._expired_contracts'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang._expired_contracts')
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
                            <table class="table table-bordered table-striped" id="expired_contracts">
                                <thead class="bg-green">
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


                var expired_contracts;

                function reloadDataTable() {
                    expired_contracts.ajax.reload();
                }

                expired_contracts = $('#expired_contracts').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('expired_contracts') }}",

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
