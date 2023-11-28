@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">
                <a href="{{ route('filteredRequests', ['filter' => 'new']) }}">
                    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('followup::lang.new_request') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $new_requests }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
                <a href="{{ route('filteredRequests', ['filter' => 'under_process']) }}">
                    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('followup::lang.on_going_request') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $on_going_requests }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
                <a href="{{ route('filteredRequests', ['filter' => 'finished']) }}">
                    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('followup::lang.finished_request') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $finished_requests }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
                <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests']) }}">
                    <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('followup::lang.total_requests') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $total_requests }}</h4>
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
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.residency')</th>
                                    <th>@lang('followup::lang.project')</th>
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
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.residency')</th>
                                    <th>@lang('followup::lang.project')</th>
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
                        data: 'worker_name'
                    },
                    {
                        data: 'residency'
                    },
                    {
                        data: 'project'
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
    <script type="text/javascript">
        $(document).ready(function() {

            $('#within_two_month_expiry_residency_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('withinTwoMonthExpiryResidency') }}",
                },
                columns: [{
                        data: 'worker_name'
                    },
                    {
                        data: 'residency'
                    },
                    {
                        data: 'project'
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
