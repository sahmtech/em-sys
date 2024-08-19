@extends('layouts.app')

@section('title', __('accounting::lang.trial_balance'))

@section('content')


    {{-- @include('accounting::layouts.nav') --}}

    <section class="content-header">
        <h1>@lang('accounting::lang.trial_balance')</h1>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('all_levels', __('accounting::lang.account_level') . ':') !!}
                                {!! Form::select('level_filter', $levelsArray, null, [
                                    'class' => 'form-control',
                                    'style' => 'width:100%',
                                    'id' => 'level_filter',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('with_zero_balances', __('accounting::lang.balance') . ':') !!}
                                <select class="form-control" name="with_zero_balances" id='with_zero_balances'
                                    style="padding: 2px;">
                                    <option value="0" selected>{{ __('accounting::lang.without_zero_balances') }}
                                    </option>
                                    <option value="1">{{ __('accounting::lang.with_zero_balances') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('classification', __('accounting::lang.classification') . ':') !!}
                                <select class="form-control" name="classification" id='classification' style="padding: 2px;">
                                    <option value="0" selected>{{ __('accounting::lang.detailed') }}</option>
                                    <option value="1">{{ __('accounting::lang.aggregated') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">


                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('start_date_filter', __('accounting::lang.from_date') . ':') !!}
                                {!! Form::date('start_date_filter', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.select_start_date'),
                                    'id' => 'start_date_filter',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('end_date_filter', __('accounting::lang.to_date') . ':') !!}
                                {!! Form::date('end_date_filter', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.select_end_date'),
                                    'id' => 'end_date_filter',
                                ]) !!}
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-11">
                            <div class="form-group">
                                {!! Form::label('choose_fields', __('accounting::lang.account') . ':') !!}
                                {!! Form::select('choose_accounts_select[]', $accounts_array, array_keys($accounts_array), [
                                    'class' => 'form-control select2 ',
                                    'multiple',
                                    'id' => 'choose_accounts_select',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-1 ">
                            <button class="btn btn-primary pull-right btn-flat" onclick="accounts_table.ajax.reload();"
                                style="margin-top: 24px;
                        width: 62px;
                        height: 40px;
                        border-radius: 4px;">تطبيق</button>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border text-center">
                <h2 class="box-title">@lang('accounting::lang.trial_balance')</h2>
                {{-- <p>{{ @format_date($start_date) }} ~ {{ @format_date($end_date) }}</p> --}}
            </div>

            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-stripped table-bordered" id="accounts-table">
                        <thead>
                            <tr>
                                <th colspan="2"></th>
                                <th colspan="2">@lang('accounting::lang.autoMigration.opening_balance')</th>
                                <th colspan="2">@lang('accounting::lang.accounting_transactions')</th>
                                <th colspan="2">@lang('accounting::lang.closing_balance')</th>
                            </tr>
                            <tr>
                                <th>@lang('accounting::lang.number')</th>
                                <th>@lang('accounting::lang.name')</th>
                                <th>@lang('accounting::lang.debit')</th>
                                <th>@lang('accounting::lang.credit')</th>
                                <th>@lang('accounting::lang.debit')</th>
                                <th>@lang('accounting::lang.credit')</th>
                                <th>@lang('accounting::lang.debit')</th>
                                <th>@lang('accounting::lang.credit')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-center">Total:</th>
                                <th id="debitOpeningTotal" class="debit_opening_total"></th>
                                <th id="creditOpeningTotal" class="credit_opening_total"></th>
                                <th id="debitTotal" class="debit_total"></th>
                                <th id="creditTotal" class="credit_total"></th>
                                <th id="closingDebitTotal" class="closing_debit_total"></th>
                                <th id="closingCreditTotal" class="closing_credit_total"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>

    </section>


@stop

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {

            $('#classification').select2();
            $('#with_zero_balances').select2();
            $('#level_filter').select2();


            $('#level_filter,#end_date_filter,#start_date_filter,#with_zero_balances,#classification,#account_filter')
                .on('change',
                    function() {
                        accounts_table.ajax.reload();
                    });

            accounts_table = $('#accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('accounting.trialBalance') }}",
                    data: function(d) {
                        if ($('#start_date_filter').val()) {
                            d.start_date = $('#start_date_filter').val();
                        }
                        if ($('#end_date_filter').val()) {
                            d.end_date = $('#end_date_filter').val();
                        }
                        if ($('#classification').val()) {
                            d.aggregated = $('#classification').val();
                        }
                        if ($('#account_filter').val()) {
                            d.type = $('#account_filter').val();
                        }
                        if ($('#level_filter').val()) {
                            d.level_filter = $('#level_filter').val();
                        }
                        if ($('#with_zero_balances').val()) {
                            d.with_zero_balances = $('#with_zero_balances').val();
                        }
                        if ($('#choose_accounts_select').val()) {
                            d.choose_accounts_select = $('#choose_accounts_select').val();
                        }
                    }
                },
                columns: [{
                        data: 'gl_code',
                        name: 'gl_code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'debit_opening_balance',
                        name: 'debit_opening_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                    {
                        data: 'credit_opening_balance',
                        name: 'credit_opening_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                    {
                        data: 'debit_balance',
                        name: 'debit_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                    {
                        data: 'credit_balance',
                        name: 'credit_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                    {
                        data: 'closing_debit_balance',
                        name: 'closing_debit_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                    {
                        data: 'closing_credit_balance',
                        name: 'closing_credit_balance',
                        render: function(data, type, row) {
                            return __currency_trans_from_en(parseFloat(data));
                        }
                    },
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var debit_opening_total = 0;
                    var credit_opening_total = 0;
                    var debit_total = 0;
                    var credit_total = 0;
                    var closing_debit_total = 0;
                    var closing_credit_total = 0;
                    for (var r in data) {
                        debit_opening_total += data[r].debit_opening_balance ?
                            parseFloat(data[r].debit_opening_balance) : 0;

                        credit_opening_total += data[r].credit_opening_balance ?
                            parseFloat(data[r].credit_opening_balance) : 0;

                        debit_total += data[r].debit_balance ?
                            parseFloat(data[r].debit_balance) : 0;

                        credit_total += $(data[r].credit_balance) ?
                            parseFloat(data[r].credit_balance) : 0;

                        closing_debit_total += data[r].closing_debit_balance ?
                            parseFloat(data[r].closing_debit_balance) : 0;

                        closing_credit_total += data[r].closing_credit_balance ?
                            parseFloat(data[r].closing_credit_balance) : 0;
                    }
                    $('.debit_opening_total').html(__currency_trans_from_en(debit_opening_total));
                    $('.credit_opening_total').html(__currency_trans_from_en(credit_opening_total));
                    $('.debit_total').html(__currency_trans_from_en(debit_total));
                    $('.credit_total').html(__currency_trans_from_en(credit_total));
                    $('.closing_debit_total').html(__currency_trans_from_en(closing_debit_total));
                    $('.closing_credit_total').html(__currency_trans_from_en(closing_credit_total));
                }
            });
        });
    </script>

@stop
