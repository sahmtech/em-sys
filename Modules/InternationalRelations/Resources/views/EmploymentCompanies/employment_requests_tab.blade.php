@if (!empty($company_requests))
    <table class="table table-condensed">
        <tr>
            <th>@lang('internationalrelations::lang.visa_number')</th>
            <th>@lang('internationalrelations::lang.provided_workers_nationality')</th>
            <th>@lang('internationalrelations::lang.provided_workers_number')</th>
            <th>@lang('internationalrelations::lang.company_request_start_date')</th>
            <th>@lang('internationalrelations::lang.company_request_end_date')</th>
            <th>@lang('internationalrelations::lang.company_request_duration')</th>
        </tr>
        @forelse($company_requests as $request)
            <tr>
                <td>{{$request->visaCard?->visa_number ?? " "}}</td>
                <td> {{ __('internationalrelations::lang.p_worker') }} {{$request->transactionSellLine?->service?->nationality?->nationality?? " "}}</td>
                <td>{{$request->targeted_quantity ?? " "}}</td>
                <td>{{$request->start_date ?? " "}}</td>
                <td>{{$request->lastArrivalproposedLabors($request->agency_id)->first()->arrival_date ?? " "}}</td>
                <td>
                    @php
                        // Calculate duration between start date and end date
                        $startDate = \Carbon\Carbon::parse($request->start_date);
                        $endDate = \Carbon\Carbon::parse($request->lastArrivalproposedLabors($request->agency_id)->first()->arrival_date);
                        $duration = $startDate->diffInDays($endDate);
                    @endphp
                    {{$duration}}  {{ __('internationalrelations::lang.day') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">
                    @lang('purchase.no_records_found')
                </td>
            </tr>
        @endforelse
    </table>
@endif
