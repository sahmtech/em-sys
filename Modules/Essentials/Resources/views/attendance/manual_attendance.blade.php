@extends('layouts.app')
@section('title', __('essentials::lang.crud_all_manual_attendance'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.crud_all_manual_attendance')
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @if (session('notification') || !empty($notification))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @if (!empty($notification['msg']))
                            {{ $notification['msg'] }}
                        @elseif(session('notification.msg'))
                            {{ session('notification.msg') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">

                    <div class="tab-content">

                        <div id="attendance_tab">

                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <form id="end_manual_attendance" action="{{ route('end_manual_attendance') }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    <form id="add_manual_attendance" action="{{ route('add_manual_attendance') }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    <!-- Button that triggers the form submission -->
                                    <button type="button"
                                        class="btn btn-app bg-blue 
                                        @if (!empty($clock_in)) hide @endif"
                                        data-type="clock_in"
                                        onclick="document.getElementById('add_manual_attendance').submit();">
                                        <i class="fas fa-arrow-circle-down"></i> @lang('essentials::lang.clock_in')
                                    </button>
                                    &nbsp;&nbsp;&nbsp;
                                    <button type="button"
                                        class="btn btn-app bg-yellow 
                                        @if (empty($clock_in)) hide @endif
                                    "
                                        data-type="clock_out"
                                        onclick="document.getElementById('end_manual_attendance').submit();">
                                        <i class="fas fa-hourglass-half fa-spin"></i> @lang('essentials::lang.clock_out')
                                    </button>
                                </div>

                            </div>

                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="attendance_table"
                                    style="width: 100%;">
                                    <thead>
                                        <tr>

                                            <th>@lang('essentials::lang.employee')</th>
                                            <th>@lang('essentials::lang.clock_in')</th>
                                            <th>@lang('essentials::lang.clock_out')</th>
                                            <th>@lang('lang_v1.date')</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>


@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            attendance_table = $('#attendance_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'manual_attendance']) }}",
                    "data": function(d) {

                    }
                },
                columns: [{
                        data: 'user',
                        name: 'user'
                    },

                    {
                        data: 'clock_in',
                        name: 'clock_in',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'clock_out',
                        name: 'clock_out',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'clock_in_time'
                    },

                ],
            });
        });
    </script>
@endsection
