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
                            <div class="col-md-12">
                                <p>@lang('worker.full_name'): {{ $user->first_name }} {{ $user->last_name }}</p>
                            </div>
                            <div class="col-md-12">
                                <p>@lang('worker.nationality'): {{ $user->country->nationality }}</p>
                            </div>
                            <div class="col-md-12">
                                <p>@lang('worker.residency'): {{ $user->id_proof_number }}</p>
                            </div>
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
                            <div class="col-md-6">
                                <p>@lang('worker.work_days'): {{ $attendance->work_days }} </p>
                            </div>

                            <div class="col-md-6">
                                <p>@lang('worker.actual_work_days'): {{ $attendance->actual_work_days }}</p>
                            </div>

                            <div class="col-md-6">
                                <p>@lang('worker.late_days'): {{ $attendance->late_days }}</p>
                            </div>
                            <div class="col-md-6">
                                <p>@lang('worker.out_of_site_days'): {{ $attendance->out_of_site_days }}</p>
                            </div>
                            <div class="col-md-6">
                                <p>@lang('worker.absence_days'): {{ $attendance->absence_days }}</p>
                            </div>
                            <div class="col-md-6">
                                <p>@lang('worker.leave_days'): {{ $attendance->leave_days }}</p>
                            </div>
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
                            <div class="col-md-6">

                                <p>@lang('agent.basic_salary'): {{ number_format($user->essentials_salary ?? 0, 0) }} </p>
                            </div>
                            <div class="col-md-6">
                                <p>@lang('agent.absence_deductions'): {{ number_format($absence_deductions ?? 0, 0) }} </p>
                            </div>
                            @foreach ($allowances_and_deductions->allowances as $key => $allowance)
                                <div class="col-md-6">
                                    <p>{{ $allowance->essentialsAllowanceAndDeduction->description }}:
                                        {{ number_format($allowance->amount ?? 0, 0) }} </p>
                                </div>
                            @endforeach

                        </div>
                        <div class="col-md-12">
                            <h4 style="float: left">@lang('agent.total'): {{ number_format($cost2 ?? 0) }}
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

    @stop
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {


                var essentialsAllowanceAndDeductionRow = 0;
                var essentialsDeductionRowCount = 0;
                var essentialsAllowance = <?php echo json_encode($essentialsAllowance); ?>;
                var essentialsDeduction = <?php echo json_encode($essentialsDeduction); ?>;
                var totalSalary = parseFloat('{{ $cost2 ?? 0 }}');
                var deductions = @json($deductions);

                var additions = @json($additions);

                check_additions_deductions();

                function check_additions_deductions() {
                    if (additions) {
                        additions = JSON.parse(@json($additions));
                        additions.allowance_names.forEach((allowance, key) => {
                            var newRow = '<tr id="row-' + essentialsAllowanceAndDeductionRow + '">' +
                                '<td>' +
                                '<div class="row">' +
                                '<div class="col-md-6">' +
                                '<select name="allowances[]" class="form-control pull-left" style="height:40px">';

                            // Add options to select and set the selected value
                            $.each(essentialsAllowance, function(allowanceKey, allowanceValue) {
                                newRow += '<option value="' + allowanceKey + '"' +
                                    (allowance === allowanceValue ? ' selected' : '') +
                                    '>' + allowanceValue + '</option>';
                            });

                            newRow += '</select>' +
                                '</div>' +
                                '<div class="col-md-4">' +
                                '<input type="text" name="allowances_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}" value="' +
                                additions.allowance_amounts[key] + '">' +
                                '</div>' +
                                '<div class="col-md-1 button_container">' +
                                '<button type="button" class="btn btn-danger btn-xs allowance_remove_tr" data-id="row-' +
                                essentialsAllowanceAndDeductionRow + '"><i class="fa fa-minus"></i></button>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '</tr>';

                            $('#essentialsAllowanceTable').append(newRow);
                            essentialsAllowanceAndDeductionRow++;
                        });
                        updateTotalSalary();
                    }

                    if (deductions) {
                        deductions = JSON.parse(@json($deductions));
                        deductions.deduction_names.forEach((deduction, key) => {
                            var newRow = '<tr id="deduction-row-' + essentialsDeductionRowCount + '">' +
                                '<td>' +
                                '<div class="row">' +
                                '<div class="col-md-6">' +
                                '<select name="deductions[]" class="form-control pull-left" style="height:40px">';

                            // Add options to select and set the selected value
                            $.each(essentialsDeduction, function(deductionKey, deductionValue) {
                                newRow += '<option value="' + deductionKey + '"' +
                                    (deduction === deductionValue ? ' selected' : '') +
                                    '>' + deductionValue + '</option>';
                            });

                            newRow += '</select>' +
                                '</div>' +
                                '<div class="col-md-4">' +
                                '<input type="text" name="deductions_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}" value="' +
                                deductions.deduction_amounts[key] + '">' +
                                '</div>' +
                                '<div class="col-md-1 button_container">' +
                                '<button type="button" class="btn btn-danger btn-xs deduction_remove_tr" data-id="deduction-row-' +
                                essentialsDeductionRowCount + '"><i class="fa fa-minus"></i></button>' +
                                '</div>' +
                                '</div>' +
                                '</td>' +
                                '</tr>';

                            $('#essentialsDeductionTable').append(newRow);
                            essentialsDeductionRowCount++;
                        });
                        updateTotalSalary();
                    }



                }


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
