@extends('layouts.app')

@section('title', __('accounting::lang.trial_balance'))

@section('content')


    {{-- @include('accounting::layouts.nav') --}}

    <section class="content-header">
        <h1>@lang('accounting::lang.trial_balance')</h1>
    </section>

    <section class="content container">

        <div class="row">
            <div class="box-body">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('date_range_filter', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'readonly',
                                'id' => 'date_range_filter',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('all_accounts', __('accounting::lang.account') . ':') !!}
                        {!! Form::select(
                            'account_filter',
                            isset($type_label) ? [$type_label['GLC'] => $type_label['label']] : [],
                            isset($type_label) ? $type_label['GLC'] : null,
                            [
                                'class' => 'form-control accounts-dropdown',
                                'style' => 'width:100%',
                                'id' => 'account_filter',
                            ],
                        ) !!}
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <div class="checkbox">
                        {!! Form::checkbox('with_zero_balances', 1, $with_zero_balances, [
                            'class' => 'input-icheck',
                            'id' => 'with_zero_balances',
                        ]) !!} {{ __('accounting::lang.with_zero_balances') }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="radio">
                        {!! Form::radio('aggregated', 1, $aggregated, [
                            'class' => 'input-icheck',
                            'id' => 'aggregated',
                        ]) !!} {{ __('accounting::lang.aggregated') }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="radio">
                        {!! Form::radio('detailed', 1, $aggregated ? false : true, [
                            'class' => 'input-icheck',
                            'id' => 'detailed',
                        ]) !!} {{ __('accounting::lang.detailed') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-md-offset-2 col-lg-12 col-md-offset-0">

            <div class="box box-warning">
                <div class="box-header with-border text-center">
                    <h2 class="box-title">@lang('accounting::lang.trial_balance')</h2>
                    <p>{{ @format_date($start_date) }} ~ {{ @format_date($end_date) }}</p>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-stripped" id="accounts-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th colspan="2">@lang('accounting::lang.autoMigration.opening_balance')</th>
                                    <th colspan="2">@lang('accounting::lang.accounting_transactions')</th>
                                    <th colspan="2">@lang('accounting::lang.closing_balance')</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th>@lang('accounting::lang.debit')</th>
                                    <th>@lang('accounting::lang.credit')</th>
                                    <th>@lang('accounting::lang.debit')</th>
                                    <th>@lang('accounting::lang.credit')</th>
                                    <th>@lang('accounting::lang.debit')</th>
                                    <th>@lang('accounting::lang.credit')</th>
                                </tr>
                            </thead>

                            @php
                                $total_debit = 0;
                                $total_credit = 0;
                                $total_credit_opening_balance = 0;
                                $total_debit_opening_balance = 0;
                                $total_closing_debit_balance = 0;
                                $total_closing_credit_balance = 0;
                            @endphp

                            <tbody>
                                @foreach ($accounts as $account)
                                    @php
                                        $total_credit_opening_balance += $account->credit_opening_balance;
                                        $total_debit_opening_balance += $account->debit_opening_balance;
                                        $total_debit += $account->debit_balance;
                                        $total_credit += $account->credit_balance;

                                        $closing_debit_balance =
                                            $account->debit_opening_balance + $account->debit_balance;
                                        $closing_credit_balance =
                                            $account->credit_opening_balance + $account->credit_balance;
                                        $closing_balance = $closing_credit_balance - $closing_debit_balance;

                                        if ($closing_balance >= 0) {
                                            $total_closing_credit_balance += $closing_balance;
                                        } else {
                                            $total_closing_debit_balance += abs($closing_balance);
                                        }
                                    @endphp
                                    <tr>
                                        @if (Lang::has('accounting::lang.' . $account->name))
                                            <td>@lang('accounting::lang.' . $account->name)</td>
                                        @else
                                            <td>{{ $account->name }}</td>
                                        @endif
                                        <td>
                                            @format_currency($account->debit_opening_balance)
                                        </td>
                                        <td>
                                            @format_currency($account->credit_opening_balance)
                                        </td>
                                        <td>
                                            @format_currency($account->debit_balance)
                                        </td>
                                        <td>
                                            @format_currency($account->credit_balance)
                                        </td>
                                        <td>
                                            @if ($closing_balance < 0)
                                                @format_currency(abs($closing_balance))
                                            @else
                                                @format_currency(0)
                                            @endif
                                        </td>
                                        <td>
                                            @if ($closing_balance >= 0)
                                                @format_currency($closing_balance)
                                            @else
                                                @format_currency(0)
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="total_credit">@format_currency($total_debit_opening_balance)</th>
                                    <th class="total_debit">@format_currency($total_credit_opening_balance)</th>
                                    <th class="total_debit">@format_currency($total_debit)</th>
                                    <th class="total_credit">@format_currency($total_credit)</th>
                                    <th class="total_debit">@format_currency($total_closing_debit_balance)</th>
                                    <th class="total_credit">@format_currency($total_closing_credit_balance)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>

    </section>


@stop

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {

            $('#account_filter').change(function() {
                account = $(this).val();
                url = base_path + '/accounting/reports/trial-balance/' + account;
                window.location = url;
            })

            $('#with_zero_balances').on('ifChecked ifUnchecked', function(event) {

                var with_zero_balances = (event.type === 'ifChecked') ? 1 : 0;

                var currentUrl = window.location.href;

                var url = new URL(currentUrl);

                url.searchParams.set('with_zero_balances', with_zero_balances);

                window.location.href = url.href;
            });

            $('input[name="detailed"]').on('ifChecked', function(event) {
                var selectedOption = $(this).val();

                var currentUrl = window.location.href;

                var url = new URL(currentUrl);

                url.searchParams.delete('aggregated');

                window.location.href = url.href;
            });

            $('input[name="aggregated"]').on('ifChecked', function(event) {
                var selectedOption = $(this).val();

                var currentUrl = window.location.href;

                var url = new URL(currentUrl);

                url.searchParams.set('aggregated', selectedOption);

                window.location.href = url.href;
            });


            $("select.accounts-dropdown").select2({
                placeholder: "Select an option",
                ajax: {
                    url: '{{ route('primary-accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data
                        };
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


            dateRangeSettings.startDate = moment('{{ $start_date }}');
            dateRangeSettings.endDate = moment('{{ $end_date }}');

            $('#date_range_filter').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#date_range_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    apply_filter();
                }
            );
            $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
                $('#date_range_filter').val('');
                apply_filter();
            });

            function apply_filter() {
                var start = '';
                var end = '';

                if ($('#date_range_filter').val()) {
                    start = $('input#date_range_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#date_range_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }

                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('start_date', start);
                urlParams.set('end_date', end);
                window.location.search = urlParams;
            }
            $('#accounts-table').DataTable();
        });
    </script>

@stop
