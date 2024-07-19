@if ($timesheets->isEmpty())
    <p>@lang('essentials::lang.no_timesheets')</p>
@else
    @foreach ($timesheets as $timesheet)
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>@lang('essentials::lang.group_date')</th>
                        <td>{{ $timesheet->group_date }}</td>
                    </tr>
                    <tr>
                        <th>@lang('essentials::lang.work_days')</th>
                        <td>{{ $timesheet->work_days }}</td>
                    </tr>
                    <tr>
                        <th>@lang('essentials::lang.absence_days')</th>
                        <td>{{ $timesheet->absence_days }}</td>
                    </tr>
                    <tr>
                        <th>@lang('essentials::lang.over_time_hours')</th>
                        <td>{{ $timesheet->over_time_hours }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.monthly_cost')</th>
                        <td>{{ number_format($timesheet->monthly_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.wd')</th>
                        <td>{{ $timesheet->work_days }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.absence_day')</th>
                        <td>{{ $timesheet->absence_days }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.absence_amount')</th>
                        <td>{{ number_format($timesheet->absence_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.over_time_h')</th>
                        <td>{{ $timesheet->over_time_hours }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.over_time')</th>
                        <td>{{ number_format($timesheet->over_time_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.other_deduction')</th>
                        <td>{{ number_format($timesheet->other_deduction, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.other_addition')</th>
                        <td>{{ number_format($timesheet->other_addition, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.cost2')</th>
                        <td>{{ number_format($timesheet->cost_2, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.invoice_value')</th>
                        <td>{{ number_format($timesheet->invoice_value, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.vat')</th>
                        <td>{{ number_format($timesheet->vat, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.total')</th>
                        <td>{{ number_format($timesheet->total, 2) }}</td>
                    </tr>
                    {{-- <tr>
                        <th>@lang('worker.sponser')</th>
                        <td>{{ $timesheet->sponser }}</td>
                    </tr> --}}
                    <tr>
                        <th>@lang('worker.basic')</th>
                        <td>{{ number_format($timesheet->basic, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.housing')</th>
                        <td>{{ number_format($timesheet->housing, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.transport')</th>
                        <td>{{ number_format($timesheet->transport, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.other_allowances')</th>
                        <td>{{ number_format($timesheet->other_allowances, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.total_salary')</th>
                        <td>{{ number_format($timesheet->total_salary, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.deductions')</th>
                        <td>{{ number_format($timesheet->deductions, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.additions')</th>
                        <td>{{ number_format($timesheet->additions, 2) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('worker.final_salary')</th>
                        <td>{{ number_format($timesheet->final_salary, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
@endif
