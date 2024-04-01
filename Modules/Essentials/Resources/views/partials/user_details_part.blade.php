<div class="clearfix">
</div>
<hr>
<div class="col-md-12">
    <h4>@lang('essentials::lang.hrm_details'):</h4>
</div>

<div class="col-md-6">
    @if ($user->user_type != 'worker')
        <p><strong>@lang('essentials::lang.department'):</strong> {{ $user_department->name ?? '' }}</p>
    @endif

    <p><strong>@lang('essentials::lang.profession'):</strong> {{ $user->profession ?? '' }}</p>


</div>

<div class="col-md-6">
    <p><strong>@lang('essentials::lang.salary'):</strong>
        @if (!empty($user->essentials_salary) && !empty($user->essentials_pay_period))
            @format_currency($user->essentials_salary) @lang('essentials::lang.per')
            @if ($user->essentials_pay_period == 'week')
                {{ __('essentials::lang.week') }}
            @else
                {{ __('lang_v1.' . $user->essentials_pay_period) }}
            @endif
        @endif
    </p>
    
     <p><strong>@lang('essentials::lang.total_salary'):</strong>
        {{ $user->calculateTotalSalary() ?? '' }}
    </p>
                                   


   
    <p><strong>@lang('essentials::lang.pay_cycle'):</strong>
        @if (!empty($user->essentials_pay_period))
            @if ($user->essentials_pay_period == 'week')
                {{ __('essentials::lang.week') }}
            @else
                {{ __('lang_v1.month') }}
            @endif
        @endif
    </p>
    <p><strong>@lang('essentials::lang.max_anuual_leave_days'):</strong> {{ $user->max_anuual_leave_days ?? '' }} @lang('essentials::lang.day')</p>
    <p><strong>@lang('lang_v1.primary_work_location'):</strong>
        @if (!empty($work_location))
            {{ $work_location->name }}
        @else
            {{ __('report.all_locations') }}
        @endif
    </p>
    <!-- Add more details here -->
</div>


@if ($contract)
    <div class="col-md-12">
        <p><strong>@lang('essentials::lang.contract_details'):</strong></p>
        <ul>

            <li><strong>@lang('essentials::lang.contract_number'):</strong> {{ $contract->contract_number ?? '' }}</li>
            <li><strong>@lang('essentials::lang.contract_start_date'):</strong>
                @if (!empty($contract->contract_start_date))
                    {{ @format_date($contract->contract_start_date) }}
                @endif
            </li>
            <li><strong>@lang('essentials::lang.contract_end_date'):</strong>
                @if (!empty($contract->contract_end_date))
                    {{ @format_date($contract->contract_end_date) }}
                @endif
            </li>
            <li>
                <strong>@lang('essentials::lang.contract_duration'):</strong>
                {{ $contract->contract_duration }}
                {{ $contract->contract_per_period ? ' ' . trans('essentials::lang.' . $contract->contract_per_period) : '' }}

            </li>
            <li><strong>@lang('essentials::lang.probation_period'):</strong> {{ $contract->probation_period ?? '' }}</li>
            <li><strong>@lang('essentials::lang.status'):</strong>
                {{ $contract->status ? ' ' . trans('essentials::lang.' . $contract->status) : '' }}</li>
            <li>
                <strong>@lang('essentials::lang.is_renewable'):</strong>
                @if ($contract->is_renewable === null)
                    {{ '' }}
                @elseif ($contract->is_renewable == 1)
                    @lang('essentials::lang.is_renewable')
                @else
                    @lang('essentials::lang.is_unrenewable')
                @endif
            </li>


        </ul>

    </div>

@endif
