@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">

                <div class="col-md-3  wow fadeIn" data-wow-delay="0.25s" style="visibility : hidden;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.number_employees_staff') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-0">0</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3  wow fadeIn" data-wow-delay="0.20s" style="visibility : hidden;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.number_employees') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-1">0</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-md-3  wow fadeIn" data-wow-delay="0.15s" style="visibility : hidden;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.number_workers') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-2">0</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3  wow fadeIn" data-wow-delay="0.1s" style="visibility : hidden;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.number_managers') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-3">0</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
            <br>

            {{-- 
            <div class="row widget-statistic">
        

            </div> --}}

            <div class="row  wow fadeIn" data-wow-delay="0.25s" style="visibility : hidden;">



                <div class="col-md-6 custom_table">
                    <canvas id="leaveStatusChart"></canvas>
                    <canvas id="contractStatusChart"></canvas>
                </div>



                <div class="col-md-5 custom_table">
                    @component('components.widget', [
                        'class' => 'box-primary',
                        'title' => __('essentials::lang.number_employees_staff'),
                    ])
                        {!! $chart->container() !!}
                    @endcomponent
                </div>



            </div>
            <div class="row  wow fadeIn" data-wow-delay="0.25s" style="visibility : hidden;">
                <div class="col-md-11 custom_table">
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


            </div>
            <div class="row  wow fadeIn" data-wow-delay="0.25s" style="visibility : hidden;">
                <div class="col-md-6 custom_table">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {!! $chart->script() !!}



          <script type="text/javascript">
         var wow = new WOW({
        callback: function (box) {
            if (box.classList.contains('fadeIn')) {
                box.style.visibility = 'visible';
            }
        }
    });

    wow.init();
        $(document).ready(function () {
            // Define an array of counters
            var counters = [
                {{ $num_employee_staff }},
                {{ $num_employees }},
                {{ $num_workers }},
                {{ $num_managers }}
                // Add more counter values if needed
            ];
    
            // Iterate through each counter
            $.each(counters, function (index, value) {
                var counterElement = $('#counter-' + index);
    
                $({ count: 0 }).animate({
                    count: value
                }, {
                    duration: 2000,
                    step: function () {
                        counterElement.text(Math.floor(this.count));
                    },
                    complete: function () {
                        counterElement.text(this.count);
                    }
                });
            });
        });
    </script>

    <script>
        function fetchLeaveStatusData() {

            $.ajax({
                url: '{{ route('leaveStatusData') }}',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    updateLeaveStatusChart(response);
                },
                error: function(error) {
                    console.error('Error fetching leave status data:', error);
                }
            });
        }


        function updateLeaveStatusChart(data) {
            var leaveStatusCanvas = document.getElementById('leaveStatusChart').getContext('2d');

            var translatedLabels = data.labels.map(function(label) {
                return label;
            });

            var leaveStatusChart = new Chart(leaveStatusCanvas, {
                type: 'bar',
                data: {
                    labels: translatedLabels,
                    datasets: [{
                        label: '{{ __('essentials::lang.leaves_status') }}',
                        data: data.values,
                        backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384'],
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    },
                }
            });
        }

        // Rest of your code remains unchanged...



        $(document).ready(function() {
            fetchLeaveStatusData();
        });
    </script>


    <script>
        function fetchContractStatusData() {
            $.ajax({
                url: '{{ route('contractStatusData') }}',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    updateContractStatusChart(response);
                },
                error: function(error) {
                    console.error('Error fetching contract status data:', error);
                }
            });
        }

        function updateContractStatusChart(data) {
            var contractStatusCanvas = document.getElementById('contractStatusChart').getContext('2d');

            var contractStatusChart = new Chart(contractStatusCanvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '{{ __('essentials::lang.contract_status') }}',
                        data: data.values,
                        backgroundColor: ['#FF6384', '#36A2EB'],
                    }]
                },
                options: {
                    responsive: true,
                }
            });
        }

        // Document ready function to fetch contract status data on page load
        $(document).ready(function() {
            fetchContractStatusData();
        });
    </script>



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
