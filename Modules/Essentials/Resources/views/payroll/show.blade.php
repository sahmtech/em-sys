@extends('layouts.app')
@section('title', __('essentials::lang.payroll'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.payroll_for')</span> {{ $user_name ?? '' }} {{ $date }}
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => '',
            ]) !!}
            {!! Form::hidden('payroll_group_name', strip_tags($group_name)) !!}
            <div class="table-responsive">
                <div style="margin-bottom: 10px;">
                    <div class="col-md-12">
                        <div class="col-md-1">
                            <button type="button" class="btn btn-primary" aria-label="Print"
                                onclick="$(this).closest('.content').find('#employees_table_payrolls').printThis();">
                                <i class="fa fa-print"></i> @lang('messages.print')
                            </button>
                        </div>

                    </div>
                    <div class="clearfix"></div>
                    <br>
                </div>
                <table class="table table-bordered table-striped" id="employees_table_payrolls"
                    style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width:100px;">@lang('essentials::lang.name')</th>
                            <th style="width:75px;">@lang('essentials::lang.nationality')</th>
                            <th style="width:100px;">@lang('essentials::lang.identity_card_number')</th>
                            <th style="width:75px;">@lang('essentials::lang.profession')</th>
                            <th style="width:75px;">@lang('essentials::lang.work_days')</th>
                            <th style="width:75px;">@lang('essentials::lang.salary')</th>
                            <th style="width:75px;">@lang('essentials::lang.housing_allowance')</th>
                            <th style="width:75px;">@lang('essentials::lang.transportation_allowance')</th>
                            <th style="width:75px;">@lang('essentials::lang.other_allowance')</th>
                            <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total')</th>
                            <th style="width:75px;">@lang('essentials::lang.violations')</th>
                            <th style="width:75px;">@lang('essentials::lang.absence')</th>
                            <th style="width:75px;">@lang('essentials::lang.other_deductions')</th>
                            <th style="width:75px;">@lang('essentials::lang.loan')</th>
                            <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total_deduction')</th>
                            <th style="width:75px;">@lang('essentials::lang.over_time_hours')</th>
                            <th style="width:75px;">@lang('essentials::lang.additional_addition')</th>
                            <th style="width:75px;">@lang('essentials::lang.other_additions')</th>
                            <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.total_additions')</th>
                            <th style="background-color: rgb(185, 182, 182); width:75px;">@lang('essentials::lang.final_salary')</th>
                            <th style="width:75px;">@lang('essentials::lang.payment_method')</th>
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
                                <td name="name">{{ $payroll['name'] }}
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
                                <td name="identity_card_number">{{ $payroll['identity_card_number'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][identity_card_number]', $payroll['identity_card_number'], [
                                        'data-index' => $index,
                                        'data-field' => 'identity_card_number',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="profession">{{ $payroll['profession'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][profession]', $payroll['profession'], [
                                        'data-index' => $index,
                                        'data-field' => 'profession',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="work_days"> <span contenteditable="false"
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
                                        data-index="{{ $index }}" data-field="total">{{ $payroll['total'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][total]', $payroll['total'], [
                                        'data-index' => $index,
                                        'data-field' => 'total',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="violations"> <span contenteditable="false"
                                        data-index="{{ $index }}"
                                        data-field="violations">{{ $payroll['violations'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][violations]', $payroll['violations'], [
                                        'data-index' => $index,
                                        'data-field' => 'violations',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="absence"> <span contenteditable="false"
                                        data-index="{{ $index }}" data-field="absence">{{ $payroll['absence'] }}</span>
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
                                <td class="editable" name="other_deductions"> <span contenteditable="false"
                                        data-index="{{ $index }}"
                                        data-field="other_deductions">{{ $payroll['other_deductions'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][other_deductions]', $payroll['other_deductions'], [
                                        'data-index' => $index,
                                        'data-field' => 'other_deductions',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="loan"> <span contenteditable="false"
                                        data-index="{{ $index }}" data-field="loan">{{ $payroll['loan'] }}</span>
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
                                <td class="editable" name="over_time_hours"> <span contenteditable="false"
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
                                <td class="editable" name="additional_addition"> <span contenteditable="false"
                                        data-index="{{ $index }}"
                                        data-field="additional_addition">{{ $payroll['additional_addition'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][additional_addition]', $payroll['additional_addition'], [
                                        'data-index' => $index,
                                        'data-field' => 'additional_addition',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="other_additions"> <span contenteditable="false"
                                        data-index="{{ $index }}"
                                        data-field="other_additions">{{ $payroll['other_additions'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][other_additions]', $payroll['other_additions'], [
                                        'data-index' => $index,
                                        'data-field' => 'other_additions',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td style="background-color: rgb(185, 182, 182);" name="total_additions"><span
                                        data-index="{{ $index }}"
                                        data-field="total_additions">{{ $payroll['total_additions'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][total_additions]', $payroll['total_additions'], [
                                        'data-index' => $index,
                                        'data-field' => 'total_additions',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td style="background-color: rgb(185, 182, 182);" name="final_salary"><span
                                        data-index="{{ $index }}"
                                        data-field="final_salary">{{ $payroll['final_salary'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][final_salary]', $payroll['final_salary'], [
                                        'data-index' => $index,
                                        'data-field' => 'final_salary',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="payment_method"> <span contenteditable="false"
                                        data-index="{{ $index }}"
                                        data-field="payment_method">{{ $payroll['payment_method'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][payment_method]', $payroll['payment_method'], [
                                        'data-index' => $index,
                                        'data-field' => 'payment_method',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td class="editable" name="notes"> <span contenteditable="false"
                                        data-index="{{ $index }}" data-field="notes">{{ $payroll['notes'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][notes]', $payroll['notes'], [
                                        'data-index' => $index,
                                        'data-field' => 'notes',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                @lang('essentials::lang.the_total')
                            </td>
                            <td>
                            </td>
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
                            <td name="the_total_other_deductions">
                            </td>
                            <td name="the_total_loan">
                            </td>
                            <td name="the_total_total_deduction">
                            </td>
                            <td name="the_total_over_time_hours">
                            </td>
                            <td name="the_total_additional_addition">
                            </td>
                            <td name="the_total_other_additions">
                            </td>
                            <td name="the_total_total_additions">
                            </td>
                            <td style="  color: red;" name="the_total_final_salary">

                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </div>
            {!! Form::close() !!}
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            // $('td.editable span[contenteditable="false"]').on('input', function() {
            //     var index = $(this).data('index');
            //     var field = $(this).data('field');
            //     var newValue = $(this).text();

            //     $('input.form-hidden[data-index="' + index + '"][data-field="' + field + '"]').val(
            //         newValue);
            //     if (field == 'absence') {
            //         updateAbsenceDeduction(index);
            //     }
            //     if (field == 'over_time_hours') {
            //         updateOverTimeHoursAddition(index);
            //     }

            //     initializeCalculations();
            //     updateFooter();
            // });

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

            // $('td.editable span[contenteditable="false"]').on('input', function() {
            //     var index = $(this).data('index');
            //     var field = $(this).data('field');
            //     var newValue = $(this).text();
            //     updateFooter();

            // });

        });

        function updateAbsenceDeduction(index) {
            var absence = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='absence']").val()) || 0;
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;
            var work_days = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='work_days']").val()) || 0;
            var absence_deduction = absence * (total / work_days / 8);
            $("span[data-index='" + index + "'][data-field='absence_deduction']").text(absence_deduction.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='absence_deduction']").val(absence_deduction
                .toFixed(0));
        }

        function updateOverTimeHoursAddition(index) {
            var over_time_hours = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='over_time_hours']").val()) || 0;
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;
            var work_days = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='work_days']").val()) || 0;
            var over_time_hours_addition = over_time_hours * (total / work_days / 8);
            $("span[data-index='" + index + "'][data-field='over_time_hours_addition']").text(over_time_hours_addition
                .toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='over_time_hours_addition']").val(
                over_time_hours_addition
                .toFixed(0));
        }

        function initializeCalculations(index) {
            $('.payroll_row').each(function() {
                var index = $(this).find('.editable span[contenteditable="false"]').first().data('index');
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
            $("span[data-index='" + index + "'][data-field='total']").text(total.toFixed(0));

            $("input.form-hidden[data-index='" + index + "'][data-field='total']").val(total.toFixed(0));
        }

        function updateTotalDeduction(index) {
            var violations = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='violations']").val()) || 0;

            var absence_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='absence_deduction']").val()) || 0;

            var other_deductions = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_deductions']").val()) || 0;
            var loan = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='loan']").val()) || 0;

            var total_deduction = violations + absence_deduction + other_deductions + loan;
            $("span[data-index='" + index + "'][data-field='total_deduction']").text(total_deduction.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_deduction']").val(total_deduction.toFixed(
                0));
        }

        function updateTotalAdditions(index) {
            var over_time_hours_addition = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='over_time_hours_addition']").val()) || 0;

            var additional_addition = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='additional_addition']").val()) || 0;

            var other_additions = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_additions']").val()) || 0;


            var total_additions = over_time_hours_addition + additional_addition + other_additions;
            $("span[data-index='" + index + "'][data-field='total_additions']").text(total_additions.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_additions']").val(total_additions.toFixed(
                0));
        }

        function updateFinalSalary(index) {
            var total = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total']").val()) || 0;

            var total_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total_deduction']").val()) || 0;

            var total_additions = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='total_additions']").val()) || 0;


            var final_salary = total - total_deduction + total_additions;
            $("span[data-index='" + index + "'][data-field='final_salary']").text(final_salary.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='final_salary']").val(final_salary.toFixed(
                0));
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
            var value = 0;
            $("input.form-hidden[data-field='over_time_hours']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_over_time_hours"]').text(value);
        };

        function updateTheTotalAdditionalAddition() {
            var value = 0;
            $("input.form-hidden[data-field='additional_addition']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_additional_addition"]').text(value);
        };

        function updateTheTotalOtherAdditions() {
            var value = 0;
            $("input.form-hidden[data-field='other_additions']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_other_additions"]').text(value);
        };

        function updateTheTotalTotalAdditions() {
            var value = 0;
            $("input.form-hidden[data-field='total_additions']").each(function() {
                value += parseFloat($(this).val()) || 0;
            });
            $('td[name="the_total_total_additions"]').text(value);
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
