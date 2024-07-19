<!-- resources/views/partials/salaries.blade.php -->
@if ($salaries->isEmpty())
    <p>@lang('followup::lang.no_salaries')</p>
@else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>@lang('followup::lang.timesheet_group_id')</th>
                <th>@lang('followup::lang.monthly_cost')</th>
                <th>@lang('followup::lang.work_days')</th>
                <th>@lang('followup::lang.absence_days')</th>
                <th>@lang('followup::lang.absence_amount')</th>
                <th>@lang('followup::lang.over_time_hours')</th>
                <th>@lang('followup::lang.over_time_amount')</th>
                <th>@lang('followup::lang.other_deduction')</th>
                <th>@lang('followup::lang.other_addition')</th>
                <th>@lang('followup::lang.cost_2')</th>
                <th>@lang('followup::lang.invoice_value')</th>
                <th>@lang('followup::lang.vat')</th>
                <th>@lang('followup::lang.total')</th>
                <th>@lang('followup::lang.project_id')</th>
                <th>@lang('followup::lang.basic')</th>
                <th>@lang('followup::lang.housing')</th>
                <th>@lang('followup::lang.transport')</th>
                <th>@lang('followup::lang.other_allowances')</th>
                <th>@lang('followup::lang.total_salary')</th>
                <th>@lang('followup::lang.deductions')</th>
                <th>@lang('followup::lang.additions')</th>
                <th>@lang('followup::lang.final_salary')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salaries as $salary)
                <tr>
                    <td>{{ $salary->timesheet_group_id }}</td>
                    <td>{{ $salary->monthly_cost }}</td>
                    <td>{{ $salary->work_days }}</td>
                    <td>{{ $salary->absence_days }}</td>
                    <td>{{ $salary->absence_amount }}</td>
                    <td>{{ $salary->over_time_hours }}</td>
                    <td>{{ $salary->over_time_amount }}</td>
                    <td>{{ $salary->other_deduction }}</td>
                    <td>{{ $salary->other_addition }}</td>
                    <td>{{ $salary->cost_2 }}</td>
                    <td>{{ $salary->invoice_value }}</td>
                    <td>{{ $salary->vat }}</td>
                    <td>{{ $salary->total }}</td>
                    <td>{{ $salary->project_id }}</td>
                    <td>{{ $salary->basic }}</td>
                    <td>{{ $salary->housing }}</td>
                    <td>{{ $salary->transport }}</td>
                    <td>{{ $salary->other_allowances }}</td>
                    <td>{{ $salary->total_salary }}</td>
                    <td>{{ $salary->deductions }}</td>
                    <td>{{ $salary->additions }}</td>
                    <td>{{ $salary->final_salary }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
