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
    {!! Form::open([
        'url' => route('agentTimeSheet.submitTmeSheet'),
        'method' => 'post',
        'id' => 'add_payroll_form',
    ]) !!}
    {!! Form::hidden('transaction_date', $transaction_date) !!}
    <section class="content-header">
        <div class="box box-solid">
            <div class="box-body">
                <div class="col-md-12">
                    <div class="col-md-4"
                        style=" display: flex;
                    justify-content: center; 
                    align-items: center;
                 
                    text-align: center;">
                        <h3>{!! $group_name !!}</h3>
                    </div>

                    <div class="col-md-4"
                        style=" display: flex;
                    justify-content: center; 
                    align-items: center;
                 
                    text-align: center;">
                        <h3 style="  color: red;" class="total_payrolls"></h3>
                    </div>
                    <div class="col-md-4"
                        style=" display: flex;
                    justify-content: center; 
                    align-items: center;
                 
                    text-align: center;">
                        <h3>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i>
                                @lang('worker.submit')
                            </button>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Main content -->
    <section class="content">

        @if ($action == 'edit')
            {!! Form::hidden('payroll_group_id', $payroll_group->id) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-body">

                        @foreach ($payrolls as $employee => $payroll)
                            <div class=" payroll_box">
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
                                                        <p>@lang('worker.full_name'): {{ $payroll['name'] }}</p>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <p>@lang('worker.nationality'): {{ $payroll['nationality'] }}</p>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <p>@lang('worker.residency'): {{ $payroll['id_proof_number'] }}</p>
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
                                                    <div class="col-md-12">

                                                        <p>@lang('agent.basic_salary'):
                                                            {{ number_format($payroll['essentials_salary'] ?? 0, 0) }} </p>
                                                    </div>
                                                    @if (!empty($payroll['allowances']))
                                                        @foreach ($payroll['allowances']['allowance_names'] as $key => $allowance)
                                                            <div class="col-md-6">
                                                                <p>{{ $allowance }}:
                                                                    {{ number_format($payroll['allowances']['allowance_amounts'][$key] ?? 0, 0) }}
                                                                </p>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <div class="col-md-12">
                                                    <h4 class="total_salary" style="float: left">@lang('agent.total'):
                                                        {{ number_format($payroll['total_salary'] ?? 0) }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 ">
                                            <div class="custom_box box box-success custom_container">
                                                <h3>@lang('agent.total_salary')</h3>
                                                <h3 class="total_salary_h3"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-4 ">
                                            <div class="custom_box box box-success essentialsAllowanceContainer">

                                                <div class="col-md-10">
                                                    <h4> @lang('agent.additional_allowances')</h4>
                                                </div>

                                                <div class="col-md-1 button_container">
                                                    <h4>
                                                        <button type="button"
                                                            class="btn btn-primary essentialsAllowanceAddRow">
                                                            @lang('messages.add')
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="clearfix"></div>
                                                <hr>
                                                <div class="form-group">
                                                    <table class="table">

                                                        <tbody class="essentialsAllowanceTable">
                                                        </tbody>
                                                    </table>
                                                </div>



                                            </div>
                                        </div>


                                        <div class="col-md-4 ">
                                            <div class="custom_box box box-success essentialsDeductionContainer">

                                                <div class="col-md-10">
                                                    <h4> @lang('agent.deductions')</h4>
                                                </div>

                                                <div class="col-md-1 button_container">
                                                    <h4>
                                                        <button type="button"
                                                            class="btn btn-primary essentialsDeductionAddRow">
                                                            @lang('messages.add')
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="clearfix"></div>
                                                <hr>
                                                <div class="form-group">
                                                    <table class="table">

                                                        <tbody class="essentialsDeductionTable">
                                                        </tbody>
                                                    </table>
                                                </div>



                                            </div>
                                        </div>




                                    </div>

                                </div>
                            </div>
                            <hr>
                        @endforeach
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

    @stop
    {!! Form::close() !!}
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {
                var init_total_payrolls = 0;
                var total_payrolls = 0;
                var formatter = new Intl.NumberFormat('en-US', {
                    style: 'decimal',

                });

                function updateTotalSalary(inputElement) {
                    // Find the closest payroll container to the input element
                    var payrollContainer = inputElement.closest('.payroll_box');
                    var initialTotal = parseFloat(payrollContainer.find('.total_salary').text().replace(/[^0-9.-]+/g,
                        "")) || 0;

                    var total = initialTotal;
                    total_payrolls = init_total_payrolls;

                    // Iterate through all allowances amount inputs within the same payroll container and add their values
                    payrollContainer.find('input[name="allowances_amount[]"]').each(function() {
                        var amount = parseFloat($(this).val()) || 0;
                        total += amount;
                        total_payrolls += amount;
                    });
                    payrollContainer.find('input[name="deductions_amount[]"]').each(function() {
                        var amount = parseFloat($(this).val()) || 0;
                        total -= amount;
                        total_payrolls -= amount;
                    });

                    var formattedTotal = formatter.format(total);

                    // Update the total salary display within the same payroll container
                    payrollContainer.find('.total_salary_h3').text(formattedTotal);
                    $('.total_payrolls').text('@lang('agent.total_payrolls'): ' + formatter.format(total_payrolls));
                }
                $('body').on('input', 'input[name="allowances_amount[]"]', function() {
                    updateTotalSalary($(this));
                });
                $('body').on('input', 'input[name="deductions_amount[]"]', function() {
                    updateTotalSalary($(this));
                });
                $('body').on('click', '.allowance_remove_tr', function() {
                    var container = $(this).closest('.payroll_box');
                    $(this).closest('.essentialsAllowance').remove();
                    updateTotalSalary($(container));
                });
                $('body').on('click', '.deduction_remove_tr', function() {
                    var container = $(this).closest('.payroll_box');
                    $(this).closest('.essentialsDeduction').remove();
                    updateTotalSalary($(container));
                });
                $('body').on('click', '.essentialsAllowanceAddRow', function() {
                    var newRow = '<tr class="essentialsAllowance">' +
                        '<td>' +
                        '<div class="row">' +
                        '<div class="col-md-6">' +
                        '<select name="allowances[]" class="form-control pull-left" style="height:40px" ></select>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<input type="text" name="allowances_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}">' +
                        '</div>' +
                        '<div class="col-md-1 button_container">' +
                        '<button type="button" class="btn btn-danger btn-xs allowance_remove_tr"><i class="fa fa-minus"></i></button>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                    $(this).closest('.essentialsAllowanceContainer').find('.essentialsAllowanceTable').append(
                        newRow);
                });

                $('body').on('click', '.allowance_remove_tr', function() {
                    $(this).closest('.essentialsAllowance').remove();
                });

                $('body').on('click', '.essentialsDeductionAddRow', function() {
                    var newRow = '<tr class="essentialsDeduction">' +
                        '<td>' +
                        '<div class="row">' +
                        '<div class="col-md-6">' +
                        '<select name="deductions[]" class="form-control pull-left" style="height:40px"></select>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<input type="text" name="deductions_amount[]" class="form-control pull-left" style="height:40px" placeholder="{{ __('essentials::lang.amount') }}">' +
                        '</div>' +
                        '<div class="col-md-1 button_container">' +
                        '<button type="button" class="btn btn-danger btn-xs deduction_remove_tr" ><i class="fa fa-minus"></i></button>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';

                    $(this).closest('.essentialsDeductionContainer').find('.essentialsDeductionTable').append(
                        newRow);
                });

                $('body').on('click', '.deduction_remove_tr', function() {
                    $(this).closest('.essentialsDeduction').remove();
                });
                initSalaries();

                function initSalaries() {

                    $('.payroll_box').each(function() {
                        var payrollContainer = $(this);
                        var initialTotal = parseFloat(payrollContainer.find('.total_salary').text().replace(
                            /[^0-9.-]+/g,
                            "")) || 0;

                        var total = initialTotal;

                        init_total_payrolls += total;
                        var formattedTotal = formatter.format(total);

                        // Update the total salary display within the same payroll container
                        payrollContainer.find('.total_salary_h3').text(formattedTotal);
                        // updateTotalSalary($(this).find('input[name="allowances_amount[]"]:first'));
                    });
                    $('.total_payrolls').text('@lang('agent.total_payrolls'): ' + formatter.format(init_total_payrolls));
                }




            });
        </script>
    @endsection
