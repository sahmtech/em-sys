@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">


                <a href="{{ action([Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestForm']) }}">
                    <div class="col-md-3">
                    <div class="custom_card">

                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">استمارات تنتهي خالل 15 يوم</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{$latestForm_count}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
 </div>
                    </div>
                </a>
                <a href="">
                    <div class="col-md-3">
                        <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">تأمين مركبات ينتهي خلال 15 يوم</h5>
                                    </div>
                                   
                                </div>
                            </div>

                        </div>
</div>
                    </div>
                </a>
                
                <a href="{{ action([Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestChangeOil']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">تغيير الزيت خلال 15 يوم </h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{$latestChangeOil_count}}</h4>
                                    </div>
                                    
                                </div>
                            </div>

                        </div>
 </div>
                    </div>
                </a>
                <a href="{{ action([Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestMaintenances']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">الفحص الدوري خلال 15 يوم</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{$latestMaintenance_count}}</h4>
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
                    'title' => __('essentials::lang.requests'),
                ])
           
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="within_two_month_expiry_contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.request_number')</th>
                                    <th>@lang('essentials::lang.worker_name')</th>
                                    <th>@lang('essentials::lang.residency_number')</th>
                                    <th>@lang('essentials::lang.request_type')</th>
                                    <th>@lang('essentials::lang.date_application')</th>
                                    <th>@lang('essentials::lang.Status')</th>
                                    <th>@lang('essentials::lang.nots')</th>
                                    <th>@lang('essentials::lang.actions')</th>
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
{{-- 
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
@endsection --}}
