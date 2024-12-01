@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')
    <section class="content-header">
        {{-- <h1>{{ $timesheetGroup->name }}</h1> --}}
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border text-center">
                @if (!empty(Session::get('business.logo')))
                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}" alt="Logo"
                        style="width: auto; max-height: 50px;">
                @endif
                <h3 class="box-title">{{ Session::get('business.name') ?? '' }}</h3>
                <br>
                <small>{!! Session::get('business.business_address') ?? '' !!}</small>
                <br>
                <h4>
                    {{-- {{ $timesheetGroup->name }} --}}
                </h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width:100px;">@lang('essentials::lang.name')</th>
                                <th style="width:75px;">@lang('essentials::lang.nationality')</th>

                                @if ($user_type == 'worker')
                                    <th style="width:100px;">@lang('essentials::lang.residence_permit')</th>
                                    <th style="width:75px;">@lang('essentials::lang.company')</th>
                                    <th style="width:75px;">@lang('essentials::lang.project_name')</th>
                                    <th style="width:75px;">@lang('essentials::lang.issuing_location')</th>
                                @endif
                                @if ($user_type != 'worker')
                                    <th style="width:100px;">@lang('essentials::lang.identity_card_number')</th>
                                @endif
                                @if ($user_type != 'remote_employee' && $user_type != 'worker')
                                    <th style="width:75px;">@lang('essentials::lang.profession')</th>
                                @endif
                                {{-- city --}}
                                <th style="width:75px;">@lang('essentials::lang.issuing_location')</th>


                                <th style="width:75px;">@lang('essentials::lang.work_days')</th>
                                <th style="width:75px;">@lang('essentials::lang.salary')</th>
                                <th style="width:75px;">@lang('essentials::lang.housing_allowance')</th>
                                <th style="width:75px;">@lang('essentials::lang.transportation_allowance')</th>
                                <th style="width:75px;">@lang('essentials::lang.other_allowance')</th>
                                <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total')</th>
                                <th style="width:75px;">@lang('essentials::lang.violations')</th>
                                <th style="width:75px;">@lang('essentials::lang.absence_days')</th>
                                <th style="width:75px;">@lang('essentials::lang.late_hours_numbers')</th>
                                <th style="width:75px;">@lang('essentials::lang.absence_deduction')</th>
                                <th style="width:75px;">@lang('essentials::lang.late_deduction')</th>
                                <th style="width:75px;">@lang('essentials::lang.other_deductions')</th>
                                <th style="width:75px;">@lang('essentials::lang.loan')</th>
                                <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total_deduction')</th>
                                @if ($user_type != 'remote_employee')
                                    <th style="width:75px;">@lang('essentials::lang.over_time_hours')</th>
                                    <th style="width:75px;">@lang('essentials::lang.over_time_hours_addition')</th>
                                    <th style="width:75px;">@lang('essentials::lang.additional_addition')</th>
                                    @if ($user_type != 'worker')
                                        <th style="width:75px;">@lang('essentials::lang.other_additions')</th>
                                    @endif
                                    <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total_additions')</th>
                                @endif
                                <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.final_salary')</th>
                                @if ($user_type != 'remote_employee')
                                    <th style="width:75px;">@lang('essentials::lang.payment_method')</th>
                                @endif
                                <th style="width:75px;">@lang('essentials::lang.notes')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $index => $payroll)
                                <tr class="payroll_row">
                                    <td name="id">{{ $payroll['id'] }}</td>
                                    <td name="name">{{ $payroll['name'] }}</td>
                                    <td name="nationality">{{ $payroll['nationality'] }}</td>
                                    <td name="identity_card_number">{{ $payroll['identity_card_number'] }}</td>
                                    @if ($user_type == 'worker')
                                        <td name="company">{{ $payroll['company'] }}</td>
                                        <td name="project_name">{{ $payroll['project_name'] }}</td>
                                        <td name="region">{{ $payroll['region'] }}</td>
                                    @endif
                                    @if ($user_type != 'remote_employee' && $user_type != 'worker')
                                        <td name="profession">{{ $payroll['profession'] ?? '' }}</td>
                                    @endif
                                    <td name="work_days">{{ $payroll['work_days'] }}</td>
                                    <td name="salary">
                                        {{ is_numeric($payroll['salary']) ? number_format(floatval($payroll['salary']), 2) : $payroll['salary'] }}
                                    </td>
                                    <td name="housing_allowance">
                                        {{ is_numeric($payroll['housing_allowance']) ? number_format(floatval($payroll['housing_allowance']), 2) : $payroll['housing_allowance'] }}
                                    </td>
                                    <td name="transportation_allowance">
                                        {{ is_numeric($payroll['transportation_allowance']) ? number_format(floatval($payroll['transportation_allowance']), 2) : $payroll['transportation_allowance'] }}
                                    </td>
                                    <td name="other_allowance">
                                        {{ is_numeric($payroll['other_allowance']) ? number_format(floatval($payroll['other_allowance']), 2) : $payroll['other_allowance'] }}
                                    </td>
                                    <td style="background-color: rgb(185, 182, 182);" name="total">
                                        {{ is_numeric($payroll['total']) ? number_format(floatval($payroll['total']), 2) : $payroll['total'] }}
                                    </td>
                                    <td name="violations">{{ $payroll['violations'] }}</td>
                                    <td name="absence">{{ $payroll['absence'] }}</td>
                                    <td name="late">{{ $payroll['late'] }}</td>
                                    <td name="absence_deduction">
                                        {{ is_numeric($payroll['absence_deduction']) ? number_format(floatval($payroll['absence_deduction']), 2) : $payroll['absence_deduction'] }}
                                    </td>
                                    <td name="late_deduction">
                                        {{ is_numeric($payroll['late_deduction']) ? number_format(floatval($payroll['late_deduction']), 2) : $payroll['late_deduction'] }}
                                    </td>
                                    <td name="other_deductions">
                                        {{ is_numeric($payroll['other_deductions']) ? number_format(floatval($payroll['other_deductions']), 2) : $payroll['other_deductions'] }}
                                    </td>
                                    <td name="loan">{{ $payroll['loan'] }}</td>
                                    <td style="background-color: rgb(185, 182, 182);" name="total_deduction">
                                        {{ is_numeric($payroll['total_deduction']) ? number_format(floatval($payroll['total_deduction']), 2) : $payroll['total_deduction'] }}
                                    </td>
                                    @if ($user_type != 'remote_employee')
                                        <td name="over_time_hours">{{ $payroll['over_time_hours'] }}</td>
                                        <td name="over_time_hours_addition">{{ $payroll['over_time_hours_addition'] }}</td>
                                        <td name="additional_addition">{{ $payroll['additional_addition'] }}</td>
                                        @if ($user_type != 'worker')
                                            <td name="other_additions">{{ $payroll['other_additions'] ?? '' }}</td>
                                        @endif
                                        <td style="background-color: rgb(185, 182, 182);" name="total_additions">
                                            {{ is_numeric($payroll['total_additions']) ? number_format(floatval($payroll['total_additions']), 2) : $payroll['total_additions'] }}
                                        </td>
                                    @endif
                                    <td style="background-color: rgb(185, 182, 182);" name="final_salary">
                                        {{ is_numeric($payroll['final_salary']) ? number_format(floatval($payroll['final_salary']), 2) : $payroll['final_salary'] }}
                                    </td>
                                    @if ($user_type != 'remote_employee')
                                        <td name="payment_method">{{ $payroll['payment_method'] }}</td>
                                    @endif
                                    <td name="notes">{{ $payroll['notes'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer text-right">
                <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i>
                    @lang('messages.print')</button>
                <a href="{{ url()->previous() }}" class="btn btn-default">@lang('essentials::lang.back')</a>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the first user from payrolls array
            const payrolls = @json($payrolls);
            const userType = payrolls.length > 0 ? payrolls[0]['user_type'] : null;

            // Define which columns should be hidden for non-workers and shown for workers
            const columnsForWorkers = [
                '.column-project',
                '.column-monthly_cost',
            ];

            // Hide/show columns based on user type
            if (userType !== 'worker') {
                columnsForWorkers.forEach(columnClass => {
                    document.querySelectorAll(columnClass).forEach(element => {
                        element.style.display = 'none';
                    });
                });
            } else {
                columnsForWorkers.forEach(columnClass => {
                    document.querySelectorAll(columnClass).forEach(element => {
                        element.style.display = '';
                    });
                });
            }
        });
    </script>
@endsection

@section('css')
    <style>
        .content-header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .box {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .box-header {
            background-color: #f7f7f7;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .box-title {
            font-size: 20px;
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        @media print {

            .box-header,
            .box-footer,
            .btn {
                display: none;
            }

            .content {
                margin: 0;
                padding: 0;
                border: none;
            }

            .box {
                box-shadow: none;
                border: none;
            }

            table {
                width: 100%;
                table-layout: fixed;
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 2px;
            }
        }
    </style>
@endsection
