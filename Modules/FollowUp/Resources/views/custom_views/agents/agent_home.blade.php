@extends('layouts.app')
@section('title', __('home.home'))


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header content-header-custom">

        <br>
        <div class="row">
            <div class="col-md-3 ">
                <a href="{{ route('agent_workers') }}">
                    <div class=" custom_card_customer">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h3 id="counter-0">0</h3>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h5>{{ __('agent.worker') }}</h5>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3 ">
                <a href="{{ route('agent_workers') }}">
                    <div class="  custom_card_customer">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">

                                    <div>
                                        <p class="w-value"></p>
                                        <h3 id="counter-1">0</h3>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h5>{{ __('agent.active_worker') }}</h5>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3 ">
                <a href="{{ route('agent_workers') }}">
                    <div class="  custom_card_customer">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">

                                    <div>
                                        <p class="w-value"></p>
                                        <h3 id="counter-2">0</h3>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h5>{{ __('agent.inactive_worker') }}</h5>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('agent_workers') }}">
                    <div class="  custom_card_customer">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">

                                    <div>
                                        <p class="w-value"></p>
                                        <h3 id="counter-3">0</h3>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h5>{{ __('agent.under_process_worker_request') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-md-6 ">
                <div class="custom_table_card_6">
                    <h4> @lang('followup::lang.customer_home_workers_requests')</h4>
                    <div class="table-responsive custom_table3">
                        <table class="table table-bordered table-striped" id="requests_table">
                            <thead>
                                <tr>
                                    <th>@lang('followup::lang.request_number')</th>
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.eqama_number')</th>
                                    <th>@lang('followup::lang.project_name')</th>
                                    <th>@lang('followup::lang.request_type')</th>
                                    <th>@lang('followup::lang.request_date')</th>
                                    <th>@lang('followup::lang.status')</th>
                                    <th>@lang('followup::lang.note')</th>
                                    <th>@lang('followup::lang.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @component('components.widget', [
                    'class' => 'chart_card',
                ])
                    <h4> @lang('followup::lang.customer_home_workers')</h4>
                    {!! $chart->container() !!}
                @endcomponent
            </div>
        </div>

    </section>
    <!-- Main content -->

@stop
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {!! $chart->script() !!}


    <script type="text/javascript">
        $(document).ready(function() {

            var counters = [
                {{ $workers_count }},
                {{ $active_workers_count }},
                {{ $inactive_workers_count }},
                {{ $total_requests ?? 0 }}
                // Add more counter values if needed
            ];

            // Iterate through each counter
            $.each(counters, function(index, value) {
                var counterElement = $('#counter-' + index);

                $({
                    count: 0
                }).animate({
                    count: value
                }, {
                    duration: 2000,
                    step: function() {
                        counterElement.text(Math.floor(this.count));
                    },
                    complete: function() {
                        counterElement.text(this.count);
                    }
                });
            });
            // var requests_table = $('#requests_table').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     fixedHeader: false,
            //     scrollCollapse: true,
            //     paging: false,
            //     info: false,
            //     dom: 'rtip',
            //     ajax: {
            //         url: "{{ route('agent_workers_requests') }}"
            //     },

            //     columns: [

            //         {
            //             data: 'request_no'
            //         },

            //         {
            //             data: 'user'
            //         },
            //         {
            //             data: 'id_proof_number'
            //         },
            //         {
            //             data: 'assigned_to'
            //         },
            //         {
            //             data: 'type',
            //             render: function(data, type, row) {
            //                 if (data === 'exitRequest') {
            //                     return '@lang('followup::lang.exitRequest')';

            //                 } else if (data === 'returnRequest') {
            //                     return '@lang('followup::lang.returnRequest')';
            //                 } else if (data === 'escapeRequest') {
            //                     return '@lang('followup::lang.escapeRequest')';
            //                 } else if (data === 'advanceSalary') {
            //                     return '@lang('followup::lang.advanceSalary')';
            //                 } else if (data === 'leavesAndDepartures') {
            //                     return '@lang('followup::lang.leavesAndDepartures')';
            //                 } else if (data === 'atmCard') {
            //                     return '@lang('followup::lang.atmCard')';
            //                 } else if (data === 'residenceRenewal') {
            //                     return '@lang('followup::lang.residenceRenewal')';
            //                 } else if (data === 'workerTransfer') {
            //                     return '@lang('followup::lang.workerTransfer')';
            //                 } else if (data === 'residenceCard') {
            //                     return '@lang('followup::lang.residenceCard')';
            //                 } else if (data === 'workInjuriesRequest') {
            //                     return '@lang('followup::lang.workInjuriesRequest')';
            //                 } else if (data === 'residenceEditRequest') {
            //                     return '@lang('followup::lang.residenceEditRequest')';
            //                 } else if (data === 'baladyCardRequest') {
            //                     return '@lang('followup::lang.baladyCardRequest')';
            //                 } else if (data === 'mofaRequest') {
            //                     return '@lang('followup::lang.mofaRequest')';
            //                 } else if (data === 'insuranceUpgradeRequest') {
            //                     return '@lang('followup::lang.insuranceUpgradeRequest')';
            //                 } else if (data === 'chamberRequest') {
            //                     return '@lang('followup::lang.chamberRequest')';
            //                 } else if (data === 'cancleContractRequest') {
            //                     return '@lang('followup::lang.cancleContractRequest')';
            //                 } else {
            //                     return data;
            //                 }
            //             }
            //         },
            //         {
            //             data: 'created_at'
            //         },
            //         {
            //             data: 'status',

            //         },
            //         {
            //             data: 'note'
            //         },




            //     ],
            // });


        });
    </script>
@endsection
