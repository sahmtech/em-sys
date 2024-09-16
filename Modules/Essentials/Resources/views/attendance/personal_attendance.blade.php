@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">
                <div class="col-md-4">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">@lang('lang_v1.attendance_days')</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $res['attended'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">@lang('lang_v1.late_days')</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $res['late'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">@lang('lang_v1.absent_days')</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff">{{ $res['absent'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
        </div>

    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <!-- Calendar spans two columns -->
                <div class="col-md-8">
                    <!-- Calendar -->
                    <div id="calendar"></div>
                </div>
                <!-- Info Box spans one column -->
                <div class="col-md-4">
                    <!-- Info Box -->
                    <div class="info-box">
                        <!-- Day and Date -->
                        <div class="info-row">
                            <span id="selected-date">{{ \Carbon\Carbon::now()->format('M d Y') }}</span>
                            <span id="selected-day">{{ \Carbon\Carbon::now()->format('l') }}</span> <!-- Day name -->
                            <!-- Date without day name -->
                        </div>
                        <!-- Status -->
                        <div class="info-row">
                            <span>@lang('lang_v1.status'): <span id="attendance-status"></span></span>
                        </div>
                        <!-- Start Time -->
                        <div class="info-row">
                            <span>@lang('lang_v1.start_time'): <span id="attendance-start-time"></span></span>
                        </div>
                        <!-- End Time -->
                        <div class="info-row">
                            <span>@lang('lang_v1.end_time'): <span id="attendance-end-time"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css" rel="stylesheet" />

    <style>
        /* Custom CSS to enhance calendar appearance */
        #calendar {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background-color: #f9f9f9;
            border-radius: 15px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Info Box Styling */
        .info-box {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);

            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            font-size: 1.2rem;
        }

        .info-row {
            display: flex;
            direction: rtl;
            font-size: 1.5rem;
        }

        .fc-toolbar-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #007bff;
        }

        .fc-daygrid-day-frame {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50px;
        }

        .fc-daygrid-day {
            background-color: #ffffff;
            border: 1px solid #ddd;
            height: 50px;
            transition: all 0.3s ease;
        }

        .fc-daygrid-day:hover {
            background-color: #f0f8ff;
            cursor: pointer;
        }

        .fc-day-today {
            background-color: #fffbcc !important;
            border-color: #ffcc00;
        }

        .fc-col-header-cell {
            background-color: #007bff;
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
            text-align: center;
        }

        .fc-col-header-cell-cushion {
            color: #fff;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .fc-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .fc-button:hover {
            background-color: #0056b3;
        }

        #selected-date {
            margin-left: 10px;
        }
    </style>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var dayDisplay = document.getElementById('selected-day'); // The day name display element
            var dateDisplay = document.getElementById('selected-date'); // The date display element
            var attendanceStatus = document.getElementById('attendance-status'); // To display status
            var startTimeDisplay = document.getElementById('attendance-start-time'); // To display start time
            var endTimeDisplay = document.getElementById('attendance-end-time'); // To display end time

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                dateClick: function(info) {
                    var selectedDate = new Date(info.dateStr);
                    var year = selectedDate.getFullYear();
                    var month = selectedDate.getMonth() + 1; // getMonth is 0-based
                    var day = selectedDate.getDate();
                    var dayName = selectedDate.toLocaleString('en-US', {
                        weekday: 'long'
                    });

                    // Format the date without the day name
                    var formattedDate = selectedDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    // Update the day and date in the info box
                    dayDisplay.textContent = dayName;
                    dateDisplay.textContent = formattedDate;

                    // Check if the day is already available in the "res" variable (local data)
                    var foundDay = @json($res['days']).find(function(dayItem) {
                        return dayItem.number_in_month === day && dayItem.month === month &&
                            dayItem.year === year;
                    });

                    if (foundDay) {
                        // Display local data if available
                        attendanceStatus.textContent = foundDay.status !== null ? foundDay.status : '';
                        startTimeDisplay.textContent = foundDay.start_time || '';
                        endTimeDisplay.textContent = foundDay.end_time || '';
                    } else {
                        // Make AJAX call if day not found
                        $.ajax({
                            url: '/user/personalAttendance/' + year + '/' + month,
                            type: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    var data = response.data.days.find(function(dayItem) {
                                        return dayItem.number_in_month === day &&
                                            dayItem.month === month && dayItem
                                            .year === year;
                                    });

                                    if (data) {
                                        attendanceStatus.textContent = data.status !==
                                            null ? data.status : '';
                                        startTimeDisplay.textContent = data.start_time ||
                                            '';
                                        endTimeDisplay.textContent = data.end_time || '';
                                    } else {
                                        attendanceStatus.textContent = '';
                                        startTimeDisplay.textContent = '';
                                        endTimeDisplay.textContent = '';
                                    }
                                }
                            },
                            error: function(error) {
                                console.error(error);
                                attendanceStatus.textContent = 'Error';
                                startTimeDisplay.textContent = '';
                                endTimeDisplay.textContent = '';
                            }
                        });
                    }
                }
            });

            calendar.render();
        });
    </script>
@endsection
