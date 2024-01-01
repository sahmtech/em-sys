@extends('layouts.custom_layouts.custom_home_layout')
@section('title', __('home.home'))

@section('content')
    <section class="content"
        style="   
    background: url('{{ asset('img/emdadat-bg-img.jpg') }}');
    background:#b4b4b4, url('{{ asset('img/emdadat-bg-img.jpg') }}');
    background-size: cover;">
        <div class="custom_column">
            {{-- Single Column --}}
            <div class="col-md-12">

                {{-- <div class="row">


                    {{-- <div class="col-md-5">
                        <div class="custom_table_card">

                            <h4> @lang('followup::lang.requests')</h4>
                            <div class="table-responsive custom_table2">
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
                    <div class="col-md-5">
                        <div class="custom_table_card">
                            <h5>@lang('essentials::lang.todo_list')</h5>
                            <div class="table-responsive custom_table2">
                                <table class="table table-bordered table-striped" id="task_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('lang_v1.added_on')</th>
                                            <th> @lang('essentials::lang.task_id')</th>
                                            <th class="col-md-2"> @lang('essentials::lang.task')</th>
                                            <th> @lang('sale.status')</th>

                                            <th> @lang('essentials::lang.estimated_hours')</th>
                                            <th> @lang('essentials::lang.assigned_by')</th>
                                            <th> @lang('essentials::lang.assigned_to')</th>

                                        </tr>
                                    </thead>
                                </table>
                            </div>

                        </div>
                    </div> --}}

                <div class="col-md-10">
                    <div class="card-grid">
                     
                        @foreach ($cards as $card)
                            <div class="col-md-3">
                                <div class="card">
                                    <a href="{{ $card['link'] }}" class="card-link">
                                        <div class="card-content">
                                            <h3>{{ $card['title'] }}</h3>
                                            <i class="fa fa-{{ $card['icon'] }}"></i>
                                        </div>
                                    </a>
                                </div>

                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card-content2" style=" margin-top: 17%;">
                            <div class="ribbon">
                                <span>الموظف المثالي</span>
                            </div>
                            <!-- Employee Profile Card Content -->
                            <img src="{{ asset('img/personAvatar.png') }}" alt="Employee Photo" class="employee-photo">
                            <div class="employee-info">
                                <h3>متعب الكبرى</h3>
                                <p>مدير إدارة التشغيل</p>

                            </div>
                          
                        @endforeach
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card-content2" style=" margin-top: 17%;">
                        <div class="ribbon">
                            <span>الموظف المثالي</span>
                        </div>
                        <!-- Employee Profile Card Content -->
                        <img src="{{ asset('img/personAvatar.png') }}" alt="Employee Photo" class="employee-photo">
                        <div class="employee-info">
                            <h3>متعب الكبرى</h3>
                            <p>مدير التشغيل</p>
                        </div>
                    </div>
                </div>

            </div>


            {{-- Second Item (Placeholder) --}}
            {{-- <div class="row">
                  
                </div> --}}

        </div>
        @if (count($cards) < 4)
            <div class="card-grid">
            </div>
            <div class="card-grid">
            </div>
            <div class="card-grid">
            </div>
        @endif
        </div>
    </section>
    <!-- Main content -->
    {{-- <section class="content content-custom no-print">

    </section> --}}
    <!-- /.content -->

@stop

@section('javascript')


    <script type="text/javascript">
     


        $(document).ready(function() {



            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                scrollCollapse: true,
                paging: false,
                info: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('my_requests') }}"
                },

                columns: [

                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'assigned_to'
                    },
                    {
                        data: 'type',
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
            task_table = $('#task_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                scrollCollapse: true,
                paging: false,
                info: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('my_todo') }}",

                },
                columnDefs: [{

                    orderable: false,
                    searchable: false,
                }, ],
                aaSorting: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'task_id',
                        name: 'task_id'
                    },
                    {
                        data: 'task',
                        name: 'task'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },

                    {
                        data: 'estimated_hours',
                        name: 'estimated_hours'
                    },
                    {
                        data: 'assigned_by'
                    },
                    {
                        data: 'users'
                    },

                ],
            });

        });
    </script>
@endsection
