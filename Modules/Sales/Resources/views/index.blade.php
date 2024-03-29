@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('sales.get_all_workers') }}">
                    <div class="custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>

                                        <h5 style="color:#fff">{{ __('sales::lang.workers_count') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $workers_count }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('sales.get_active_workers', ['filter' => 'under_process']) }}">
                    <div class="custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('sales::lang.active_workers_count') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $active_workers_count }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('sales.get_inactive_workers') }}">
                    <div class="custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('sales::lang.inactive_workers_count') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $inactive_workers_count }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
            <div class="col-md-3">
                 <a href="{{ route('under_study_offer_prices') }}">
                    <div class="custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('sales::lang.under_study_price_offers') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $under_study_price_offers }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </a>
            </div>
        </div>
        <br>    <br>
        <div class="row">
            <div class="col-md-6">
                <div class="custom_table_card_6">
                    <h4> @lang('sales::lang.operation_available_contracts')</h4>
                    <div class="table-responsive custom_table3">
                        <table class="table table-bordered table-striped" id="contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('sales::lang.number_of_contract')</th>
                                    <th>@lang('sales::lang.offer_price_number')</th>
                                    <th>@lang('sales::lang.remaning_quantity')</th>

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
    </section>

    <!-- Main content -->
    <section class="content">




    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {!! $chart->script() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            var contracts_table = $('#contracts_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                scrollCollapse: true,
                paging: false,
                info: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('getOperationAvailableContracts') }}"
                },

                columns: [

                    {
                        data: 'number_of_contract'
                    },
                    {
                        data: 'ref_no'
                    },
                    {
                        data: 'total_quantity'
                    },

                ],
            });


        });


        $(document).ready(function() {

            $('#within_two_month_expiry_contracts_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('sales.withinTwoMonthExpiryContracts') }}",
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
                        data: 'customer_name'
                    },
                    {
                        data: 'end_date'
                    },
                ]

            });
        });
    </script>
@endsection
