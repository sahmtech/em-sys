@extends('layouts.app')
@section('title', __('essentials::lang.payroll'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.payroll_for')</span> {{ $date }}
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => route('payrolls.submit'),
                'method' => 'post',
                'id' => 'add_payroll_step1',
            ]) !!}
            {!! Form::hidden('payroll_group_name', strip_tags($group_name)) !!}
            {!! Form::hidden('timesheet_groups', json_encode($timesheet_groups)) !!}
            <div class="table-responsive2">
                <div style="margin-bottom: 10px;">
                    <div class="col-md-12">
                        <div class="col-md-1">
                            <input type="hidden" name="totals" id="totals">
                            <input type="hidden" name="ids" id="ids"> <!-- Example hidden input -->
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i>
                                @lang('essentials::lang.submit')
                            </button>
                        </div>
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('payroll_group_status', __('sale.status') . ':*') !!}

                            {!! Form::select(
                                'payroll_group_status',
                                ['draft' => __('essentials::lang.draft_payroll'), 'final' => __('essentials::lang.final_payroll')],
                                null,
                                [
                                    'class' => 'form-control select2 pull-right',
                                    'required',
                                    'style' => 'width: 80%;',
                                    'placeholder' => __('messages.please_select'),
                                ],
                            ) !!}

                        </div>
                        <div class="col-md-4">
                            <h4 style="  color: red;" class="total_payrolls" id="h4_total_payrolls"></h4>
                            {!! Form::hidden('total_payrolls', null, [
                                'id' => 'total_payrolls',
                            ]) !!}
                            {!! Form::hidden('transaction_date', $transaction_date) !!}

                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br>
                </div>
                <div class="custom-scrollbar"
                    style="overflow-x: auto; top: 60px; left: 0; right: 0; height: 20px; background: #f1f1f1;">
                    <div style="width: 2000px; height: 1px;"></div> <!-- Adjust width to be larger than the table's width -->
                </div>
                <div class="table-container" style="position: relative;">
                    <div class="table-responsive table-responsive2">
                        <table class="table table-bordered table-striped" id="workers_table_timesheet"
                            style="table-layout: fixed !important;">
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

                                    <th style="width:75px;">@lang('essentials::lang.work_days')</th>
                                    <th style="width:75px;">@lang('essentials::lang.salary')</th>
                                    <th style="width:75px;">@lang('essentials::lang.housing_allowance')</th>
                                    <th style="width:75px;">@lang('essentials::lang.transportation_allowance')</th>
                                    <th style="width:75px;">@lang('essentials::lang.other_allowance')</th>
                                    <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total')</th>
                                    <th style="width:75px;">@lang('essentials::lang.violations')</th>
                                    <th style="width:75px;">@lang('essentials::lang.absence_days')</th>
                                    <th style="background-color: rgb(255, 255, 255); width:75px;">@lang('essentials::lang.total_amount_of_absence')</th>

                                    <th style="width:75px;">@lang('essentials::lang.late_hours_numbers')</th>
                                    <th style="background-color: rgb(255, 255, 255); width:75px;">@lang('essentials::lang.total_amount_of_delay')</th>

                                    <th style="width:75px;">@lang('essentials::lang.other_deductions')</th>
                                    <th style="width:75px;">@lang('essentials::lang.loan')</th>
                                    <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total_deduction')</th>
                                    @if ($user_type != 'remote_employee')
                                        <th style="width:75px;">@lang('essentials::lang.over_time_hours')</th>
                                        <th style="background-color: rgb(255, 255, 255); width:75px;">@lang('essentials::lang.total_amount_over_time_hours')</th>

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
                                        <td name="id">{{ $payroll['id'] }}
                                            {!! Form::hidden('payrolls[' . $index . '][id]', $payroll['id'], [
                                                'data-index' => $index,
                                                'data-field' => 'id',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="name"><a>{{ $payroll['name'] }}</a>
                                            {!! Form::hidden('payrolls[' . $index . '][name]', $payroll['name'], [
                                                'data-index' => $index,
                                                'data-field' => 'name',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="nationality">{{ $payroll['nationality'] }}
                                            {!! Form::hidden('payrolls[' . $index . '][nationality]', $payroll['nationality'], [
                                                'data-index' => $index,
                                                'data-field' => 'nationality',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="identity_card_number"><a>{{ $payroll['identity_card_number'] }}</a>
                                            {!! Form::hidden('payrolls[' . $index . '][identity_card_number]', $payroll['identity_card_number'], [
                                                'data-index' => $index,
                                                'data-field' => 'identity_card_number',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        @if ($user_type == 'worker')
                                            <td name="company">{{ $payroll['company'] }}
                                                {!! Form::hidden('payrolls[' . $index . '][company]', $payroll['company'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'company',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                            <td name="project_name">{{ $payroll['project_name'] }}
                                                {!! Form::hidden('payrolls[' . $index . '][project_name]', $payroll['project_name'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'project_name',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>

                                            <td name="region">{{ $payroll['region'] }}
                                                {!! Form::hidden('payrolls[' . $index . '][region]', $payroll['region'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'region',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                        @endif
                                        @if ($user_type != 'remote_employee' && $user_type != 'worker')
                                            <td name="profession">{{ $payroll['profession'] }}
                                                {!! Form::hidden('payrolls[' . $index . '][profession]', $payroll['profession'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'profession',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                        @endif
                                        <td class="editable" name="work_days"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="work_days">{{ $payroll['work_days'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][work_days]', $payroll['work_days'], [
                                                'data-index' => $index,
                                                'data-field' => 'work_days',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="salary"><span data-index="{{ $index }}"
                                                data-field="salary">{{ $payroll['salary'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][salary]', $payroll['salary'], [
                                                'data-index' => $index,
                                                'data-field' => 'salary',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="housing_allowance"><span data-index="{{ $index }}"
                                                data-field="housing_allowance">{{ $payroll['housing_allowance'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][housing_allowance]', $payroll['housing_allowance'], [
                                                'data-index' => $index,
                                                'data-field' => 'housing_allowance',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="transportation_allowance"><span data-index="{{ $index }}"
                                                data-field="transportation_allowance">{{ $payroll['transportation_allowance'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][transportation_allowance]', $payroll['transportation_allowance'], [
                                                'data-index' => $index,
                                                'data-field' => 'transportation_allowance',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="other_allowance"><span data-index="{{ $index }}"
                                                data-field="other_allowance">{{ $payroll['other_allowance'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][other_allowance]', $payroll['other_allowance'], [
                                                'data-index' => $index,
                                                'data-field' => 'other_allowance',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td style="background-color: rgb(185, 182, 182);" name="total"> <span
                                                data-index="{{ $index }}"
                                                data-field="total">{{ $payroll['total'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][total]', $payroll['total'], [
                                                'data-index' => $index,
                                                'data-field' => 'total',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td class="editable" name="violations"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="violations">{{ $payroll['violations'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][violations]', $payroll['violations'], [
                                                'data-index' => $index,
                                                'data-field' => 'violations',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td class="editable" name="absence"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="absence">{{ $payroll['absence'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][absence]', $payroll['absence'], [
                                                'data-index' => $index,
                                                'data-field' => 'absence',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                            {!! Form::hidden('payrolls[' . $index . '][absence_deduction]', $payroll['absence_deduction'], [
                                                'data-index' => $index,
                                                'data-field' => 'absence_deduction',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="total_amount_absence"><span data-index="{{ $index }}"
                                                data-field="total_amount_absence">{{ $payroll['total_amount_absence'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][total_amount_absence]', $payroll['total_amount_absence'], [
                                                'data-index' => $index,
                                                'data-field' => 'total_amount_absence',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td class="editable" name="late"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="late">{{ $payroll['late'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][late]', $payroll['late'], [
                                                'data-index' => $index,
                                                'data-field' => 'late',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                            {!! Form::hidden('payrolls[' . $index . '][late_deduction]', $payroll['late_deduction'], [
                                                'data-index' => $index,
                                                'data-field' => 'late_deduction',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td name="total_amount_of_delay"><span data-index="{{ $index }}"
                                                data-field="total_amount_of_delay">{{ $payroll['total_amount_of_delay'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][total_amount_of_delay]', $payroll['total_amount_of_delay'], [
                                                'data-index' => $index,
                                                'data-field' => 'total_amount_of_delay',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td class="editable" name="other_deductions"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="other_deductions">{{ $payroll['other_deductions'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][other_deductions]', $payroll['other_deductions'], [
                                                'data-index' => $index,
                                                'data-field' => 'other_deductions',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td class="editable" name="loan"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="loan">{{ $payroll['loan'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][loan]', $payroll['loan'], [
                                                'data-index' => $index,
                                                'data-field' => 'loan',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        <td style="background-color: rgb(185, 182, 182);" name="total_deduction"><span
                                                data-index="{{ $index }}"
                                                data-field="total_deduction">{{ $payroll['total_deduction'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][total_deduction]', $payroll['total_deduction'], [
                                                'data-index' => $index,
                                                'data-field' => 'total_deduction',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        @if ($user_type != 'remote_employee')
                                            <td class="editable" name="over_time_hours"> <span contenteditable="true"
                                                    data-index="{{ $index }}"
                                                    data-field="over_time_hours">{{ $payroll['over_time_hours'] }}</span>
                                                {!! Form::hidden('payrolls[' . $index . '][over_time_hours]', $payroll['over_time_hours'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'over_time_hours',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                                {!! Form::hidden('payrolls[' . $index . '][over_time_hours_addition]', $payroll['over_time_hours_addition'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'over_time_hours_addition',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                            <td name="total_amount_over_time_hours"><span data-index="{{ $index }}"
                                                    data-field="total_amount_over_time_hours">{{ $payroll['total_amount_over_time_hours'] }}</span>
                                                {!! Form::hidden(
                                                    'payrolls[' . $index . '][total_amount_over_time_hours]',
                                                    $payroll['total_amount_over_time_hours'],
                                                    [
                                                        'data-index' => $index,
                                                        'data-field' => 'total_amount_over_time_hours',
                                                        'class' => 'form-hidden',
                                                    ],
                                                ) !!}
                                            </td>



                                            <td class="editable" name="additional_addition"> <span contenteditable="true"
                                                    data-index="{{ $index }}"
                                                    data-field="additional_addition">{{ $payroll['additional_addition'] }}</span>
                                                {!! Form::hidden('payrolls[' . $index . '][additional_addition]', $payroll['additional_addition'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'additional_addition',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                            @if ($user_type != 'worker')
                                                <td class="editable" name="other_additions"> <span contenteditable="true"
                                                        data-index="{{ $index }}"
                                                        data-field="other_additions">{{ $payroll['other_additions'] }}</span>
                                                    {!! Form::hidden('payrolls[' . $index . '][other_additions]', $payroll['other_additions'], [
                                                        'data-index' => $index,
                                                        'data-field' => 'other_additions',
                                                        'class' => 'form-hidden',
                                                    ]) !!}
                                                </td>
                                            @endif
                                            <td style="background-color: rgb(185, 182, 182);" name="total_additions"><span
                                                    data-index="{{ $index }}"
                                                    data-field="total_additions">{{ $payroll['total_additions'] }}</span>
                                                {!! Form::hidden('payrolls[' . $index . '][total_additions]', $payroll['total_additions'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'total_additions',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                        @endif
                                        <td style="background-color: rgb(185, 182, 182);" name="final_salary"><span
                                                data-index="{{ $index }}"
                                                data-field="final_salary">{{ $payroll['final_salary'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][final_salary]', $payroll['final_salary'], [
                                                'data-index' => $index,
                                                'data-field' => 'final_salary',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        @if ($user_type != 'remote_employee')
                                            <td class="editable" name="payment_method"> <span contenteditable="true"
                                                    data-index="{{ $index }}"
                                                    data-field="payment_method">{{ $payroll['payment_method'] }}</span>
                                                {!! Form::hidden('payrolls[' . $index . '][payment_method]', $payroll['payment_method'], [
                                                    'data-index' => $index,
                                                    'data-field' => 'payment_method',
                                                    'class' => 'form-hidden',
                                                ]) !!}
                                            </td>
                                        @endif
                                        <td class="editable" name="notes"> <span contenteditable="true"
                                                data-index="{{ $index }}"
                                                data-field="notes">{{ $payroll['notes'] }}</span>
                                            {!! Form::hidden('payrolls[' . $index . '][notes]', $payroll['notes'], [
                                                'data-index' => $index,
                                                'data-field' => 'notes',
                                                'class' => 'form-hidden',
                                            ]) !!}
                                        </td>
                                        {!! Form::hidden('payrolls[' . $index . '][timesheet_user_id]', $payroll['timesheet_user_id'] ?? null, [
                                            'data-index' => $index,
                                            'data-field' => 'timesheet_user_id',
                                            'class' => 'form-hidden',
                                        ]) !!}

                                        {{-- 
                                        {!! Form::hidden('payrolls[' . $index . '][timesheet_group_id]', $payroll['timesheet_group_id'] ?? null, [
                                            'data-index' => $index,
                                            'data-field' => 'timesheet_group_id',
                                            'class' => 'form-hidden',
                                        ]) !!} --}}
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <td colspan="2">
                                        @lang('essentials::lang.the_total')
                                    </td>
                                    @if ($user_type != 'remote_employee' && $user_type != 'worker')
                                        <td>
                                        </td>
                                    @endif
                                    @if ($user_type == 'worker')
                                        <td>
                                        </td>
                                        <td>
                                        </td>
                                    @endif
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                    </td>


                                    <td name="the_total_salary">
                                    </td>
                                    <td name="the_total_housing_allowance">
                                    </td>
                                    <td name="the_total_transportation_allowance">
                                    </td>
                                    <td name="the_total_other_allowance">
                                    </td>
                                    <td name="the_total_total">
                                    </td>
                                    <td name="the_total_violations">
                                    </td>
                                    <td name="the_total_absence">
                                    </td>
                                    <td name="the_total_late">
                                    </td>
                                    <td name="the_total_other_deductions">
                                    </td>
                                    <td name="the_total_loan">
                                    </td>
                                    <td name="the_total_total_deduction">
                                    </td>
                                    @if ($user_type != 'remote_employee')
                                        <td name="the_total_over_time_hours">
                                        </td>
                                        <td name="the_total_additional_addition">
                                        </td>
                                        @if ($user_type != 'worker')
                                            <td name="the_total_other_additions">
                                            </td>
                                        @endif
                                        <td name="the_total_total_additions">
                                        </td>
                                    @endif
                                    <td style="  color: red;" name="the_total_final_salary">

                                    </td>
                                    @if ($user_type != 'remote_employee')
                                        <td>
                                        </td>
                                    @endif
                                    <td>
                                    </td>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        @endcomponent
        {{-- <div class="modal fade" id="worker-info" tabindex="-1" role="dialog" aria-labelledby="workerInfoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="workerInfoModalLabel">@lang('essentials::lang.personal_info')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('essentials::lang.close')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div style="margin-top: 20px;">
                            <p><strong>@lang('essentials::lang.full_name'):</strong> <span id="worker_full_name"></span></p>
                            <p><strong>@lang('essentials::lang.emp_number'):</strong> <span id="worker_emp_number"></span></p>
                            <p><strong>@lang('essentials::lang.contry_nationality'):</strong> <span id="nationality"></span></p>
                            <p><strong>@lang('essentials::lang.IBAN_number'):</strong> <span id="iban"></span></p>
                            <p><strong>@lang('essentials::lang.Basic_salary'):</strong> <span id="basic_salary"></span></p>
                            <p><strong>@lang('essentials::lang.housing_allowance'):</strong> <span id="housing_allowance"></span></p>
                            <p><strong>@lang('essentials::lang.transportation_allowance'):</strong> <span id="transportation_allowance"></span></p>
                            <p><strong>@lang('essentials::lang.other_allowance'):</strong> <span id="other_allowance"></span></p>
                            <p><strong>@lang('essentials::lang.final_salary'):</strong> <span id="final_salary"></span></p>
                            <p><strong>@lang('essentials::lang.status'):</strong> <span id="worker_status"></span></p>
                            <p><strong>@lang('essentials::lang.sub_status'):</strong> <span id="worker_sub_status"></span></p>
                            <p><strong>@lang('essentials::lang.id_proof_number'):</strong> <span id="worker_id_proof_number"></span></p>
                            <p><strong>@lang('essentials::lang.residence_permit_expiration'):</strong> <span id="worker_residence_permit_expiration"></span>
                            </p>
                            <p><strong>@lang('essentials::lang.passport_number'):</strong> <span id="worker_passport_number"></span></p>
                            <p><strong>@lang('essentials::lang.passport_expire_date'):</strong> <span id="worker_passport_expire_date"></span></p>
                            <p><strong>@lang('essentials::lang.border_number'):</strong> <span id="worker_border_no"></span></p>
                            <p><strong>@lang('essentials::lang.company_name'):</strong> <span id="worker_company_name"></span></p>
                            <p><strong>@lang('essentials::lang.assigned_to'):</strong> <span id="worker_assigned_to"></span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="edit-worker-info" class="btn btn-primary">@lang('essentials::lang.edit')</button>
                        <button type="button" id="save-worker-info" class="btn btn-success"
                            style="display:none;">@lang('essentials::lang.save')</button>
                        <button type="button" id="cancel-worker-info" class="btn btn-secondary"
                            style="display:none;">@lang('essentials::lang.cancel')</button>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- Modal Template -->
        <!-- Reusable Modal Template -->
        {{-- <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalBodyContent">
                        <!-- Content will be dynamically injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="edit-worker-modal" class="btn btn-primary">@lang('essentials::lang.edit')</button>
                        <button type="button" id="save-worker-modal" class="btn btn-success"
                            style="display:none;">@lang('essentials::lang.save')</button>
                        <button type="button" id="cancel-worker-modal" class="btn btn-secondary"
                            style="display:none;">@lang('essentials::lang.cancel')</button>
                    </div>
                </div>
            </div>
        </div> --}}


        <!-- Combined Modal -->
        <div class="modal fade" id="salaryInfoModal" tabindex="-1" role="dialog" aria-labelledby="salaryInfoModal"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document"> <!-- Make the modal larger -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="salaryInfoModalLabel">@lang('essentials::lang.view_salary_info') & @lang('essentials::lang.personal_info')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Table: Salary Information -->
                            <div class="col-md-6">
                                <div id="salaryModalBody"></div>
                            </div>

                            <!-- Right Table: Worker Information -->
                            <div class="col-md-6">
                                <div id="workerInfoBody">
                                    <p><strong>@lang('essentials::lang.full_name'):</strong> <span id="worker_full_name"></span></p>
                                    <p><strong>@lang('essentials::lang.emp_number'):</strong> <span id="worker_emp_number"></span></p>
                                    <p><strong>@lang('essentials::lang.contry_nationality'):</strong> <span id="nationality"></span></p>

                                    <p><strong>@lang('essentials::lang.status'):</strong> <span id="worker_status"></span></p>
                                    <p><strong>@lang('essentials::lang.sub_status'):</strong> <span id="worker_sub_status"></span></p>
                                    <p><strong>@lang('essentials::lang.id_proof_number'):</strong> <span id="worker_id_proof_number"></span></p>
                                    <p><strong>@lang('essentials::lang.residence_permit_expiration'):</strong> <span
                                            id="worker_residence_permit_expiration"></span></p>
                                    <p><strong>@lang('essentials::lang.passport_number'):</strong> <span id="worker_passport_number"></span></p>
                                    <p><strong>@lang('essentials::lang.passport_expire_date'):</strong> <span id="worker_passport_expire_date"></span>
                                    </p>
                                    <p><strong>@lang('essentials::lang.border_number'):</strong> <span id="worker_border_no"></span></p>
                                    <p><strong>@lang('essentials::lang.company_name'):</strong> <span id="worker_company_name"></span></p>
                                    <p><strong>@lang('essentials::lang.assigned_to'):</strong> <span id="worker_assigned_to"></span></p>
                                    <p><strong>@lang('worker.location'):</strong> <span id="worker_location"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-primary edit-salary">@lang('essentials::lang.edit')</button>
                            <button type="button" class="btn btn-sm btn-success save-salary"
                                style="display: none;">@lang('essentials::lang.save')</button>
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">@lang('essentials::lang.close')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        var is_remote = {!! json_encode($user_type) !!} == "remote_employee" ? true : false;
        var is_worker = {!! json_encode($user_type) !!} == "worker" ? true : false;

        $(document).ready(function() {

            // Handle Table Row Click
            $(document).ready(function() {
                var clickedCellIndex = null; // Declare clickedCellIndex globally
                var clickedWorkerId = null; // Declare clickedWorkerId globally

                // Handle Table Row Click
                $('#workers_table_timesheet tbody').on('click', 'tr', function(e) {
                    clickedCellIndex = $(e.target).closest('td')
                        .index(); // Set the index on row click

                    // Ensure the clicked cell is not an editable cell
                    if (!$(e.target).closest('td').hasClass('editable')) {
                        // Capture the worker_id from the hidden input field
                        clickedWorkerId = $(this).find('input.form-hidden[data-field="id"]').val();

                        console.log('Clicked Worker ID:', clickedWorkerId);

                        if (!clickedWorkerId) {
                            console.error('Worker ID not found in the row.');
                            return;
                        }

                        // Generate URL for Salary Info
                        var salaryInfoUrl = '{{ route('payrolls.view_salary_info', ':id') }}';
                        salaryInfoUrl = salaryInfoUrl.replace(':id', clickedWorkerId);

                        // Fetch Salary Info
                        $.ajax({
                            url: salaryInfoUrl,
                            type: 'GET',
                            data: {
                                worker_id: clickedWorkerId
                            },
                            success: function(response) {
                                var data = response.data;

                                // Generate the Salary Info HTML with name attributes
                                var salaryHtml =
                                    '<table class="table table-bordered table-striped">';
                                salaryHtml +=
                                    '<tr><th style="display:none"></th><td style="display:none" name="user_id">' +
                                    data.user_id + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('essentials::lang.work_days')</th><td name="work_days">' +
                                    data
                                    .work_days + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('essentials::lang.Basic_salary')</th><td class="editable-cell" name="salary">' +
                                    data.salary + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('essentials::lang.housing_allowance')</th><td class="editable-cell" name="housing_allowance">' +
                                    data.housing_allowance + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('essentials::lang.transportation_allowance')</th><td class="editable-cell" name="transportation_allowance">' +
                                    data.transportation_allowance + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('essentials::lang.other_allowance')</th><td class="editable-cell" name="other_allowance">' +
                                    data.other_allowance + '</td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('worker.final_salary')</th><td><input type="text" id="total_salary" class="form-control" name="total" value="' +
                                    data.total + '" readonly></td></tr>';
                                salaryHtml +=
                                    '<tr><th>@lang('lang_v1.bank_code')</th><td name="iban">' +
                                    data.iban +
                                    '</td></tr>';
                                salaryHtml += '</table>';
                                $('#salaryModalBody').html(salaryHtml);

                                // Generate URL for Worker Info
                                var workerInfoUrl =
                                    '{{ route('payrolls.view_worker_info', ':id') }}';
                                workerInfoUrl = workerInfoUrl.replace(':id',
                                    clickedWorkerId);

                                // Fetch Worker Info
                                $.ajax({
                                    url: workerInfoUrl,
                                    type: 'GET',
                                    data: {
                                        worker_id: clickedWorkerId
                                    },
                                    success: function(response) {
                                        var workerData = response.data;

                                        // Populate Worker Info HTML
                                        $('#worker_full_name').text(
                                            workerData
                                            .full_name);
                                        $('#worker_emp_number').text(
                                            workerData
                                            .emp_number);
                                        $('#worker_status').text(workerData
                                            .status);
                                        $('#worker_sub_status').text(
                                            workerData
                                            .sub_status);
                                        $('#worker_id_proof_number').text(
                                            workerData
                                            .id_proof_number);
                                        $('#worker_residence_permit_expiration')
                                            .text(
                                                workerData
                                                .residence_permit_expiration
                                            );
                                        $('#worker_passport_number').text(
                                            workerData
                                            .passport_number);
                                        $('#worker_passport_expire_date')
                                            .text(
                                                workerData
                                                .passport_expire_date);
                                        $('#worker_border_no').text(
                                            workerData
                                            .border_no);
                                        $('#worker_company_name').text(
                                            workerData
                                            .company_name);
                                        $('#worker_assigned_to').text(
                                            workerData
                                            .assigned_to);
                                        $('#worker_location').text(
                                            workerData
                                            .worker_location);
                                        $('#nationality').text(workerData
                                            .nationality);
                                        $('#iban').text(workerData.iban);
                                        $('#final_salary').text(workerData
                                            .final_salary);
                                        $('#basic_salary').text(workerData
                                            .basic_salary);

                                        // Show the Modal
                                        $('#salaryInfoModal').modal('show');
                                    },
                                    error: function(xhr) {
                                        console.error(xhr.responseText);
                                    }
                                });
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });

                // Handle modal hidden event to reset the modal state
                $('#salaryInfoModal').on('hidden.bs.modal', function() {
                    // Reset all editable fields to non-editable state
                    $('#salaryModalBody td.editable-cell').each(function() {
                        var input = $(this).find('input');
                        if (input.length > 0) {
                            var value = input.val();
                            $(this).html(value);
                        }
                    });

                    // Reset the IBAN field
                    $('#salaryModalBody td[name="iban"], #workerInfoBody span#iban').each(
                        function() {
                            var input = $(this).find('input');
                            if (input.length > 0) {
                                var value = input.val();
                                $(this).html(value);
                            }
                        });

                    // Reset buttons
                    $('.edit-salary').show();
                    $('.save-salary').hide();
                });

                // Handle Edit Button Click
                $(document).on('click', '.edit-salary', function(e) {
                    e.preventDefault();

                    $('#salaryModalBody td.editable-cell').each(function() {
                        var value = $(this).text();
                        $(this).html(
                            '<input type="text" class="form-control allowance-input" value="' +
                            value + '">');
                    });

                    // Make the IBAN field editable in both salary and worker info
                    $('#salaryModalBody td[name="iban"], #workerInfoBody span#iban').each(
                        function() {
                            var value = $(this).text();
                            $(this).html('<input type="text" class="form-control" value="' +
                                value + '">');
                        });

                    $('.edit-salary').hide();
                    $('.save-salary').show();

                    // Recalculate the total salary when any allowance input is changed
                    $(document).on('input', '.allowance-input', function() {
                        var totalSalary = 0;

                        $('.allowance-input').each(function() {
                            var inputVal = parseFloat($(this).val());
                            totalSalary += isNaN(inputVal) ? 0 : inputVal;
                        });

                        $('#total_salary').val(totalSalary);
                    });
                });

                // Restrict numeric input for all editable cells except IBAN
                $(document).on('input', '.allowance-input', function() {
                    this.value = this.value.replace(/[^0-9.]/g,
                        ''); // Allow only numbers and a single decimal point
                });

                // Handle Save Button Click
                $(document).on('click', '.save-salary', function(e) {
                    e.preventDefault();
                    var updatedData = {};
                    updatedData['user_id'] =
                        clickedWorkerId; // Use the worker ID captured from the row click

                    // Collect the values using name attributes
                    $('#salaryModalBody td[name], #salaryModalBody input[name], #workerInfoBody span[name]')
                        .each(function() {
                            var name = $(this).attr('name');
                            var value = $(this).find('input').val() || $(this).text();
                            updatedData[name] = value;
                        });

                    console.log(updatedData);
                    // Send the updated data via AJAX
                    $.ajax({
                        url: '{{ route('payrolls.update.salary') }}',
                        type: 'POST',
                        data: updatedData,
                        success: function(response) {
                            $('#salaryInfoModal').modal('hide');
                            console.log('Data updated successfully:', response);
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error('Error updating data:', xhr.responseText);
                        }
                    });
                });
            });





            $('.custom-scrollbar').on('scroll', function() {
                $('.table-responsive2').scrollLeft($(this).scrollLeft());
            });

            $('.table-responsive2').on('scroll', function() {
                $('.custom-scrollbar').scrollLeft($(this).scrollLeft());
            });

            $('td.editable span[contenteditable="true"]').on('input', function() {
                var index = $(this).data('index');
                var field = $(this).data('field');
                var newValue = $(this).text();

                $('input.form-hidden[data-index="' + index + '"][data-field="' + field + '"]').val(
                    newValue);


                //new flied
                if (field == 'absence_deduction') {
                    updateAbsenceDeduction(index);
                }



                if (field == 'absence') {
                    updateAbsenceDeduction(index);
                }
                if (field == 'late') {
                    updateLateDeduction(index);
                }
                if (field == 'over_time_hours') {
                    updateOverTimeHoursAddition(index);
                }
                updateTotal(index);
                updateTotalDeduction(index);
                updateTotalAdditions(index);
                updateFinalSalary(index);
                //initializeCalculations(index);
                updateFooter();
            });

            function updateFooter() {

                updateTheTotalSalary();
                updateTheHousingAllowance();
                updateTheTransportationAllowance();
                updateTheOtherAllowance();
                updateTheTotalTotal();
                updateTheTotalViolations();
                updateTheTotalAbsence();
                updateTheTotalOtherDeductions();
                updateTheTotalLoan();
                updateTheTotalTotalDeduction();
                updateTheTotalOverTimeHours();
                updateTheTotalAdditionalAddition();
                updateTheTotalOtherAdditions();
                updateTheTotalTotalAdditions();
                updateTheTotalFinalSalary();
            }

        });

        function updateAbsenceDeduction(index) {
            var absence = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='absence']").val()) || 0;
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;
            var work_days = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='work_days']").val()) || 0;
            var absence_deduction = absence * (total / work_days);
            $("span[data-index='" + index + "'][data-field='absence_deduction']").text(absence_deduction.toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='absence_deduction']").val(absence_deduction
                .toFixed(2));
        }

        function updateLateDeduction(index) {
            var late = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='late']").val()) || 0;
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;
            var work_days = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='work_days']").val()) || 0;
            var late_deduction = late * (total / work_days / 8);
            $("span[data-index='" + index + "'][data-field='late_deduction']").text(late_deduction.toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='late_deduction']").val(late_deduction
                .toFixed(2));
        }

        function updateOverTimeHoursAddition(index) {
            var over_time_hours = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='over_time_hours']").val()) || 0;
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;

            // Basic Salary to calu overtime
            var salary = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='salary']").val()) || 0;
            var work_days = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='work_days']").val()) || 0;
            // var over_time_hours_addition = over_time_hours * ((total / work_days / 8) * 1.5);

            // Overtime account based on Saudi law
            var over_time_hours_addition = over_time_hours * ((total / work_days / 8) + (salary / work_days / 8) * 0.5);

            $("span[data-index='" + index + "'][data-field='over_time_hours_addition']").text(over_time_hours_addition
                .toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='over_time_hours_addition']").val(
                over_time_hours_addition
                .toFixed(2));
        }

        function initializeCalculations(index) {
            $('.payroll_row').each(function() {
                var index = $(this).find('.editable span[contenteditable="true"]').first().data('index');
                if (index !== undefined) {
                    updateTotal(index);
                    updateTotalDeduction(index);
                    updateTotalAdditions(index);
                    updateFinalSalary(index);
                }
            });
        }

        function updateTotal(index) {
            var salary = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='salary']").val()) || 0;

            var housing_allowance = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='housing_allowance']").val()) || 0;

            var transportation_allowance = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='transportation_allowance']").val()) || 0;
            var other_allowance = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_allowance']").val()) || 0;

            var total = salary + housing_allowance + transportation_allowance + other_allowance;
            $("span[data-index='" + index + "'][data-field='total']").text(total.toFixed(2));

            $("input.form-hidden[data-index='" + index + "'][data-field='total']").val(total.toFixed(2));
        }

        function updateTotalDeduction(index) {
            var violations = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='violations']").val()) || 0;

            var absence_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='absence_deduction']").val()) || 0;
            var late_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='late_deduction']").val()) || 0;
            var other_deductions = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_deductions']").val()) || 0;
            var loan = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='loan']").val()) || 0;

            var total_deduction = violations + absence_deduction + late_deduction + other_deductions + loan;
            $("span[data-index='" + index + "'][data-field='total_deduction']").text(total_deduction.toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_deduction']").val(total_deduction.toFixed(
                2));
            //  total amount of absence
            var total_amount_absence = absence_deduction;
            $("span[data-index='" + index + "'][data-field='total_amount_absence']").text(total_amount_absence.toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_amount_absence']").val(total_amount_absence
                .toFixed(
                    2));


            //  total amount of delay
            var total_amount_of_delay = late_deduction;
            $("span[data-index='" + index + "'][data-field='total_amount_of_delay']").text(total_amount_of_delay.toFixed(
                2));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_amount_of_delay']").val(total_amount_of_delay
                .toFixed(
                    2));
        }

        function updateTotalAdditions(index) {
            if (!is_remote) {
                var over_time_hours_addition = parseFloat($("input.form-hidden[data-index='" + index +
                    "'][data-field='over_time_hours_addition']").val()) || 0;

                var additional_addition = parseFloat($("input.form-hidden[data-index='" + index +
                    "'][data-field='additional_addition']").val()) || 0;
                var other_additions = 0;
                if (!is_worker) {
                    other_additions = parseFloat($("input.form-hidden[data-index='" + index +
                        "'][data-field='other_additions']").val()) || 0;
                }



                var total_additions = over_time_hours_addition + additional_addition + other_additions;
                $("span[data-index='" + index + "'][data-field='total_additions']").text(total_additions.toFixed(2));
                $("input.form-hidden[data-index='" + index + "'][data-field='total_additions']").val(total_additions
                    .toFixed(
                        2));


                var total_amount_over_time_hours = over_time_hours_addition;
                $("span[data-index='" + index + "'][data-field='total_amount_over_time_hours']").text(
                    total_amount_over_time_hours.toFixed(2));
                $("input.form-hidden[data-index='" + index + "'][data-field='total_amount_over_time_hours']").val(
                    total_amount_over_time_hours
                    .toFixed(
                        2));
            }
        }

        function updateFinalSalary(index) {
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;

            var total_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total_deduction']").val()) || 0;

            var total_additions = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total_additions']").val()) || 0;


            var final_salary = total - total_deduction + total_additions;
            $("span[data-index='" + index + "'][data-field='final_salary']").text(final_salary.toFixed(2));
            $("input.form-hidden[data-index='" + index + "'][data-field='final_salary']").val(final_salary.toFixed(
                2));
        }



        function updateTheTotalSalary() {
            var value = 0;
            $("input.form-hidden[data-field='salary']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_salary"]').text(value);
        };

        function updateTheHousingAllowance() {
            var value = 0;
            $("input.form-hidden[data-field='housing_allowance']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_housing_allowance"]').text(value);
        };

        function updateTheTransportationAllowance() {
            var value = 0;
            $("input.form-hidden[data-field='transportation_allowance']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_transportation_allowance"]').text(value);
        };

        function updateTheOtherAllowance() {
            var value = 0;
            $("input.form-hidden[data-field='other_allowance']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_other_allowance"]').text(value);
        };

        function updateTheTotalTotal() {
            var value = 0;
            $("input.form-hidden[data-field='total']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_total"]').text(value);
        };

        function updateTheTotalViolations() {
            var value = 0;
            $("input.form-hidden[data-field='violations']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_violations"]').text(value);
        };

        function updateTheTotalAbsence() {
            var value = 0;
            $("input.form-hidden[data-field='absence']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_absence"]').text(value);
        };

        function updateTheTotalOtherDeductions() {
            var value = 0;
            $("input.form-hidden[data-field='other_deductions']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_other_deductions"]').text(value);
        };

        function updateTheTotalLoan() {
            var value = 0;
            $("input.form-hidden[data-field='loan']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_loan"]').text(value);
        };

        function updateTheTotalTotalDeduction() {
            var value = 0;
            $("input.form-hidden[data-field='total_deduction']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_total_deduction"]').text(value);
        };

        function updateTheTotalOverTimeHours() {
            if (!is_remote) {
                var value = 0;
                $("input.form-hidden[data-field='over_time_hours']").each(function() {
                    value += parseFloat($(this).val()) || 0;
                });
                $('td[name="the_total_over_time_hours"]').text(value);
            }
        };

        function updateTheTotalAdditionalAddition() {
            if (!is_remote) {
                var value = 0;
                $("input.form-hidden[data-field='additional_addition']").each(function() {
                    value += parseFloat($(this).val()) || 0;
                });
                $('td[name="the_total_additional_addition"]').text(value);
            }
        };

        function updateTheTotalOtherAdditions() {
            if (!is_remote && !is_worker) {
                var value = 0;
                $("input.form-hidden[data-field='other_additions']").each(function() {
                    value += parseFloat($(this).val()) || 0;
                });
                $('td[name="the_total_other_additions"]').text(value);
            }
        };

        function updateTheTotalTotalAdditions() {
            if (!is_remote) {
                var value = 0;
                $("input.form-hidden[data-field='total_additions']").each(function() {
                    value += parseFloat($(this).val()) || 0;
                });
                $('td[name="the_total_total_additions"]').text(value);
            }
        };

        function updateTheTotalFinalSalary() {
            var value = 0;
            $("input.form-hidden[data-field='final_salary']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $("#total_payrolls").val(value);
            $('td[name="the_total_final_salary"]').text(value);
            // $('#h4_total_payrolls').text(value);
            // $("#total_payrolls").val(value);
        };

        initializeCalculations();
        updateTheTotalSalary();
        updateTheHousingAllowance();
        updateTheTransportationAllowance();
        updateTheOtherAllowance();
        updateTheTotalTotal();
        updateTheTotalViolations();
        updateTheTotalAbsence();
        updateTheTotalOtherDeductions();
        updateTheTotalLoan();
        updateTheTotalTotalDeduction();
        updateTheTotalOverTimeHours();
        updateTheTotalAdditionalAddition();
        updateTheTotalOtherAdditions();
        updateTheTotalTotalAdditions();
        updateTheTotalFinalSalary();
    </script>
@endsection
