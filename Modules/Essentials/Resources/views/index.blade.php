@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">

                <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                    <div class="widget widget-one_hybrid widget-engagement">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div>
                                    <p class="w-value"></p>
                                    <h5 style="color:#fff">{{ __('essentials::lang.number_employees_staff') }}</h5>
                                </div>
                                <div>
                                    <p class="w-value"></p>
                                    <h4 style="color:#fff">{{ $num_employee_staff }}</h4>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                    <div class="widget widget-one_hybrid widget-engagement">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div>
                                    <p class="w-value"></p>
                                    <h5 style="color:#fff">{{ __('essentials::lang.number_employees') }}</h5>
                                </div>
                                <div>
                                    <p class="w-value"></p>
                                    <h4 style="color:#fff">{{ $num_employees }}</h4>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                    <div class="widget widget-one_hybrid widget-engagement">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div>
                                    <p class="w-value"></p>
                                    <h5 style="color:#fff">{{ __('essentials::lang.number_workers') }}</h5>
                                </div>
                                <div>
                                    <p class="w-value"></p>
                                    <h4 style="color:#fff">{{ $num_workers }}</h4>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="col-xl-3 col-lg-4 col-md-4 col-sm-4 col-12 layout-spacing custom_card">

                    <div class="widget widget-one_hybrid widget-engagement">
                        <div class="widget-heading">
                            <div class="w-title">
                                <div>
                                    <p class="w-value"></p>
                                    <h5 style="color:#fff">{{ __('essentials::lang.number_managers') }}</h5>
                                </div>
                                <div>
                                    <p class="w-value"></p>
                                    <h4 style="color:#fff">{{ $num_managers }}</h4>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>


            </div>
            <br>
        </div>

        <div class="row">
            <div class="col-md-5 custom_table">
                @component('components.widget', ['class' => 'box-solid', 'title' => __('essentials::lang.residence_permits')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="official_documents_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.doc_number')</th>
                                    <th>@lang('essentials::lang.expired_date')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="row">
                <div class="col-md-5 custom_table">
                    @component('components.widget', [
                        'class' => 'box-primary',
                        'title' => __('essentials::lang.number_employees_staff'),
                    ])
                        {!! $chart->container() !!}
                    @endcomponent
                </div>
            </div>


        </div>
        <div class="row">
            <div class="col-md-5 custom_table">
                @component('components.widget', ['class' => 'box-solid', 'title' => __('essentials::lang.contracts')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employees_contracts_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.contract_number')</th>
                                    <th>@lang('essentials::lang.contract_end_date')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-5 custom_table">
                @component('components.widget', ['class' => 'box-solid', 'title' => __('essentials::lang.leaves')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="leave_table">
                            <thead>
                                <tr>
                                    <th>@lang('purchase.ref_no')</th>

                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('lang_v1.date')</th>

                                    <th>@lang('sale.status')</th>
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
    {!! $chart->script() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            var employees_contracts_table;

            function reloadDataTable() {
                employees_contracts_table.ajax.reload();
            }

            employees_contracts_table = $('#employees_contracts_table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'lrtip',
                lengthMenu: [5, 10, 25, 50],
                pageLength: 5,
                ajax: {
                    url: "{{ route('employeeContracts') }}",
                },

                columns: [{
                        data: 'user'
                    },
                    {
                        data: 'contract_number'
                    },

                    {
                        data: 'contract_end_date'
                    },



                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'valid') {
                                return '@lang('essentials::lang.valid')';
                            } else {
                                return '@lang('essentials::lang.canceled')';
                            }
                        }
                    },

                ],
            });



        });
        $(document).ready(function() {

            $('#official_documents_table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'lrtip',
                lengthMenu: [5, 10, 25, 50],
                pageLength: 5,
                ajax: {
                    "url": "{{ action([\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'index']) }}",
                    data: function(d) {
                        d.isForHome = true;
                    }
                },

                columns: [{
                        data: 'user'
                    },
                    {
                        data: 'number'
                    },

                    {
                        data: 'expiration_date'
                    },

                ],
            });

        });
        $(document).ready(function() {
            leaves_table = $('#leave_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']) }}",

                },
                dom: 'lrtip',
                lengthMenu: [5, 10, 25, 50],
                pageLength: 5,
                columns: [{
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'status',
                        name: 'essentials_leaves.status'
                    },
                ],
            });
        });
    </script>
@endsection
