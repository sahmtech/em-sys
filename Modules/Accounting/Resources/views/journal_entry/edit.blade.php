@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

    @include('accounting::layouts.nav')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('accounting::lang.journal_entry') - {{ $journal->ref_no }}</h1>
    </section>
    <section class="content">

        {!! Form::open([
            'url' => action('\Modules\Accounting\Http\Controllers\JournalEntryController@update', $journal->id),
            'method' => 'PUT',
            'id' => 'journal_add_form',
        ]) !!}

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">

                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('journal_date', @format_datetime($journal->operation_date), [
                                'class' => 'form-control datetimepicker',
                                'readonly',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('accounting::lang.additional_notes')) !!}
                        {!! Form::textarea('note', $journal->note, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                </div>
            </div>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="myModalLabel"><i class="fas fa-user-tag"></i> @lang('accounting::lang.select_partner')</h4>
                        </div>
                        <div class="modal-body">

                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#employees">@lang('accounting::lang.employees')</a></li>
                                <li><a data-toggle="tab" href="#customers-suppliers">@lang('accounting::lang.customers_suppliers')</a></li>
                            </ul>

                            <div class="tab-content">
                                <div id="employees" class="tab-pane fade in active">
                                    <div class="col-md-12" style="height: 30vh;padding: 30px;">
                                        <div class="form-group">
                                            <input type="hidden" class="employees_arr" id="employees_arr"
                                                value="{{ $employees }}">

                                            {!! Form::label('parent_id', __('accounting::lang.employees') . '*') !!}
                                            <select class="form-control select2" id="select-employees"
                                                style="width: 100%;padding: 1px;" name="account_primary_type"
                                                id="account_primary_type">
                                                {{-- <option value="">@lang('messages.please_select')</option> --}}
                                                @foreach ($employees as $key => $value)
                                                    <option value="{{ $key }}">
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="customers-suppliers" class="tab-pane fade">
                                    <div class="col-md-12" style="height: 30vh;padding: 30px;">
                                        <div class="form-group">
                                            {!! Form::label('parent_id', __('accounting::lang.customers_suppliers') . '*') !!}
                                            <select class="form-control select2" style="width: 100%;padding: 1px;"
                                                name="select-customers_suppliers" id="select-customers_suppliers">
                                                <option value="">@lang('messages.please_select')</option>
                                                @foreach ($contacts as $contact)
                                                    <option value="{{ $contact['id'] }}">
                                                        {{ $contact['name'] . ' - ' . $contact['supplier_business_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.select')</button>
                        </div>
                    </div> <!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


            <div class="row">
                <div class="col-sm-12">

                    <table class="table table-bordered table-striped hide-footer" id="journal_table">
                        <thead>
                            <tr>
                                <th class="col-md-1">#</th>
                                <th class="col-md-4">@lang('accounting::lang.account')</th>
                                <th class="col-md-2">@lang('accounting::lang.select_partner')</th>
                                <th class="col-md-1">@lang('accounting::lang.debit')</th>
                                <th class="col-md-1">@lang('accounting::lang.credit')</th>
                                <th class="col-md-5">@lang('accounting::lang.additional_notes')</th>

                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 10; $i++)
                                <tr>

                                    @php
                                        $account_id = '';
                                        $debit = '';
                                        $credit = '';
                                        $additional_notes = '';
                                        $default_array = [];
                                        $selected_partner_id = '';
                                        $selected_partner_type = '';
                                        $partner = '';
                                        $partner_type = '';
                                    @endphp

                                    @if (isset($accounts_transactions[$i - 1]))
                                        @php

                                            $account_id = $accounts_transactions[$i - 1]['accounting_account_id'];
                                            $debit =
                                                $accounts_transactions[$i - 1]['type'] == 'debit'
                                                    ? $accounts_transactions[$i - 1]['amount']
                                                    : '';
                                            $credit =
                                                $accounts_transactions[$i - 1]['type'] == 'credit'
                                                    ? $accounts_transactions[$i - 1]['amount']
                                                    : '';
                                            $default_array = [
                                                $account_id => $accounts_transactions[$i - 1]['account']['name'],
                                            ];
                                            $additional_notes =
                                                $accounts_transactions[$i - 1]['additional_notes'] ?? '';

                                            if ($i <= count($accounts_transactions)) {
                                                $selected_partner_id =
                                                    $accounts_transactions[$i - 1]['partner_id'] ?? ' ';
                                                $selected_partner_type =
                                                    $accounts_transactions[$i - 1]['partner_type'] ?? ' ';
                                                if ($selected_partner_id) {
                                                    if ($selected_partner_type === 'employees') {
                                                        $partner = $employees[$selected_partner_id];
                                                    } else {
                                                        $contactsArray = $contacts->toArray();
                                                        $contact = array_column($contactsArray, null, 'id')[
                                                            $selected_partner_id
                                                        ];
                                                        $partner =
                                                            $contact['name'] .
                                                            ' - ' .
                                                            $contact['supplier_business_name'];
                                                    }
                                                    $partner_type =
                                                        $selected_partner_type === 'employees'
                                                            ? __('accounting::lang.employees')
                                                            : __('accounting::lang.customers_suppliers');
                                                }
                                            }

                                        @endphp

                                        {!! Form::hidden('accounts_transactions_id[' . $i . ']', $accounts_transactions[$i - 1]['id']) !!}
                                    @endif

                                    <td>{{ $i }}</td>
                                    <td>
                                        {!! Form::select('account_id[' . $i . ']', $default_array, $account_id, [
                                            'class' => 'form-control accounts-dropdown account_id',
                                            'placeholder' => __('messages.please_select'),
                                            'style' => 'width: 100%;',
                                        ]) !!}
                                    </td>
                                    <th class="col-md-1">
                                        <button type="button" id="{{ $i }}"
                                            class="btn btn-primary open-dialog-btn">@lang('accounting::lang.select_partner')</button>
                                        <input type="text" readonly name="selected_partner[{{ $i }}]"
                                            class="selected_partner" id="selected_partner[{{ $i }}]"
                                            style="background: transparent;border: 0;" value="{{ $partner }}">
                                        <input type="text" readonly name="selected_partner_type[{{ $i }}]"
                                            class="selected_partner_type[{{ $i }}]"
                                            id="selected_partner_type[{{ $i }}]"
                                            style="background: transparent;border: 0;" value="{{ $partner_type }}">
                                        <input type="hidden" readonly name="selected_partner_id[{{ $i }}]"
                                            id="selected_partner_id[{{ $i }}]" class="selected_partner"
                                            value="{{ $selected_partner_id }}">
                                        <input type="hidden" readonly name="selected_partner_type_[{{ $i }}]"
                                            id="selected_partner_type_[{{ $i }}]" class="selected_partner"
                                            value="{{ $selected_partner_type }}">
                                    </th>

                                    <td>
                                        {!! Form::text('debit[' . $i . ']', $debit, ['class' => 'form-control input_number debit']) !!}
                                    </td>

                                    <td>
                                        {!! Form::text('credit[' . $i . ']', $credit, ['class' => 'form-control input_number credit']) !!}
                                    </td>

                                    <td>
                                        {!! Form::text('additional_notes[' . $i . ']', $additional_notes, ['class' => 'form-control additional_notes']) !!}
                                    </td>
                                </tr>
                            @endfor
                        </tbody>

                        <tfoot>
                            <tr>
                                <th></th>
                                <th class="text-center">@lang('accounting::lang.total')</th>
                                <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                                <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
            <input type="hidden" class="row-number" id="row-number">

            <div class="row">
                <div class="col-sm-12">
                    <button type="button"
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
            $('#select-employees').change(function() {
                const id = $('.row-number').val();

                const selectedOption = $(this).find('option:selected');

                const selectedValue = selectedOption.val();
                const selectedText = selectedOption.text().trim();

                $('#selected_partner\\[' + id + '\\]').val(selectedText);
                $('#selected_partner_id\\[' + id + '\\]').val(selectedValue);
                $('#selected_partner_type\\[' + id + '\\]').val("@lang('accounting::lang.employees')");
                $('#selected_partner_type_\\[' + id + '\\]').val("employees");


            });


            $('#select-customers_suppliers').change(function() {
                const id = $('.row-number').val();
                const selectedOption = $(this).find('option:selected');

                const selectedValue = selectedOption.val();
                const selectedText = selectedOption.text().trim();

                $('#selected_partner_id\\[' + id + '\\]').val(selectedValue);
                $('#selected_partner\\[' + id + '\\]').val(selectedText);
                $('#selected_partner_type\\[' + id + '\\]').val("@lang('accounting::lang.customers_suppliers')");
                $('#selected_partner_type_\\[' + id + '\\]').val("customers_suppliers");


            });


            $(document).on('click', '.open-dialog-btn', function() {
                currentRow = $(this).closest('tr');
                const id = this.id;
                console.log(id);
                $('.row-number').val(id);
                $('#myModal').modal('show');
            });

            calculate_total();

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
    </script>
@endsection
