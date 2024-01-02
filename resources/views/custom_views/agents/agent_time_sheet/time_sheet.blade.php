@extends('layouts.app')

@php
    $action_url = action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'store']);
    $title = __('essentials::lang.add_payroll');
    $subtitle = __('essentials::lang.add_payroll');
    $submit_btn_text = __('messages.save');
    $group_name = __('essentials::lang.payroll_for_month', ['date' => $month_name . ' ' . $year]);
    if ($action == 'edit') {
        $action_url = action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'getUpdatePayrollGroup']);
        $title = __('essentials::lang.edit_payroll');
        $subtitle = __('essentials::lang.edit_payroll');
        $submit_btn_text = __('messages.update');
    }
@endphp

@section('title', $title)

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> @lang('agent.time_sheet') </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open(['url' => route('agentTimeSheet.store'), 'method' => 'post']) !!}
        {!! Form::hidden('total', $user->total_salary) !!}
        {!! Form::hidden('transaction_date', $transaction_date) !!}
        {!! Form::hidden('user', json_encode($user)) !!}
        {!! Form::hidden('attendance', json_encode($attendance)) !!}
        {!! Form::hidden('allowances_and_deductions', json_encode($allowances_and_deductions)) !!}
        <input type=hidden name='total_salary' id='total_salary'>
        <div class="row">
            <div class="col-md-12">

                <div class="col-md-4 ">
                    <div class="custom_box box box-success">
                        <div class="col-md-12">
                            <h4> @lang('agent.worker_info') </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-md-12">
                            <p>@lang('worker.full_name'): {{ $user->first_name }} {{ $user->last_name }}</p>
                            <p>@lang('worker.nationality'): {{ $user->country->nationality }}</p>
                            <p>@lang('worker.residency'): {{ $user->id_proof_number }}</p>
                            <p>Monthly Cost: </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="custom_box box box-success">
                        <div class="col-md-12">
                            <h4> @lang('agent.work_days_details') </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-md-12">
                            <p>@lang('worker.work_days'): {{ $attendance->work_days }} </p>

                            <p>@lang('worker.actual_work_days'): {{ $attendance->actual_work_days }}</p>

                            <p>@lang('worker.late_days'): {{ $attendance->late_days }}</p>

                            <p>@lang('worker.out_of_site_days'): {{ $attendance->out_of_site_days }}</p>

                            <p>@lang('worker.absence_days'): {{ $attendance->absence_days }}</p>

                            <p>@lang('worker.leave_days'): {{ $attendance->leave_days }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="custom_box box box-success">

                        <div class="col-md-9">
                            <h4> @lang('agent.payroll_details')</h4>
                        </div>

                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-md-12">
                            <p>@lang('agent.basic_salary'): {{ number_format($user->essentials_salary ?? 0, 0) }} </p>
                            @foreach ($allowances_and_deductions->allowances as $key => $allowance)
                                <p>{{ $allowance->essentialsAllowanceAndDeduction->description }}:
                                    {{ number_format($allowance->amount ?? 0, 0) }} </p>
                            @endforeach
                        </div>
                        <div class="col-md-12">
                            <h4>@lang('agent.total'): {{ number_format($user->total_salary ?? 0) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-4 ">
                    <div class="custom_box box box-success">

                        <div class="col-md-10">
                            <h4> @lang('agent.additional_allowances')</h4>
                        </div>

                        <div class="col-md-1 button_container">
                            <h4>
                                <button type="button" id="essentialsAllowanceAddRow" class="btn btn-primary">
                                    @lang('messages.add')
                                </button>
                            </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="form-group">
                            <table class="table">

                                <tbody id="essentialsAllowanceTable">
                                </tbody>
                            </table>
                        </div>



                    </div>
                </div>


                <div class="col-md-4 ">
                    <div class="custom_box box box-success">

                        <div class="col-md-10">
                            <h4> @lang('agent.deductions')</h4>
                        </div>

                        <div class="col-md-1 button_container">
                            <h4>
                                <button type="button" id="essentialsDeductionAddRow" class="btn btn-primary">
                                    @lang('messages.add')
                                </button>
                            </h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="form-group">
                            <table class="table">

                                <tbody id="essentialsDeductionTable">
                                </tbody>
                            </table>
                        </div>



                    </div>
                </div>



                <div class="col-md-4 ">
                    <div class="custom_box box box-success custom_container">
                        <h3>@lang('agent.total_salary')</h3>
                        <h3 id="total_salary_h3"></h3>
                        <div class="row custom_submit">
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary" id="submit_user_button">
                                    {{ $submit_btn_text }}
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {!! Form::close() !!}
        {{-- {!! Form::open(['url' => $action_url, 'method' => 'post', 'id' => 'add_payroll_form']) !!}
        {!! Form::hidden('transaction_date', $transaction_date) !!}
        @if ($action == 'edit')
            {!! Form::hidden('payroll_group_id', $payroll_group->id) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-body">

                        <table class="table" id="payroll_table">
                            <tr>
                                <th>
                                    @lang('essentials::lang.employee')
                                </th>
                                <th>
                                    @lang('essentials::lang.salary')
                                </th>
                                <th>
                                    @lang('essentials::lang.allowances')
                                </th>
                                <th>
                                    @lang('essentials::lang.deductions')
                                </th>
                                <th>
                                    @lang('essentials::lang.gross_amount')
                                </th>
                            </tr>
                            @foreach ($payrolls as $employee => $payroll)
                                @php
                                    if ($action != 'edit') {
                                        $amount_per_unit_duration = (float) $payroll['essentials_salary'];
                                        $total_work_duration = 1;
                                        $duration_unit = __('lang_v1.month');
                                        if ($payroll['essentials_pay_period'] == 'week') {
                                            $total_work_duration = 4;
                                            $duration_unit = __('essentials::lang.week');
                                        } elseif ($payroll['essentials_pay_period'] == 'day') {
                                            $total_work_duration = \Carbon::parse($transaction_date)->daysInMonth;
                                            $duration_unit = __('lang_v1.day');
                                        }
                                        $total = $total_work_duration * $amount_per_unit_duration;
                                    } else {
                                        $amount_per_unit_duration = $payroll['essentials_amount_per_unit_duration'];
                                        $total_work_duration = $payroll['essentials_duration'];
                                        $duration_unit = $payroll['essentials_duration_unit'];
                                        $total = $total_work_duration * $amount_per_unit_duration;
                                    }
                                @endphp
                                <tr data-id="{{ $employee }}">
                                    <input type="hidden" name="payrolls[{{ $employee }}][expense_for]"
                                        value="{{ $employee }}">
                                    @if ($action == 'edit')
                                        {!! Form::hidden('payrolls[' . $employee . '][transaction_id]', $payroll['transaction_id']) !!}
                                    @endif
                                    <td>
                                        {{ $payroll['name'] }}
                                        <br><br>
                                        <b>{{ __('essentials::lang.leaves') }} :</b>
                                        {{ __('essentials::lang.total_leaves_days', ['total_leaves' => $payroll['total_leaves']]) }}
                                        <br><br>
                                        <b>{{ __('essentials::lang.work_duration') }} :</b>
                                        {{ __('essentials::lang.work_duration_hour', ['duration' => $payroll['total_work_duration']]) }}
                                        <br><br>
                                        <b>
                                            {{ __('essentials::lang.attendance') }}:
                                        </b>
                                        {{ $payroll['total_days_worked'] }} @lang('lang_v1.days')
                                    </td>
                                    <td>
                                        {!! Form::label('essentials_duration_' . $employee, __('essentials::lang.total_work_duration') . ':*') !!}
                                        {!! Form::text('payrolls[' . $employee . '][essentials_duration]', $total_work_duration, [
                                            'class' => 'form-control input_number essentials_duration',
                                            'placeholder' => __('essentials::lang.total_work_duration'),
                                            'required',
                                            'data-id' => $employee,
                                            'id' => 'essentials_duration_' . $employee,
                                        ]) !!}
                                        <br>

                                        {!! Form::label('essentials_duration_unit_' . $employee, __('essentials::lang.duration_unit') . ':') !!}
                                        {!! Form::text('payrolls[' . $employee . '][essentials_duration_unit]', $duration_unit, [
                                            'class' => 'form-control',
                                            'placeholder' => __('essentials::lang.duration_unit'),
                                            'data-id' => $employee,
                                            'id' => 'essentials_duration_unit_' . $employee,
                                        ]) !!}

                                        <br>

                                        {!! Form::label(
                                            'essentials_amount_per_unit_duration_' . $employee,
                                            __('essentials::lang.amount_per_unit_duartion') . ':*',
                                        ) !!}
                                        {!! Form::text('payrolls[' . $employee . '][essentials_amount_per_unit_duration]', $amount_per_unit_duration, [
                                            'class' => 'form-control input_number essentials_amount_per_unit_duration',
                                            'placeholder' => __('essentials::lang.amount_per_unit_duartion'),
                                            'required',
                                            'data-id' => $employee,
                                            'id' => 'essentials_amount_per_unit_duration_' . $employee,
                                        ]) !!}

                                        <br>
                                        {!! Form::label('total_' . $employee, __('sale.total') . ':') !!}
                                        {!! Form::text('payrolls[' . $employee . '][total]', $total, [
                                            'class' => 'form-control input_number total',
                                            'placeholder' => __('sale.total'),
                                            'data-id' => $employee,
                                            'id' => 'total_' . $employee,
                                        ]) !!}
                                    </td>
                                    <td>
                                        @component('components.widget')
                                            <table class="table table-condenced allowance_table"
                                                id="allowance_table_{{ $employee }}" data-id="{{ $employee }}">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5">@lang('essentials::lang.description')</th>
                                                        <th class="col-md-3">@lang('essentials::lang.amount_type')</th>
                                                        <th class="col-md-3">@lang('sale.amount')</th>
                                                        <th class="col-md-1">&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $total_allowances = 0;
                                                    @endphp
                                                    @if (!empty($payroll['allowances']))
                                                        @foreach ($payroll['allowances']['allowance_names'] as $key => $value)
                                                            @include(
                                                                'essentials::payroll.allowance_and_deduction_row',
                                                                [
                                                                    'add_button' =>
                                                                        $loop->index == 0 ? true : false,
                                                                    'type' => 'allowance',
                                                                    'name' => $value,
                                                                    'value' =>
                                                                        $payroll['allowances'][
                                                                            'allowance_amounts'
                                                                        ][$key],
                                                                    'amount_type' =>
                                                                        $payroll['allowances']['allowance_types'][
                                                                            $key
                                                                        ],
                                                                    'percent' =>
                                                                        $payroll['allowances'][
                                                                            'allowance_percents'
                                                                        ][$key],
                                                                ]
                                                            )

                                                            @php
                                                                $total_allowances += $payroll['allowances']['allowance_amounts'][$key];
                                                            @endphp
                                                        @endforeach
                                                    @else
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['add_button' => true, 'type' => 'allowance']
                                                        )
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['type' => 'allowance']
                                                        )
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['type' => 'allowance']
                                                        )
                                                    @endif
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">@lang('sale.total')</th>
                                                        <td><span id="total_allowances_{{ $employee }}"
                                                                class="display_currency"
                                                                data-currency_symbol="true">{{ $total_allowances }}</span>
                                                        </td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        @endcomponent
                                    </td>
                                    <td>
                                        @component('components.widget')
                                            <table class="table table-condenced deductions_table"
                                                id="deductions_table_{{ $employee }}" data-id="{{ $employee }}">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5">@lang('essentials::lang.description')</th>
                                                        <th class="col-md-3">@lang('essentials::lang.amount_type')</th>
                                                        <th class="col-md-3">@lang('sale.amount')</th>
                                                        <th class="col-md-1">&nbsp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $total_deductions = 0;
                                                    @endphp
                                                    @if (!empty($payroll['deductions']))
                                                        @foreach ($payroll['deductions']['deduction_names'] as $key => $value)
                                                            @include(
                                                                'essentials::payroll.allowance_and_deduction_row',
                                                                [
                                                                    'add_button' =>
                                                                        $loop->index == 0 ? true : false,
                                                                    'type' => 'deduction',
                                                                    'name' => $value,
                                                                    'value' =>
                                                                        $payroll['deductions'][
                                                                            'deduction_amounts'
                                                                        ][$key],
                                                                    'amount_type' =>
                                                                        $payroll['deductions']['deduction_types'][
                                                                            $key
                                                                        ],
                                                                    'percent' =>
                                                                        $payroll['deductions'][
                                                                            'deduction_percents'
                                                                        ][$key],
                                                                ]
                                                            )

                                                            @php
                                                                $total_deductions += $payroll['deductions']['deduction_amounts'][$key];
                                                            @endphp
                                                        @endforeach
                                                    @else
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['add_button' => true, 'type' => 'deduction']
                                                        )
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['type' => 'deduction']
                                                        )
                                                        @include(
                                                            'essentials::payroll.allowance_and_deduction_row',
                                                            ['type' => 'deduction']
                                                        )
                                                    @endif
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">@lang('sale.total')</th>
                                                        <td><span id="total_deductions_{{ $employee }}"
                                                                class="display_currency"
                                                                data-currency_symbol="true">{{ $total_deductions }}</span>
                                                        </td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        @endcomponent
                                    </td>
                                    <td>
                                        <strong>
                                            <span id="gross_amount_text_{{ $employee }}">0</span>
                                        </strong>
                                        <br>
                                        {!! Form::hidden('payrolls[' . $employee . '][final_total]', 0, [
                                            'id' => 'gross_amount_' . $employee,
                                            'class' => 'gross_amount',
                                        ]) !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <div class="form-group">
                                            {!! Form::label('note_' . $employee, __('brand.note') . ':') !!}
                                            {!! Form::textarea('payrolls[' . $employee . '][staff_note]', $payroll['staff_note'] ?? null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sale.total'),
                                                'id' => 'note_' . $employee,
                                                'rows' => 3,
                                            ]) !!}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                {!! Form::hidden('total_gross_amount', 0, ['id' => 'total_gross_amount']) !!}
                <button type="submit" class="btn btn-primary pull-right m-8" id="submit_user_button">
                    {{ $submit_btn_text }}
                </button>
                <div class="form-group pull-right m-8 mt-15">
                    <label>
                        {!! Form::checkbox('notify_employee', 1, 0, ['class' => 'input-icheck']) !!} {{ __('essentials::lang.notify_employee') }}
                    </label>
                </div>
            </div>
        </div>
        {!! Form::close() !!}  --}}
    @stop
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {


                var essentialsAllowanceAndDeductionRow = 0;
                var essentialsDeductionRowCount = 0;
                var essentialsAllowance = <?php echo json_encode($essentialsAllowance); ?>;
                var essentialsDeduction = <?php echo json_encode($essentialsDeduction); ?>;
                var totalSalary = parseFloat('{{ $user->total_salary ?? 0 }}');


                $('#essentialsAllowanceAddRow').click(function() {
                    var newRow = '<tr id="row-' + essentialsAllowanceAndDeductionRow + '">' +
                        '<td>' +
                        '<div class="row">' +
                        '<div class="col-md-6">' +
                        '<select name="allowances[]" class="form-control pull-left" style="height:40px" ></select>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<input type="text" name="allowances_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}">' +
                        '</div>' +
                        '<div class="col-md-1 button_container">' +
                        '<button type="button" class="btn btn-danger btn-xs allowance_remove_tr" data-id="row-' +
                        essentialsAllowanceAndDeductionRow + '"><i class="fa fa-minus"></i></button>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';

                    $('#essentialsAllowanceTable').append(newRow);

                    // Populate the select options for the new row
                    var selectElement = $('#row-' + essentialsAllowanceAndDeductionRow +
                        ' select[name="allowances[]"]');
                    selectElement.empty(); // Clear existing options
                    $.each(essentialsAllowance, function(key, value) {
                        selectElement.append($('<option>', {
                            value: key,
                            text: value
                        }));
                    });

                    essentialsAllowanceAndDeductionRow++;
                    updateTotalSalary();

                });

                $('#essentialsAllowanceTable').on('click', '.allowance_remove_tr', function() {
                    var rowId = $(this).data('id');
                    $('#' + rowId).remove();
                    updateTotalSalary();

                });

                $('#essentialsDeductionAddRow').click(function() {
                    var newRow = '<tr id="row-' + essentialsAllowanceAndDeductionRow + '">' +
                        '<td>' +
                        '<div class="row">' +
                        '<div class="col-md-6">' +
                        '<select name="deductions[]" class="form-control pull-left" style="height:40px"></select>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<input type="text" name="deductions_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}">' +
                        '</div>' +
                        '<div class="col-md-1 button_container">' +
                        '<button type="button" class="btn btn-danger btn-xs deduction_remove_tr" data-id="row-' +
                        essentialsAllowanceAndDeductionRow + '"><i class="fa fa-minus"></i></button>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';

                    $('#essentialsDeductionTable').append(newRow);

                    // Populate the select options for the new row
                    var selectElement = $('#row-' + essentialsAllowanceAndDeductionRow +
                        ' select[name="deductions[]"]');
                    selectElement.empty(); // Clear existing options
                    $.each(essentialsDeduction, function(key, value) {
                        selectElement.append($('<option>', {
                            value: key,
                            text: value
                        }));
                    });

                    essentialsAllowanceAndDeductionRow++;
                    updateTotalSalary();

                });

                $('#essentialsDeductionTable').on('click', '.deduction_remove_tr', function() {
                    var rowId = $(this).data('id');
                    $('#' + rowId).remove();
                    updateTotalSalary();

                });



                function updateTotalSalary() {
                    var totalAllowances = 0;
                    var totalDeductions = 0;

                    // Calculate total allowances
                    $('select[name="allowances[]"]').each(function() {
                        var allowanceId = $(this).val();
                        var allowanceAmount = parseFloat($(this).closest('tr').find(
                            'input[name="allowances_amount[]"]').val() || 0);
                        if (!isNaN(allowanceAmount)) {
                            totalAllowances += allowanceAmount;
                        }
                    });

                    // Calculate total deductions
                    $('select[name="deductions[]"]').each(function() {
                        var deductionId = $(this).val();
                        var deductionAmount = parseFloat($(this).closest('tr').find(
                            'input[name="deductions_amount[]"]').val() || 0);
                        if (!isNaN(deductionAmount)) {
                            totalDeductions += deductionAmount;
                        }
                    });

                    // Update the total_salary
                    var totalSalary2 = totalSalary + totalAllowances - totalDeductions;


                    // Update the text of the element
                    $('#total_salary_h3').text(totalSalary2);
                    $('#total_salary').val(totalSalary2);
                }

                $('#essentialsAllowanceTable').on('input', 'input[name="allowances_amount[]"]', function() {
                    // Update total_salary when an allowance value is changed
                    updateTotalSalary();
                });

                $('#essentialsDeductionTable').on('input', 'input[name="deductions_amount[]"]', function() {
                    // Update total_salary when an allowance value is changed
                    updateTotalSalary();
                });

                updateTotalSalary();

                //add allowance row
                $('.add_allowance').click(function() {
                    let id = $(this).parent().parent().parent().parent().data('id');
                    $this = $(this);
                    $.ajax({
                        method: "GET",
                        dataType: "html",
                        data: {
                            'employee_id': id,
                            'type': 'allowance'
                        },
                        url: '/hrm/get-allowance-deduction-row',
                        success: function(result) {
                            $this.closest('.allowance_table tbody').append(result);
                        }
                    });
                });

                //add deduction row
                $('.add_deduction').click(function() {
                    let id = $(this).parent().parent().parent().parent().data('id');
                    $this = $(this);
                    $.ajax({
                        method: "GET",
                        dataType: "html",
                        data: {
                            'employee_id': id,
                            'type': 'deduction'
                        },
                        url: '/hrm/get-allowance-deduction-row',
                        success: function(result) {
                            $this.closest('.deductions_table tbody').append(result);
                        }
                    });
                });

                //remove allowance/deduction row
                $(document).on('click', 'button.remove_tr', function() {
                    let id = $(this).parent().parent().parent().parent().data('id');
                    $(this).closest('tr').remove();
                    calculateTotal(id);
                    calculateTotalGrossAmount();
                });

                //toggle allowance/deduction amount type
                $(document).on('change', '.amount_type', function() {
                    let tr = $(this).closest('tr');
                    if ($(this).val() == 'percent') {
                        tr.find('.percent_field').removeClass('hide');
                        tr.find('.value_field').attr('readonly', true);
                    } else {
                        tr.find('.percent_field').addClass('hide');
                        tr.find('.value_field').removeAttr('readonly');
                    }
                });

                //calculate amount per unit duration
                $(document).on('change', '.total', function() {
                    let total_duration = __read_number($(this).closest('td').find('input.essentials_duration'));
                    let total = __read_number($(this));
                    let amount_per_unit_duration = total / total_duration;
                    __write_number($(this).closest('td').find('input.essentials_amount_per_unit_duration'),
                        amount_per_unit_duration, false, 2);
                    calculateTotal($(this).data('id'));
                    calculateTotalGrossAmount();
                });

                $(document).on('change',
                    '.essentials_duration, .essentials_amount_per_unit_duration, input.allowance, input.deduction, input.percent',
                    function() {
                        let id = $(this).data('id');
                        if ($(this).hasClass('allowance') || $(this).hasClass('deduction')) {
                            id = $(this).parent().parent().parent().parent().data('id');
                        } else if ($(this).hasClass('percent')) {
                            console.log();
                            id = $(this).parent().parent().parent().parent().parent().data('id');
                        }
                        calculateTotal(id);
                        calculateTotalGrossAmount();
                    });

                function calculateTotal(id) {
                    //calculate basic salary
                    let total_duration = __read_number($("input#essentials_duration_" + id));
                    let amount_per_unit_duration = __read_number($("input#essentials_amount_per_unit_duration_" + id));
                    let total = total_duration * amount_per_unit_duration;
                    __write_number($("input#total_" + id), total, false, 2);

                    //calculate total allownace
                    let total_allowance = 0;
                    $("table#allowance_table_" + id).find('tbody tr').each(function() {
                        let type = $(this).find('.amount_type').val();
                        if (type == 'percent') {
                            let percent = __read_number($(this).find('.percent'));
                            let row_total = __calculate_amount('percentage', percent, total);
                            __write_number($(this).find('input.allowance'), row_total);
                        }
                        total_allowance += __read_number($(this).find('input.allowance'));
                    });
                    $('#total_allowances_' + id).text(__currency_trans_from_en(total_allowance, true));

                    //calculate total deduction
                    let total_deduction = 0;
                    $('table#deductions_table_' + id).find('tbody tr').each(function() {
                        let type = $(this).find('.amount_type').val();
                        if (type == 'percent') {
                            let percent = __read_number($(this).find('.percent'));
                            let row_total = __calculate_amount('percentage', percent, total);
                            __write_number($(this).find('input.deduction'), row_total);
                        }
                        total_deduction += __read_number($(this).find('input.deduction'));
                    });
                    $('#total_deductions_' + id).text(__currency_trans_from_en(total_deduction, true));

                    //calculate gross amount
                    var gross_amount = total + total_allowance - total_deduction;
                    $('#gross_amount_' + id).val(gross_amount);
                    $('#gross_amount_text_' + id).text(__currency_trans_from_en(gross_amount, true));
                }

                function calculateTotalGrossAmount() {
                    let total_gross_amount = 0;
                    $("input.gross_amount").each(function() {
                        let gross_amount = __read_number($(this));
                        total_gross_amount += gross_amount;
                    });
                    $('#total_gross_amount').val(total_gross_amount);
                }

                $("table#payroll_table tbody tr").each(function() {
                    calculateTotal($(this).data('id'));
                    calculateTotalGrossAmount();
                });
            });
        </script>
    @endsection
