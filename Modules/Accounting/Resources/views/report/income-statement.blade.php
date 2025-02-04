@extends('layouts.app')

@section('company_title', __('accounting::lang.income_list'))

@section('content')

    <section class="content-header">
        @if (isset($breadcrumbs))
            <nav>
                <ol class="breadcrumb">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if ($breadcrumb['url'])
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1>@lang('accounting::lang.income_list')</h1>
    </section>

    <section class="content container">

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

        <div class="col-md-8 col-md-offset-2 col-lg-12 col-md-offset-0">

            <div class="box box-warning">
                <div class="box-header with-border text-center">
                    <h2 class="box-title">@lang('accounting::lang.income_list')</h2>
                    <p>{{ @format_date($start_date) }} ~ {{ @format_date($end_date) }}</p>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-stripped" id="accounts-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>
                                        <h4>@lang('accounting::lang.Revenues')</h4>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                @foreach ($accounts as $account)
                                    @if (str_starts_with($account->gl_code, '6.1'))
                                        <tr>
                                            <td>
                                                {{ $account->name }}
                                            </td>
                                            <td>
                                                @format_currency(abs($account->credit_balance - $account->debit_balance))
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.total_revenues')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->revenue_net)
                                        </h4>
                                    </td>
                                </tr>


                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4>@lang('accounting::lang.cost_goods_sold')</h4>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                @foreach ($accounts as $account)
                                    @if (str_starts_with($account->gl_code, '4'))
                                        <tr>
                                            <td>
                                                {{ $account->name }}
                                            </td>
                                            <td>
                                                @format_currency(abs($account->debit_balance - $account->credit_balance))
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.total_cost_goods_sold')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->cost_of_revenue)
                                        </h4>
                                    </td>
                                </tr>


                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.gross_profit')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->gross_profit)
                                        </h4>
                                    </td>
                                </tr>


                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4>
                                            @lang('accounting::lang.operating_expense')
                                        </h4>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                @foreach ($accounts as $account)
                                    @if (str_starts_with($account->gl_code, '5.1'))
                                        <tr>
                                            <td>
                                                {{ $account->name }}
                                            </td>
                                            <td>
                                                @format_currency(abs($account->debit_balance - $account->credit_balance))
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.total_operating_expense')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->total_expense)
                                        </h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.income_from_operation')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->operation_income)
                                        </h4>
                                    </td>
                                </tr>


                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4>
                                            @lang('accounting::lang.other_revenues')
                                        </h4>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                @foreach ($accounts as $account)
                                    @if (str_starts_with($account->gl_code, '6.2'))
                                        <tr>
                                            <td>
                                                {{ $account->name }}
                                            </td>
                                            <td>
                                                @format_currency(abs($account->credit_balance - $account->debit_balance))
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.total_other_revenues')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->total_other_income)
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h4>
                                            @lang('accounting::lang.other_expenses')
                                        </h4>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                @foreach ($accounts as $account)
                                    @if (str_starts_with($account->gl_code, '5.2'))
                                        <tr>
                                            <td>
                                                {{ $account->name }}
                                            </td>
                                            <td>
                                                @format_currency(abs($account->debit_balance - $account->credit_balance))
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.total_other_expenses')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->total_other_expense)
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.income_before_tax')
                                        </h5>

                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->income_before_tax)
                                        </h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.autoMigration.tax_amount')
                                        </h5>

                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->tax_amount)
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h5>
                                            @lang('accounting::lang.autoMigration.final_total')
                                        </h5>
                                    </td>
                                    <td>
                                        <h4>
                                            @format_currency($data->income_before_tax - $data->tax_amount)
                                        </h4>
                                    </td>
                                </tr>
                            </tbody>
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
            $('#accounts-table').DataTable({
                "aaSorting": [],
                pageLength: 100
            });
        });
    </script>

@stop
