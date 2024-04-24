@extends('layouts.app')
@section('title', __('essentials::lang.projects'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.projects')
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
                            <table class="table table-bordered table-striped" id="projects">
                                <thead class="bg-green">
                                    <tr>
                                        <th>#</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.contact_name')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.contact_location_name')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.contract_number')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.start_date')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.end_date')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.active_worker_count')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.worker_count')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.contractDuration')</th>
                                        <th class="table-td-width-100px">@lang('sales::lang.contract_form')</th>
                                        <th class="table-td-width-100px">@lang('followup::lang.project_status')</th>
                                        <th class="table-td-width-100px">@lang('followup::lang.project_type')</th>

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


                var projects;

                function reloadDataTable() {
                    projects.ajax.reload();
                }

                projects = $('#projects').DataTable({
                    processing: true,
                    serverSide: true,
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('followup::lang.followUp')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.projects')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
                    ajax: {
                        url: "{{ route('reports-projects') }}",

                    },
                    
                    columns: [

                    {
                        data: 'id'
                    },
                    {
                        data: 'contact_name'
                    },
                   
                    {
                        data: 'contact_location_name',
                        render: function(data, type, full, meta) {
                            return '<a href="#" class="open-contract-modal" data-id="' + full.id + '">' + data + '</a>';
                        }
                    },
                     
                    {
                        data: 'number_of_contract'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'active_worker_count'
                    },
                    {
                        data: 'worker_count'
                    },
                    {
                        data: 'duration'
                    },
                    {
                        data: 'contract_form',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'monthly_cost':
                                    return '{{ trans('sales::lang.monthly_cost') }}';
                                case 'operating_fees':
                                    return '{{ trans('sales::lang.operating_fees') }}';

                                default:
                                    return data;
                            }
                        }
                    },

                    {
                        data: 'status',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'Done':
                                    return '{{ trans('sales::lang.Done') }}';
                                case 'Under_process':
                                    return '{{ trans('sales::lang.Under_process') }}';


                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'type',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'External':
                                    return '{{ trans('sales::lang.external') }}';
                                case 'Internal':
                                    return '{{ trans('sales::lang.internal') }}';

                                default:
                                    return data;
                            }
                        }
                    },



                    ],
                });




            });
        </script>
    @endsection
