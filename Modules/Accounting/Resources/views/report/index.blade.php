@extends('layouts.app')

@section('company_title', __('accounting::lang.journal_entry'))

@section('content')

    {{-- @include('accounting::layouts.nav') --}}

    <!-- Content Header (Page header) -->
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
        <h1>@lang('accounting::lang.reports')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.trial_balance')</h3>
                    </div>

                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.trial_balance_description') --}}
                        <a href="{{ route('accounting.trialBalance') }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.ledger_report')</h3>
                    </div>

                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.ledger_report_description') --}}
                        <a ($ledger_url) href="{{ $ledger_url }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.employees_statement_of_account_report')</h3>
                    </div>

                    <div class="box-body">
                        <a ($employees_statement_url) href="{{ $employees_statement_url }}" {{-- href="{{ route('accounting.employeesStatement') }}" --}}
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                        {{-- @lang( 'accounting::lang.ledger_report_description') --}}
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.customers_and_suppliers_statement_of_account_report')</h3>
                    </div>


                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.ledger_report_description') --}}
                        <a ($customers_suppliers_statement_url) href="{{ $customers_suppliers_statement_url }}"
                            {{-- href="{{ route('accounting.customersSuppliersStatement') }}" --}} class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.balance_sheet')</h3>
                    </div>

                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.balance_sheet_description') --}}
                        <a href="{{ route('accounting.balanceSheet') }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>

                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.account_recievable_ageing_report')</h3>
                    </div>
                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.account_recievable_ageing_report_description') --}}
                        <a href="{{ route('accounting.account_receivable_ageing_report') }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.account_payable_ageing_report')</h3>
                    </div>
                    <div class="box-body">
                        {{-- @lang( 'accounting::lang.account_payable_ageing_report_description') --}}
                        <a href="{{ route('accounting.account_payable_ageing_report') }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.income_list')</h3>
                    </div>
                    <div class="box-body">
                        <a href="{{ route('accounting.incomeStatement') }}"
                            class="btn btn-primary btn-sm pt-2">@lang('accounting::lang.view_report')</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

@stop
