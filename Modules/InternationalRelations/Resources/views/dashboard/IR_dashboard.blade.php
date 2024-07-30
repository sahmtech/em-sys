@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">


                <a
                    href="{{ action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index']) }}">
                    <div class="col-md-2">
                        <div class="custom_card">

                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">أوامر الطلب</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $operations_count }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a
                    href="{{ action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'proposed_laborIndex']) }}">
                    <div class="col-md-2">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">العمال المرشحيين</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $proposed_workers_count }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a
                    href="{{ action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'accepted_workers']) }}">
                    <div class="col-md-2">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">العمال المقبوليين</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $accepted_workers_count }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>

                <a
                    href="{{ action([\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'index']) }}">
                    <div class="col-md-2">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">الطلبات قيد الاجراء</h5>
                                        </div>
                                        <div>
                                            <p class="w-value">{{ $requestsProcess_count }}</p>
                                            <h4 style="color:#fff"></h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>

                <a
                    href="{{ action([\Modules\InternationalRelations\Http\Controllers\VisaCardController::class, 'index']) }}">
                    <div class="col-md-2">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">التأشيرات</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $visaCards_count }}</h4>
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




    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12 ">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('essentials::lang.requests'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="requests_table">
                            <thead>
                                <tr>
                                    <th>@lang('request.company')</th>
                                    <th>@lang('followup::lang.request_number')</th>
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.eqama_number')</th>

                                    <th>@lang('followup::lang.request_type')</th>
                                    <th>@lang('followup::lang.request_date')</th>
                                    <th>@lang('followup::lang.status')</th>
                                    <th>@lang('followup::lang.note')</th>



                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>


        </div>



    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('allIrRequests') }}"
                },

                columns: [{
                        data: 'company_id'
                    },
                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    // {
                    //     data: 'assigned_to'
                    // },
                    {
                        data: 'request_type_id',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('followup::lang.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('followup::lang.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('followup::lang.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('followup::lang.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('followup::lang.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('followup::lang.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('followup::lang.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('followup::lang.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('followup::lang.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('followup::lang.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('followup::lang.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('followup::lang.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('followup::lang.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('followup::lang.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('followup::lang.chamberRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('followup::lang.cancleContractRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('followup::lang.WarningRequest')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },





                ],
            });

        });
    </script>
@endsection
