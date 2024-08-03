@extends('layouts.app')

@section('title', __('accounting::lang.trial_balance'))

@section('content')

    @include('accounting::layouts.nav')

    <section class="content">

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
                {!! Form::text('date_range_filter', null, [
                    'placeholder' => __('lang_v1.select_a_date_range'),
                    'class' => 'form-control',
                    'readonly',
                    'id' => 'date_range_filter',
                ]) !!}
            </div>
        </div>

        <div class="col-md-8 col-md-offset-2">

            <div class="box box-warning">
                <div class="box-header with-border text-center">
                    <h2 class="box-title">@lang('accounting::lang.trial_balance')</h2>
                    <p>{{ @format_date($start_date) }} ~ {{ @format_date($end_date) }}</p>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-stripped">
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
                                $total_credit_opining_balance = 0;
                                $total_dedit_opining_balance = 0;
                                $total_closing_debit_balance = 0;
                                $total_closing_crebit_balance = 0;
                            @endphp

                            <tbody>
                                @foreach ($accounts as $account)
                                    @php
                                        $total_credit_opining_balance += $account->credit_opining_balance;
                                        $total_dedit_opining_balance += $account->dedit_opining_balance;
                                        $total_debit += $account->debit_balance;
                                        $total_credit += $account->credit_balance;

                                        $net_Financial_Transactions =
                                            $account->debit_balance - $account->credit_balance;

                                        if ($net_Financial_Transactions > 0) {
                                            $closing_debit_balance =
                                                $net_Financial_Transactions + $account->dedit_opining_balance;
                                            $closing_credit_balance = 0;
                                        } elseif ($net_Financial_Transactions < 0) {
                                            $closing_credit_balance =
                                                abs($net_Financial_Transactions) + $account->credit_opining_balance;
                                            $closing_debit_balance = 0;
                                        } else {
                                            $closing_credit_balance = $account->credit_opining_balance;
                                            $closing_debit_balance = $account->dedit_opining_balance;
                                        }

                                        $total_closing_debit_balance += $closing_debit_balance;
                                        $total_closing_crebit_balance += $closing_credit_balance;

                                    @endphp
                                    <tr>
                                        <td>{{ $account->name }}</td>
                                        <td>
                                            @if ($account->dedit_opining_balance != 0)
                                                @format_currency($account->dedit_opining_balance)
                                            @endif
                                        </td>
                                        <td>
                                            @if ($account->dedit_opining_balance != 0)
                                                @format_currency($account->credit_opining_balance)
                                            @endif
                                        </td>
                                        <td>
                                            @if ($account->debit_balance != 0)
                                                @format_currency($account->debit_balance)
                                            @endif
                                        </td>
                                        <td>
                                            @if ($account->credit_balance != 0)
                                                @format_currency($account->credit_balance)
                                            @endif
                                        </td>
                                        <td>
                                            @format_currency($closing_debit_balance)
                                        </td>
                                        <td>
                                            @format_currency($closing_credit_balance)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="total_debit">@format_currency($total_credit_opining_balance)</th>
                                    <th class="total_credit">@format_currency($total_dedit_opining_balance)</th>
                                    <th class="total_debit">@format_currency($total_debit)</th>
                                    <th class="total_credit">@format_currency($total_credit)</th>
                                    <th class="total_debit">@format_currency($total_closing_debit_balance)</th>
                                    <th class="total_credit">@format_currency($total_closing_crebit_balance)</th>
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
        });
    </script>

@stop
