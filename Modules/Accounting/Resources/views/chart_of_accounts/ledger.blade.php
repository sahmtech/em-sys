@extends('layouts.app')

@section('title', __('accounting::lang.ledger'))

@section('content')

    {{-- @include('accounting::layouts.nav') --}}

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('accounting::lang.ledger') - @if (Lang::has('accounting::lang.' . $account->name))
                @lang('accounting::lang.' . $account->name)
            @else
                {{ $account->name }}
            @endif
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="box box-solid">
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr>
                                <th>@lang('user.name'):</th>
                                <td>
                                    @if (app()->getLocale() == 'ar')
                                        @if (!empty($account->gl_code))
                                            ({{ $account->gl_code }})
                                            -
                                        @endif
                                        @if (Lang::has('accounting::lang.' . $account->name))
                                            @lang('accounting::lang.' . $account->name)
                                        @else
                                            {{ $account->name }}
                                        @endif
                                    @else
                                        @if (Lang::has('accounting::lang.' . $account->name))
                                            @lang('accounting::lang.' . $account->name)
                                        @else
                                            {{ $account->name }}
                                            @endif @if (!empty($account->gl_code))
                                                - ({{ $account->gl_code }})
                                            @endif
                                        @endif
                                </td>
                            </tr>

                            <tr>
                                <th>@lang('accounting::lang.account_primary_type'):</th>
                                <td>
                                    @if (!empty($account->account_primary_type))
                                        {{ __('accounting::lang.' . $account->account_primary_type) }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>@lang('accounting::lang.account_sub_type'):</th>
                                <td>
                                    @if (!empty($account->account_sub_type))
                                        {{ __('accounting::lang.' . $account->account_sub_type->name) }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>@lang('accounting::lang.detail_type'):</th>
                                <td>
                                    @if (!empty($account->detail_type))
                                        {{ __('accounting::lang.' . $account->detail_type->name) }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('accounting::lang.account_category'):</th>
                                <td>
                                    @if (!empty($account->account_category))
                                        {{ __('accounting::lang.' . $account->account_category) }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('accounting::lang.account_type'):</th>
                                <td>
                                    @if (!empty($account->account_type))
                                        {{ __('accounting::lang.' . $account->account_type) }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('lang_v1.balance'):</th>
                                <td>@format_currency($current_bal)</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="box box-solid">
                    <div class="box-header">
                        <h3 class="box-title"> <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters'):</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('all_accounts', __('accounting::lang.account') . ':') !!}
                                    {!! Form::select('account_filter', [$account->id => $account->name], $account->id, [
                                        'class' => 'form-control accounts-dropdown',
                                        'style' => 'width:100%',
                                        'id' => 'account_filter',
                                        'data-default' => $account->id,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('start_date_filter', __('accounting::lang.from_date') . ':') !!}
                                    {!! Form::date('start_date_filter', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('lang_v1.select_start_date'),
                                        'id' => 'start_date_filter',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-sm-6">
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="ledger">
                                <thead>
                                    <tr>
                                        <th colspan="7"></th>
                                        <th colspan="2" class="bg-gray   text-center">
                                            @lang('accounting::lang.transaction_op')</th>
                                        <th colspan="2" class="bg-gray  text-center">
                                            @lang('accounting::lang.balance')</th>
                                    </tr>
                                    <tr>
                                        <th>@lang('accounting::lang.number')</th>
                                        <th>@lang('messages.date')</th>
                                        <th>@lang('accounting::lang.transaction')</th>
                                        <th>@lang('lang_v1.cost_senter')</th>
                                        <th>@lang('accounting::lang.partner_name')</th>
                                        <th>@lang('brand.note')</th>
                                        <th>@lang('lang_v1.added_by')</th>
                                        <th class="  text-center">@lang('account.debit')</th>
                                        <th class="  text-center">@lang('account.credit')</th>
                                        <th class="  text-center">@lang('account.debit')</th>
                                        <th class="  text-center"> @lang('account.credit')</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="7"><strong>@lang('accounting::lang.period_total'):</strong></td>
                                        <td class="footer_total_debit"></td>
                                        <td class="footer_total_credit"></td>
                                        <td class=""></td>
                                        <td class=""></td>
                                    </tr>
                                    <tr class="bg-gray font-17 footer-total text-center">
                                        <td colspan="7"><strong>@lang('accounting::lang.autoMigration.final_total'):</strong></td>
                                        <td class="footer_final_total_debit"></td>
                                        <td class="footer_final_total_credit"></td>
                                        <td class=""></td>
                                        <td class=""></td>
                                    </tr>

                                </tfoot>
                            </table>
                            <div class="modal fade" id="printJournalEntry" tabindex="-1" role="dialog"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@stop

@section('javascript')
    @include('accounting::accounting.common_js')
    <script>
        $(document).ready(function() {
            $('#account_filter').change(function() {
                account_id = $(this).val();
                url = base_path + '/accounting/ledger/' + account_id;
                window.location = url;
            })

            dateRangeSettings.startDate = moment().subtract(6, 'days');
            dateRangeSettings.endDate = moment();
            $('#transaction_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#transaction_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));

                    ledger.ajax.reload();
                }
            );

            // Account Book
            ledger = $('#ledger').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ action('\Modules\Accounting\Http\Controllers\CoaController@ledger', [$account->id]) }}',
                    data: function(d) {
                        if ($('#start_date_filter').val()) {
                            d.start_date = $('#start_date_filter').val();
                        }
                        if ($('#end_date_filter').val()) {
                            d.end_date = $('#end_date_filter').val();
                        }
                        var transaction_type = $('select#transaction_type').val();
                        d.type = transaction_type;
                    }
                },
                "ordering": false,
                columns: [{
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'operation_date',
                        name: 'operation_date'
                    },
                    {
                        data: 'transaction',
                        name: 'transaction'
                    },
                    {
                        data: 'cost_center_name',
                        name: 'cost_center_name',
                    },
                    {
                        data: 'partner_name',
                        name: 'partner_name',
                    },
                    {
                        data: 'note',
                        name: 'ATM.note'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by'
                    },
                    {
                        data: 'op_debit',
                        name: 'amount',
                        searchable: false
                    },
                    {
                        data: 'op_credit',
                        name: 'amount',
                        searchable: false
                    },
                    {
                        data: 'balance_debit',
                        name: 'amount',
                        searchable: false
                    },
                    {
                        data: 'balance_credit',
                        name: 'amount',
                        searchable: false
                    },
                    //{data: 'balance', name: 'balance', searchable: false},
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#ledger'));
                },
                "footerCallback": function(row, data, start, end, display) {
                    var footer_total_debit = 0;
                    var footer_total_credit = 0;
                    for (var r in data) {
                        footer_total_debit += $(data[r].debit).data('orig-value') ? parseFloat($(data[r]
                            .debit).data('orig-value')) : 0;
                        footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(data[
                            r].credit).data('orig-value')) : 0;
                    }
                    $('.footer_total_debit').html(__currency_trans_from_en(footer_total_debit));
                    $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                    $('.footer_final_total_debit').html(__currency_trans_from_en(
                        {{ $total_debit_bal }}));
                    $('.footer_final_total_credit').html(__currency_trans_from_en(
                        {{ $total_credit_bal }}));
                }
            });
            $('#end_date_filter,#start_date_filter').on('change', function() {
                ledger.ajax.reload();
            });

        });
    </script>
@stop
