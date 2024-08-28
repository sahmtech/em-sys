@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

            <div class="row widget-statistic">
                <a href="{{ route('followup.getFilteredRequests', ['filter' => 'today_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">

                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.today_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $today_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('followup.getFilteredRequests', ['filter' => 'pending_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.pending_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $pending_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('followup.getFilteredRequests', ['filter' => 'completed_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.completed_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $completed_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('followup.getFilteredRequests', ['filter' => 'all']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.all_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $all_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>


            </div>
            <br>

        </div>
        <div class="row">
            <div class="col-md-12 custom_table">
                @component('components.widget', [
                    'class' => 'box-solid',
                    'title' => __('followup::lang.expiry_within_two_months_contracts'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="within_two_month_expiry_contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('followup::lang.sponsor')</th>
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.eqama')</th>
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

        <div class="row">
            <div class="col-md-12 custom_table">
                @component('components.widget', [
                    'class' => 'box-solid',
                    'title' => __('followup::lang.within_two_month_expiry_residency'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="within_two_month_expiry_residency_table">
                            <thead>
                                <tr>
                                    <th>@lang('followup::lang.sponsor')</th>
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.eqama')</th>
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

        <div class="row">
            <div class="col-md-12 custom_table">
                @component('components.widget', [
                    'class' => 'box-solid',
                    'title' => __('followup::lang.within_two_month_expiry_word_cards'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="within_two_month_expiry_work_cards_table">
                            <thead>
                                <tr>
                                    <th>@lang('followup::lang.sponsor')</th>
                                    <th>@lang('followup::lang.worker_name')</th>


                                    <th>@lang('followup::lang.eqama')</th>
                                    <th>@lang('followup::lang.work_card')</th>
                                    <th>@lang('followup::lang.project')</th>
                                    <th>@lang('followup::lang.customer_name')</th>
                                    <th>@lang('followup::lang.end_date')</th>

                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>


        </div>

    </section>

    <!-- Main content -->
    <section class="content">




    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('#within_two_month_expiry_contracts_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('withinTwoMonthExpiryContracts') }}",
                },
                columns: [{
                        data: 'sponser'
                    },
                    {
                        data: 'worker_name'
                    },

                    {
                        data: 'residency'
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

                    // {
                    //     data: 'contract_form',
                    //     render: function(data, type, full, meta) {
                    //         switch (data) {
                    //             case 'monthly_cost':
                    //                 return '{{ trans('sales::lang.monthly_cost') }}';
                    //             case 'operating_fees':
                    //                 return '{{ trans('sales::lang.operating_fees') }}';

                    //             default:
                    //                 return data;
                    //         }
                    //     }
                    // },
                ]

            });
            $('#within_two_month_expiry_residency_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('withinTwoMonthExpiryResidency') }}",
                },
                columns: [{
                        data: 'sponser'
                    },
                    {
                        data: 'worker_name'
                    },
                    {
                        data: 'residency'
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

                    // {
                    //     data: 'contract_form',
                    //     render: function(data, type, full, meta) {
                    //         switch (data) {
                    //             case 'monthly_cost':
                    //                 return '{{ trans('sales::lang.monthly_cost') }}';
                    //             case 'operating_fees':
                    //                 return '{{ trans('sales::lang.operating_fees') }}';

                    //             default:
                    //                 return data;
                    //         }
                    //     }
                    // },
                ]

            });
            $('#within_two_month_expiry_work_cards_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('withinTwoMonthExpiryWorkCard') }}",
                },
                columns: [{
                        data: 'sponser'
                    },
                    {
                        data: 'worker_name'
                    },
                    {
                        data: 'residency'
                    },
                    {
                        data: 'work_card_no'
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

                    // {
                    //     data: 'contract_form',
                    //     render: function(data, type, full, meta) {
                    //         switch (data) {
                    //             case 'monthly_cost':
                    //                 return '{{ trans('sales::lang.monthly_cost') }}';
                    //             case 'operating_fees':
                    //                 return '{{ trans('sales::lang.operating_fees') }}';

                    //             default:
                    //                 return data;
                    //         }
                    //     }
                    // },
                ]

            });



        });
    </script>
@endsection
