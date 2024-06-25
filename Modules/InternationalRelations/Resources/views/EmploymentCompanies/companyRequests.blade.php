@extends('layouts.app')
@section('title', __('internationalrelations::lang.Delegation'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('internationalrelations::lang.Delegation')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="EmpCompany_table">

                    <tr>
                        <th>{{ __('internationalrelations::lang.agency_name') }}</th>
                        <th>{{ __('internationalrelations::lang.target_quantity') }}</th>
                        <th>{{ __('internationalrelations::lang.currently_proposed_labors_quantity') }}</th>
                        <th>{{ __('sales::lang.profession_name') }}</th>
                        {{-- <th>{{ __('sales::lang.specialization_name') }}</th> --}}
                        <th>{{ __('sales::lang.gender') }}</th>
                        <th>{{ __('sales::lang.service_price') }}</th>
                        <th>{{ __('sales::lang.additional_allwances') }}</th>
                        <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
                        <th>@lang('messages.action')</th>



                    </tr>
                    @foreach ($irDelegations as $delegation)
                        <tr>

                            <td>{{ $delegation->agency->supplier_business_name }}</td>
                            <td>{{ $delegation->targeted_quantity }}</td>
                            <td>{{ $delegation->proposed_labors_quantity }}</td>
                            <td>{{ $delegation->transactionSellLine?->service?->profession->name ?? ' ' }}</td>
                            {{-- <td>{{ $delegation->transactionSellLine?->service?->specialization->name ?? ""}}</td> --}}
                            <td> {{ __('sales::lang.' . $delegation->transactionSellLine?->service?->gender ?? '') }}</td>
                            <td>{{ $delegation->transactionSellLine?->service?->service_price ?? ' ' }}</td>

                            <td>
                                @if (!empty($delegation->transactionSellLine?->additional_allwances))
                                    <ul>
                                        @foreach (json_decode($delegation->transactionSellLine->additional_allwances) as $allwance)
                                            @if (is_object($allwance) && property_exists($allwance, 'salaryType') && property_exists($allwance, 'amount'))
                                                @if ($allwance->salaryType)
                                                    <li>
                                                        {{ __('sales::lang.' . $allwance->salaryType) }}:
                                                        {{ $allwance->amount }}

                                                    </li>
                                                @endif
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif



                            </td>
                            <td>{{ $delegation->transactionSellLine?->service?->monthly_cost_for_one ?? '' }}</td>
                            <td>
                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.add_proposed_worker'))
                                    <button class="btn btn-xs btn-primary">
                                        <a href="{{ route('createProposed_labor', ['delegation_id' => $delegation, 'agency_id' => $delegation->agency->id, 'transaction_sell_line_id' => $delegation->transactionSellLine?->id]) }}"
                                            style="color: white; text-decoration: none;">
                                            @lang('internationalrelations::lang.add_roposed_labor')
                                        </a>
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @endforeach


                </table>
            </div>
        @endcomponent


    </section>
    <!-- /.content -->

@endsection
