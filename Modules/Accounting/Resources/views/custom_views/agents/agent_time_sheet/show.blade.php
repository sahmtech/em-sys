@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')
    <section class="content-header">
        <h1>{{ $timesheetGroup->name }}</h1>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border text-center">
                @if (!empty(Session::get('business.logo')))
                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}" alt="Logo"
                        style="width: auto; max-height: 50px;">
                @endif
                <h3 class="box-title">{{ Session::get('business.name') ?? '' }}</h3>
                <br>
                <small>{!! Session::get('business.business_address') ?? '' !!}</small>
                <br>
                <h4>
                    {{ $timesheetGroup->name }}
                </h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('worker.name')</th>
                                <th>@lang('worker.nationality')</th>
                                <th>@lang('worker.eqama_number')</th>
                                <th>@lang('worker.monthly_cost')</th>
                                <th>@lang('worker.wd')</th>
                                <th>@lang('worker.absence_day')</th>
                                <th>@lang('worker.absence_amount')</th>
                                <th>@lang('worker.over_time_h')</th>
                                <th>@lang('worker.over_time')</th>
                                <th>@lang('worker.other_deduction')</th>
                                <th>@lang('worker.other_addition')</th>
                                <th>@lang('worker.cost2')</th>
                                <th>@lang('worker.invoice_value')</th>
                                <th>@lang('worker.vat')</th>
                                <th>@lang('worker.total')</th>
                                <th>@lang('worker.sponser')</th>
                                <th>@lang('worker.basic')</th>
                                <th>@lang('worker.housing')</th>
                                <th>@lang('worker.transport')</th>
                                <th>@lang('worker.other_allowances')</th>
                                <th>@lang('worker.total_salary')</th>
                                <th>@lang('worker.deductions')</th>
                                <th>@lang('worker.additions')</th>
                                <th>@lang('worker.final_salary')</th>
                                {{-- <th>@lang('lang_v1.bank_name')</th>
                                <th>@lang('lang_v1.branch')</th>
                                <th>@lang('essentials::lang.IBAN_number')</th>
                                <th>@lang('essentials::lang.account_holder_name')</th>
                                <th>@lang('lang_v1.account_number')</th>
                                <th>@lang('essentials::lang.tax_number')</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $user)
                                <tr>
                                    <td>{{ $user['name'] }}</td>
                                    <td>{{ $user['nationality'] }}</td>
                                    <td>{{ $user['residency'] }}</td>
                                    <td>{{ number_format($user['monthly_cost'], 2) }}</td>
                                    <td>{{ $user['wd'] }}</td>
                                    <td>{{ $user['absence_day'] }}</td>
                                    <td>{{ number_format($user['absence_amount'], 2) }}</td>
                                    <td>{{ $user['over_time_h'] }}</td>
                                    <td>{{ number_format($user['over_time'], 2) }}</td>
                                    <td>{{ number_format($user['other_deduction'], 2) }}</td>
                                    <td>{{ number_format($user['other_addition'], 2) }}</td>
                                    <td>{{ number_format($user['cost2'], 2) }}</td>
                                    <td>{{ number_format($user['invoice_value'], 2) }}</td>
                                    <td>{{ number_format($user['vat'], 2) }}</td>
                                    <td>{{ number_format($user['total'], 2) }}</td>
                                    <td>{{ $user['sponser'] }}</td>
                                    <td>{{ number_format($user['basic'], 2) }}</td>
                                    <td>{{ number_format($user['housing'], 2) }}</td>
                                    <td>{{ number_format($user['transport'], 2) }}</td>
                                    <td>{{ number_format($user['other_allowances'], 2) }}</td>
                                    <td>{{ number_format($user['total_salary'], 2) }}</td>
                                    <td>{{ number_format($user['deductions'], 2) }}</td>
                                    <td>{{ number_format($user['additions'], 2) }}</td>
                                    <td>{{ number_format($user['final_salary'], 2) }}</td>
                                    {{-- <td>{{ $user['bank_name'] }}</td>
                                    <td>{{ $user['branch'] }}</td>
                                    <td>{{ $user['iban_number'] }}</td>
                                    <td>{{ $user['account_holder_name'] }}</td>
                                    <td>{{ $user['account_number'] }}</td>
                                    <td>{{ $user['tax_number'] }}</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer text-right">
                <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i>
                    @lang('messages.print')</button>
                <a href="{{ route('accounting.agentTimeSheetIndex') }}" class="btn btn-default">@lang('essentials::lang.back')</a>
            </div>
        </div>
    </section>
@endsection

@section('css')
    <style>
        .content-header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .box {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .box-header {
            background-color: #f7f7f7;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .box-title {
            font-size: 20px;
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        @media print {

            .box-header,
            .box-footer,
            .btn {
                display: none;
            }

            .content {
                margin: 0;
                padding: 0;
                border: none;
            }

            .box {
                box-shadow: none;
                border: none;
            }

            table {
                width: 100%;
                table-layout: fixed;
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 2px;
            }
        }
    </style>
@endsection
