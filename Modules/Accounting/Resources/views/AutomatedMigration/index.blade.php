@extends('layouts.app')

@section('title', __('accounting::lang.automatedMigration'))

@section('content')

    @include('accounting::layouts.nav')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('accounting::lang.automatedMigration')</h1>
    </section>
    <section class="content">

        {!! Form::open([
            'url' => action('\Modules\Accounting\Http\Controllers\JournalEntryController@store'),
            'method' => 'post',
            'id' => 'journal_add_form',
        ]) !!}

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                {{-- <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                        @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                        {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
                        <div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
                            {!! Form::text('journal_date', @format_datetime('now'), ['class' => 'form-control datetimepicker', 'readonly', 'required']); !!}
                        </div>
                    </div>
                </div> --}}

            </div>

            <div class="row" style="margin: 0px 0px 30px 0px">

                {{-- 
                <div class="col-sm-3">

                    {!! Form::label('account_sub_type', __('نوع العملية') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                    <select class="form-control" name="account_sub_type_id" id="account_sub_type" style="padding: 3px" required>
                        <option value="">@lang('messages.please_select')</option>
                        <option value="">@lang('accounting::lang.autoMigration.sales_bill')</option>
                    </select>
                </div> --}}

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('name_ar', __('اسم الترحيل') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                        {!! Form::text('ar_name', '', [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('اسم الترحيل'),
                            'id' => 'name_ar',
                        ]) !!}
                    </div>
                </div>


                <div class="col-sm-3">

                    {!! Form::label('account_sub_type', __('نوع العملية') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                    <select class="form-control" name="account_sub_type_id" id="account_sub_type"style="padding: 3px" required>
                        <option value="">@lang('messages.please_select')</option>
                        <option value="">@lang('accounting::lang.autoMigration.sell')</option>
                        <option value="">@lang('accounting::lang.autoMigration.sell_return')</option>
                        <option value="">@lang('accounting::lang.autoMigration.opening_stock')</option>
                        <option value="">@lang('accounting::lang.autoMigration.purchase_')</option>
                        <option value="">@lang('accounting::lang.autoMigration.purchase_order')</option>
                        <option value="">@lang('accounting::lang.autoMigration.purchase_return')</option>

                        <option value="">@lang('accounting::lang.autoMigration.expens_')</option>
                        <option value="">@lang('accounting::lang.autoMigration.sell_transfer')</option>
                        <option value="">@lang('accounting::lang.autoMigration.purchase_transfer')</option>
                        <option value="">@lang('accounting::lang.autoMigration.payroll')</option>
                        <option value="">@lang('accounting::lang.autoMigration.opening_balance')</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    {!! Form::label('account_sub_type', __('حالة الدفع') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                    <select class="form-control" name="account_sub_type_id" id="account_sub_type" style="padding: 3px" required>
                        <option value="">@lang('messages.please_select')</option>
                        <option value="">@lang('accounting::lang.autoMigration.paid')</option>
                        <option value="">@lang('accounting::lang.autoMigration.due')</option>
                        <option value="">@lang('accounting::lang.autoMigration.partial')</option>
                    </select>
                </div>

                <div class="col-sm-3">

                    {!! Form::label('account_sub_type', __('طريقة الدفع') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                    <select class="form-control" name="account_sub_type_id" id="account_sub_type"style="padding: 3px" required>
                        <option value="">@lang('messages.please_select')</option>
                        <option value="">@lang('accounting::lang.autoMigration.cash')</option>
                        <option value="">@lang('accounting::lang.autoMigration.card')</option>
                        <option value="">@lang('accounting::lang.autoMigration.bank_transfer')</option>
                        <option value="">@lang('accounting::lang.autoMigration.cheque')</option>
                    </select>
                </div>

                <div class="col-sm-3">

                </div>
                <div class="divider py-1 bg-dark">
                    <hr>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <h4 style="text-align: start">@lang('accounting::lang.first_journal')</h4>

                        <table class="table table-bordered table-striped hide-footer" id="journal_table1">
                            <thead>
                                <tr>
                                    <th class="col-md-1">#
                                    </th>
                                    <th class="col-md-3">@lang('accounting::lang.account')</th>
                                    <th class="col-md-3">@lang('accounting::lang.debit') / @lang('accounting::lang.credit')</th>
                                    <th class="col-md-3">@lang('accounting::lang.amount')</th>
                                    {{-- <th class="col-md-3">@lang('accounting::lang.credit')</th> --}}
                                </tr>
                            </thead>
                            <tbody id="tbody1">
                                <tr>
                                    <td>
                                        <button class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="1"
                                            name="1" value="1"
                                            style="    background: transparent; border: 0px;"></button>
                                    </td>
                                    <td>
                                        {!! Form::select('account_id[' . 1 . ']', [], null, [
                                            'class' => 'form-control accounts-dropdown account_id',
                                            'placeholder' => __('messages.please_select'),
                                            'style' => 'width: 100%; padding:3px;',
                                        ]) !!}
                                    </td>

                                    <td>

                                        {{-- <div class="row"> --}}
                                        {{-- <div style="width: 100%"> --}}
                                        <label class="radio-inline">
                                            <input value="debit" type="radio" name="type01" checked>@lang('accounting::lang.debtor')
                                        </label>
                                        <label class="radio-inline">
                                            <input value="credit" type="radio" name="type01">@lang('accounting::lang.creditor')
                                        </label>
                                        {{-- </div> --}}
                                        {{-- </div> --}}
                                        {{-- {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']) !!} --}}

                                    </td>
                                    {{-- 
                                    <td>
                                        {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']) !!}

                                    </td> --}}
                                    <td>
                                        <select class="form-control" name="account_sub_type_id"
                                            id="account_sub_type"style="padding: 3px" required>
                                            <option value="">@lang('accounting::lang.autoMigration.final_total')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.total_before_tax')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.tax_amount')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.shipping_charges')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.discount_amount')</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th class="text-center">@lang('accounting::lang.total')</th>
                                    <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                                    <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <h4 style="text-align: start">@lang('accounting::lang.second_journal')</h4>

                        <table class="table table-bordered table-striped hide-footer" id="journal_table2">
                            <thead>
                                <tr>
                                    <th class="col-md-1">#
                                    </th>
                                    <th class="col-md-3">@lang('accounting::lang.account')</th>
                                    <th class="col-md-3">@lang('accounting::lang.debit') / @lang('accounting::lang.credit')</th>
                                    <th class="col-md-3">@lang('accounting::lang.amount')</th>
                                    {{-- <th class="col-md-3">@lang('accounting::lang.credit')</th> --}}
                                </tr>
                            </thead>
                            <tbody id="tbody2">
                                <tr>
                                    <td><button class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="1"
                                            name="2" value="2"
                                            style="    background: transparent; border: 0px;"></button>
                                    </td>
                                    <td>
                                        {!! Form::select('account_id[' . 1 . ']', [], null, [
                                            'class' => 'form-control accounts-dropdown account_id',
                                            'placeholder' => __('messages.please_select'),
                                            'style' => 'width: 100%; padding:3px;',
                                        ]) !!}
                                    </td>

                                    <td>

                                        {{-- <div class="row"> --}}
                                        {{-- <div style="width: 100%"> --}}
                                        <label class="radio-inline">
                                            <input value="debit" type="radio" name="type02" checked>@lang('accounting::lang.debtor')
                                        </label>
                                        <label class="radio-inline">
                                            <input value="credit" type="radio" name="type02">@lang('accounting::lang.creditor')
                                        </label>
                                        {{-- </div> --}}
                                        {{-- </div> --}}
                                        {{-- {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']) !!} --}}

                                    </td>

                                    {{-- <td>
                                        {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']) !!}

                                    </td> --}}
                                    <td>
                                        <select class="form-control" name="account_sub_type_id"
                                            id="account_sub_type"style="padding: 3px" required>
                                            <option value="">@lang('accounting::lang.autoMigration.final_total')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.total_before_tax')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.tax_amount')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.shipping_charges')</option>
                                            <option value="">@lang('accounting::lang.autoMigration.shipping_charges')</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th class="text-center">@lang('accounting::lang.total')</th>
                                    <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span>
                                    </th>
                                    <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>



                <div class="row">
                    <div class="col-sm-12" style="display: flex;
                    justify-content: center;">
                        <button type="button" style="    width: 50%;
                        border-radius: 28px;"
                            class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
                    </div>
                </div>
            @endcomponent

            {!! Form::close() !!}
    </section>

@stop


@section('javascript')
    @include('accounting::accounting.common_js')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.journal_add_btn').click(function(e) {
                //e.preventDefault();
                calculate_total();

                var is_valid = true;

                //check if same or not
                if ($('.total_credit_hidden').val() != $('.total_debit_hidden').val()) {
                    is_valid = false;
                    alert("@lang('accounting::lang.credit_debit_equal')");
                }

                //check if all account selected or not
                $('table > tbody  > tr').each(function(index, tr) {
                    var credit = __read_number($(tr).find('.credit'));
                    var debit = __read_number($(tr).find('.debit'));

                    if (credit != 0 || debit != 0) {
                        if ($(tr).find('.account_id').val() == '') {
                            is_valid = false;
                            alert("@lang('accounting::lang.select_all_accounts')");
                        }
                    }
                });

                if (is_valid) {
                    $('form#journal_add_form').submit();
                }

                return is_valid;
            });

            $('.credit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });
        });

        function calculate_total() {
            var total_credit = 0;
            var total_debit = 0;
            $('table > tbody  > tr').each(function(index, tr) {
                var credit = __read_number($(tr).find('.credit'));
                total_credit += credit;

                var debit = __read_number($(tr).find('.debit'));
                total_debit += debit;
            });

            $('.total_credit_hidden').val(total_credit);
            $('.total_debit_hidden').val(total_debit);

            $('.total_credit').text(__currency_trans_from_en(total_credit));
            $('.total_debit').text(__currency_trans_from_en(total_debit));
        }
        $(document).on('click', '.fa-plus-square', function() {
            var tbode_number = $(this).val();
            let counter = $('#journal_table' + tbode_number + ' tr').length - 1;
            $('#tbody' + tbode_number).append(
                '<tr><td><button class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="' +
                counter +
                '" name="' + tbode_number + '" value="' + tbode_number +
                '" style="background: transparent; border: 0px;"></button></td><td><select class="form-control accounts-dropdown account_id" style="width: 100%;" name="account_id[' +
                counter +
                ']"><option selected="selected" value="">يرجى الاختيار</option></select> </td> <td><label class="radio-inline"><input value="debit" type="radio" name="type' +
                tbode_number + '' + counter +
                '" checked>@lang('accounting::lang.debtor')</label><label class="radio-inline"><input value="credit" type="radio" name="type' +
                tbode_number + '' + counter +
                '">@lang('accounting::lang.creditor')</label></td><td><select class="form-control" name="account_sub_type_id"id="account_sub_type"style="padding: 3px" required><option value="">@lang('accounting::lang.autoMigration.final_total')</option><option value="">@lang('accounting::lang.autoMigration.total_before_tax')</option><option value="">@lang('accounting::lang.autoMigration.tax_amount')</option><option value="">@lang('accounting::lang.autoMigration.shipping_charges')</option><option value="">@lang('accounting::lang.autoMigration.discount_amount')</option></select></td></tr>'
            )
            $('select[name="account_id[' + counter + ']"]').select2({
                ajax: {
                    url: '{{ route('accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data
                        }
                    },
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.html;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });
            $('.credit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });

        })
    </script>
@endsection
