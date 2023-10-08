<div class="clearfix"></div>
<hr>
<div class="col-md-12">
    <h4>@lang('essentials::lang.hrm_details'):</h4>
</div>

<div class="col-md-6">
    <p><strong>@lang('essentials::lang.department'):</strong> {{$user_department->name ?? ''}}</p>
    <p><strong>@lang('essentials::lang.designation'):</strong> {{$user_designstion->name ?? ''}}</p>
</div>

<div class="col-md-6">
    <p><strong>@lang('essentials::lang.salary'):</strong> 
        @if(!empty($user->essentials_salary) && !empty($user->essentials_pay_period))
            @format_currency($user->essentials_salary) @lang('essentials::lang.per')
            @if($user->essentials_pay_period == 'week')
                {{__('essentials::lang.week')}}
            @else
                {{__('lang_v1.'.$user->essentials_pay_period)}}
            @endif
        @endif
    </p>

    <p><strong>@lang('essentials::lang.pay_cycle'):</strong>
        @if(!empty($user->essentials_pay_period))
            @if($user->essentials_pay_period == 'week')
                {{__('essentials::lang.week')}}
            @else
                {{__('lang_v1.month')}}
            @endif
        @endif
    </p>

    <p><strong>@lang('lang_v1.primary_work_location'):</strong>
        @if(!empty($work_location))
            {{$work_location->name}}
        @else
            {{__('report.all_locations')}}
        @endif
    </p>
    <!-- Add more details here -->
</div>

@if ($qualification_type)
<div class="col-md-12">
    <p><strong>@lang('essentials::lang.qualification_type'):</strong> {{ $qualification_type->name ?? '' }}</p>
</div>
@endif

@if ($contract->count() > 0)
<div class="col-md-12">
    <p><strong>@lang('essentials::lang.contract_details'):</strong></p>
    <ul>
        @foreach ($contract as $contractItem)
        <li><strong>@lang('essentials::lang.contract_number'):</strong> {{ $contractItem->contract_number ?? '' }}</li>
        <li><strong>@lang('essentials::lang.contract_start_date'):</strong> {{ $contractItem->contract_start_date ?? '' }}</li>
        <li><strong>@lang('essentials::lang.contract_end_date'):</strong> {{ $contractItem->contract_end_date ?? '' }}</li>
        <li><strong>@lang('essentials::lang.contract_duration'):</strong> {{ $contractItem->contract_duration ?? '' }}</li>
        <li><strong>@lang('essentials::lang.probation_period'):</strong> {{ $contractItem->probation_period ?? '' }}</li>
        <li><strong>@lang('essentials::lang.is_active'):</strong> {{ $contractItem->is_active ?? '' }}</li>
        <li><strong>@lang('essentials::lang.is_renewable'):</strong> 
            @if ($contractItem->is_renewable == 1)
                @lang('essentials::lang.is_renewable')
            @else
                @lang('essentials::lang.is_unrenewable')
            @endif
        </li>
        <li><strong>@lang('essentials::lang.basic_salary_type'):</strong> {{ $contractItem->basic_salary_type ?? '' }}</li>
        <li><strong>@lang('essentials::lang.travel_ticket_categorie'):</strong> {{ $contractItem->travel_ticket_category_name ?? '' }}</li>
        <li><strong>@lang('essentials::lang.allowance_type'):</strong> {{ $contractItem->allowance_name ?? '' }}</li>
        <li><strong>@lang('essentials::lang.entitlement_type'):</strong> {{ $contractItem->entitlement_name ?? '' }}</li>
        @if ($contractItem->work_type == 'full_time')
            <li><strong>@lang('essentials::lang.work_type'):</strong> @lang('essentials::lang.full_time')</li>
        @elseif ($contractItem->work_type == 'part_time')
            <li><strong>@lang('essentials::lang.work_type'):</strong> @lang('essentials::lang.part_time')</li>
        @else
            <li><strong>@lang('essentials::lang.work_type'):</strong> {{ $contractItem->work_type ?? '' }}</li>
        @endif
        @endforeach
    </ul>
	@if($admissions_to_work->count() > 0)
	<div class="col-md-6">
		<ul class="list-unstyled">
			@foreach ($admissions_to_work as $admission)
			<li><strong>@lang('essentials::lang.dmissions_type'):</strong> {{ $admission->dmissions_type ?? '' }}</li>
			<li><strong>@lang('essentials::lang.dmissions_status'):</strong> {{ $admission->dmissions_status ?? '' }}</li>
		
			@endforeach
		</ul>
	</div>
	@endif
</div>

@endif
