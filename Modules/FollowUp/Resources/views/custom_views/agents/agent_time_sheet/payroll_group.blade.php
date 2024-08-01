@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('agent.time_sheet_for')</span> {{ $date }}
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => route('followup.agentTimeSheet.submitTmeSheet'),
                'method' => 'post',
                'id' => 'add_payroll_step1',
            ]) !!}
            {!! Form::hidden('payroll_group_name', strip_tags($group_name)) !!}
            {!! Form::hidden('project_id', $project_id) !!}
            {!! Form::hidden('action', $action) !!}
            @if ($action === 'edit')
                {!! Form::hidden('timesheet_group_id', $id) !!}
            @endif
            <div class="table-responsive">
                <div style="margin-bottom: 10px;">
                    <div class="col-md-12">
                        <div class="col-md-1">
                            <input type="hidden" name="totals" id="totals">
                            <input type="hidden" name="ids" id="ids"> <!-- Example hidden input -->
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i>
                                @lang('worker.submit')
                            </button>
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('payroll_group_status', __('sale.status') . ':*') !!}
                            @show_tooltip(__('essentials::lang.group_status_tooltip'))
                            {!! Form::select('payroll_group_status', ['draft' => __('sale.draft'), 'final' => __('sale.final')], null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width: 100%;',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                            <p class="help-block text-muted">@lang('essentials::lang.payroll_cant_be_deleted_if_final')</p>
                        </div>
                        <div class="col-md-4">
                            <h4 style="color: red;" class="total_payrolls"></h4>
                            {!! Form::hidden('total_payrolls', null, [
                                'id' => 'total_payrolls',
                            ]) !!}
                            {!! Form::hidden('transaction_date', $date) !!}
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br>
                </div>
                <table class="table table-bordered table-striped" id="workers_table_timesheet"
                    style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 100px;">@lang('essentials::lang.company')</th>
                            <th style="width: 100px;">@lang('worker.name')</th>
                            <th style="width: 100px;">@lang('worker.nationality')</th>
                            <th style="width: 100px;">@lang('worker.eqama_number')</th>
                            <th style="width: 100px;">@lang('worker.sponser')</th>
                            <th style="width: 100px;">@lang('worker.wd')</th>
                            <th style="width: 100px;">@lang('worker.basic')</th>
                            <th style="width: 100px;">@lang('worker.monthly_cost')</th>
                            <th style="width: 100px;">@lang('worker.housing')</th>
                            <th style="width: 100px;">@lang('worker.transport')</th>
                            <th style="width: 100px;">@lang('worker.other_allowances')</th>
                            <th style="width: 100px;">@lang('worker.total_salary')</th>
                            <th style="width: 100px;">@lang('worker.absence_day')</th>
                            <th style="width: 100px;">@lang('worker.absence_amount')</th>
                            <th style="width: 100px;">@lang('worker.other_deduction')</th>
                            <th style="width: 100px;">@lang('worker.over_time_h')</th>
                            <th style="width: 100px;">@lang('worker.over_time')</th>
                            <th style="width: 100px;">@lang('worker.other_addition')</th>
                            <th style="width: 100px;">@lang('worker.cost2')</th>
                            <th style="width: 100px;">@lang('worker.invoice_value')</th>
                            <th style="width: 100px;">@lang('worker.vat')</th>
                            <th style="width: 100px;">@lang('worker.total')</th>

                            <th style="width: 100px;">@lang('worker.deductions')</th>
                            <th style="width: 100px;">@lang('worker.additions')</th>
                            <th style="width: 100px;">@lang('worker.final_salary')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payrolls as $index => $payroll)
                            <tr class="payroll_row">

                                <td name="id">{{ $payroll['id'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][id]', $payroll['id']) !!}
                                </td>
                                <td name="company">{{ $payroll['company'] ?? null }}
                                    {!! Form::hidden('payrolls[' . $index . '][company]', $payroll['company'] ?? null) !!}
                                </td>
                                <td name="name">{{ $payroll['name'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][name]', $payroll['name']) !!}
                                </td>
                                <td name="nationality">{{ $payroll['nationality'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][nationality]', $payroll['nationality']) !!}
                                </td>
                                <td name="residency">{{ $payroll['residency'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][residency]', $payroll['residency']) !!}
                                </td>
                                <td name="sponser">{{ $payroll['sponser'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][sponser]', $payroll['sponser']) !!}</td>
                                </td>
                                <td name="wd" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="wd">{{ $payroll['wd'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][wd]', $payroll['wd'], [
                                        'data-index' => $index,
                                        'data-field' => 'wd',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="basic">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="basic">{{ $payroll['basic'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][basic]', $payroll['basic'], [
                                        'data-index' => $index,
                                        'data-field' => 'basic',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="monthly_cost">{{ $payroll['monthly_cost'] }}
                                    {!! Form::hidden('payrolls[' . $index . '][monthly_cost]', $payroll['monthly_cost'], [
                                        'data-index' => $index,
                                        'data-field' => 'monthly_cost',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="housing" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="housing">{{ $payroll['housing'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][housing]', $payroll['housing'], [
                                        'data-index' => $index,
                                        'data-field' => 'housing',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="transport" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="transport">{{ $payroll['transport'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][transport]', $payroll['transport'], [
                                        'data-index' => $index,
                                        'data-field' => 'transport',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="other_allowances" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="other_allowances">{{ $payroll['other_allowances'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][other_allowances]', $payroll['other_allowances'], [
                                        'data-index' => $index,
                                        'data-field' => 'other_allowances',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="total_salary" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="total_salary">{{ $payroll['total_salary'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][total_salary]', $payroll['total_salary'], [
                                        'data-index' => $index,
                                        'data-field' => 'total_salary',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>


                                <td name="absence_day" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="absence_day">{{ $payroll['absence_day'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][absence_day]', $payroll['absence_day'], [
                                        'data-index' => $index,
                                        'data-field' => 'absence_day',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="absence_amount" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="absence_amount">{{ $payroll['absence_amount'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][absence_amount]', $payroll['absence_amount'], [
                                        'data-index' => $index,
                                        'data-field' => 'absence_amount',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="other_deduction" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="other_deduction">{{ $payroll['other_deduction'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][other_deduction]', $payroll['other_deduction'], [
                                        'data-index' => $index,
                                        'data-field' => 'other_deduction',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="over_time_h" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="over_time_h">{{ $payroll['over_time_h'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][over_time_h]', $payroll['over_time_h'], [
                                        'data-index' => $index,
                                        'data-field' => 'over_time_h',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>
                                <td name="over_time" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="over_time">{{ $payroll['over_time'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][over_time]', $payroll['over_time'], [
                                        'data-index' => $index,
                                        'data-field' => 'over_time',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>


                                <td name="other_addition" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="other_addition">{{ $payroll['other_addition'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][other_addition]', $payroll['other_addition'], [
                                        'data-index' => $index,
                                        'data-field' => 'other_addition',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="cost2" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="cost2">{{ $payroll['cost2'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][cost2]', $payroll['cost2'], [
                                        'data-index' => $index,
                                        'data-field' => 'cost2',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="invoice_value" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="invoice_value">{{ $payroll['invoice_value'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][invoice_value]', $payroll['invoice_value'], [
                                        'data-index' => $index,
                                        'data-field' => 'invoice_value',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="vat" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="vat">{{ $payroll['vat'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][vat]', $payroll['vat'], [
                                        'data-index' => $index,
                                        'data-field' => 'vat',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="total" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="total">{{ $payroll['total'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][total]', $payroll['total'], [
                                        'data-index' => $index,
                                        'data-field' => 'total',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>








                                <td name="deductions" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="deductions">{{ $payroll['deductions'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][deductions]', $payroll['deductions'], [
                                        'data-index' => $index,
                                        'data-field' => 'deductions',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="additions" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="additions">{{ $payroll['additions'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][additions]', $payroll['additions'], [
                                        'data-index' => $index,
                                        'data-field' => 'additions',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                                <td name="final_salary" class="editable">
                                    <span contenteditable="true" data-index="{{ $index }}"
                                        data-field="final_salary">{{ $payroll['final_salary'] }}</span>
                                    {!! Form::hidden('payrolls[' . $index . '][final_salary]', $payroll['final_salary'], [
                                        'data-index' => $index,
                                        'data-field' => 'final_salary',
                                        'class' => 'form-hidden',
                                    ]) !!}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
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
            $('td.editable span[contenteditable="true"]').on('input', function() {
                var index = $(this).data('index');
                var field = $(this).data('field');
                var newValue = $(this).text();

                $('input.form-hidden[data-index="' + index + '"][data-field="' + field + '"]').val(
                    newValue);

                updateAbsenceAmount(index);
                updateOverTime(index);
                updateTotalSalary(index);
                updateCost2(index);
                updateInvoiceValue(index);
                updateVat(index);
                updateTotal(index);
                updateDeductions(index);
                updateAdditions(index);
                updateFinalSalary(index);
                updateTotalPayrolls();
            });
        });

        function updateTotalPayrolls() {
            var formatter = new Intl.NumberFormat('en-US', {
                style: 'decimal',
            });
            var total_payrolls = 0;
            $("input.form-hidden[data-field='final_salary']").each(function() {
                total_payrolls += parseFloat($(this).val()) || 0;
            });
            $('.total_payrolls').text('@lang('agent.total_payrolls'): ' + formatter.format(total_payrolls));
            $('#total_payrolls').val(total_payrolls);
        }

        function initializeCalculations() {
            $('.payroll_row').each(function() {
                var index = $(this).find('.editable span[contenteditable="true"]').first().data('index');
                if (index !== undefined) {
                    updateAbsenceAmount(index);
                    updateOverTime(index);
                    updateCost2(index);
                    updateInvoiceValue(index);
                    updateVat(index);
                    updateTotal(index);
                    updateTotalSalary(index);
                    updateDeductions(index);
                    updateAdditions(index);
                    updateFinalSalary(index);
                    updateTotalPayrolls();
                }
            });
        }

        function updateAbsenceAmount(index) {
            var absence_day = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='absence_day']")
                .val()) || 0;
            var basic = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='monthly_cost']").val()) ||
                0;
            var other_allowances = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_allowances']").val()) || 0;
            var absence_amount = absence_day * (basic + other_allowances) / 30;
            $("span[data-index='" + index + "'][data-field='absence_amount']").text(absence_amount.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='absence_amount']").val(absence_amount.toFixed(0));
        }

        function updateOverTime(index) {
            var total_salary = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='total_salary']")
                .val()) || 0;
            var basic = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='monthly_cost']").val()) ||
                0;
            var over_time_h = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='over_time_h']")
                .val()) || 0;
            var over_time = ((total_salary / 30 / 8) + (basic / 30 / 16)) * over_time_h;
            $("span[data-index='" + index + "'][data-field='over_time']").text(over_time.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='over_time']").val(over_time.toFixed(0));
        }

        function updateTotalSalary(index) {
            var basic = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='monthly_cost']").val()) ||
                0;
            var other_allowances = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_allowances']").val()) || 0;
            var housing = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='housing']").val()) || 0;
            var transport = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='transport']").val()) ||
                0;
            var total_salary = basic + other_allowances + housing + transport;
            $("span[data-index='" + index + "'][data-field='total_salary']").text(total_salary.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='total_salary']").val(total_salary.toFixed(0));
        }

        function updateCost2(index) {
            var monthly_cost = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='monthly_cost']")
                .val()) || 0;
            var wd = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='wd']").val()) || 0;
            var absence_amount = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='absence_amount']")
                .val()) || 0;
            var over_time = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='over_time']").val()) ||
                0;
            var other_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_deduction']").val()) || 0;
            var other_addition = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='other_addition']")
                .val()) || 0;

            var base_cost = (monthly_cost / 30) * wd;
            var cost2 = base_cost + over_time + other_addition - absence_amount - other_deduction;

            $("span[data-index='" + index + "'][data-field='cost2']").text(cost2.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='cost2']").val(cost2.toFixed(0));
        }

        function updateInvoiceValue(index) {
            var cost2 = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='cost2']").val()) || 0;
            var other_addition = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='other_addition']")
                .val()) || 0;
            var absence_amount = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='absence_amount']")
                .val()) || 0;
            var other_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_deduction']").val()) || 0;
            var over_time = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='over_time']").val()) ||
                0;
            var invoice_value = cost2;
            $("span[data-index='" + index + "'][data-field='invoice_value']").text(invoice_value.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='invoice_value']").val(invoice_value.toFixed(0));
        }

        function updateVat(index) {
            var monthly_cost = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='monthly_cost']")
                .val()) || 0;
            var vat = monthly_cost * 0.15;
            $("span[data-index='" + index + "'][data-field='vat']").text(vat.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='vat']").val(vat.toFixed(0));
        }

        function updateTotal(index) {
            var invoice_value = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='invoice_value']")
                .val()) || 0;
            var total = invoice_value * 1.15;
            $("span[data-index='" + index + "'][data-field='total']").text(total.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='total']").val(total.toFixed(0));
        }

        function updateDeductions(index) {
            var absence_amount = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='absence_amount']")
                .val()) || 0;
            var other_deduction = parseFloat($("input.form-hidden[data-index='" + index +
                "'][data-field='other_deduction']").val()) || 0;
            var deductions = absence_amount + other_deduction;
            $("span[data-index='" + index + "'][data-field='deductions']").text(deductions.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='deductions']").val(deductions.toFixed(0));
        }

        function updateAdditions(index) {
            var over_time = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='over_time']").val()) ||
                0;
            var other_addition = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='other_addition']")
                .val()) || 0;
            var additions = over_time + other_addition;
            $("span[data-index='" + index + "'][data-field='additions']").text(additions.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='additions']").val(additions.toFixed(0));
        }

        function updateFinalSalary(index) {
            var total_salary = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='total_salary']")
                .val()) || 0;
            var wd = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='wd']").val()) || 0;
            var additions = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='additions']").val()) ||
                0;
            var deductions = parseFloat($("input.form-hidden[data-index='" + index + "'][data-field='deductions']")
                .val()) || 0;
            var final_salary = ((total_salary / 30) * wd) + additions - deductions;
            $("span[data-index='" + index + "'][data-field='final_salary']").text(final_salary.toFixed(0));
            $("input.form-hidden[data-index='" + index + "'][data-field='final_salary']").val(final_salary.toFixed(0));
        }

        initializeCalculations();
    </script>
@endsection
