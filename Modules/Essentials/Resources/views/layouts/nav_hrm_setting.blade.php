<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action([\Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])}}"><i class="fa fas fa-users"></i> {{__('essentials::lang.hrm')}}</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    
               
                    @if(auth()->user()->can('essentials.crud_countries') )
                        <li @if(request()->segment(2) == 'countries') class="active" @endif>
                            <a href="{{ route('countries') }}">@lang('essentials::lang.countries')</a>
                        </li>
                    @endif

                    @if(auth()->user()->can('essentials.crud_cities') )
                        <li @if(request()->segment(2) == 'cities') class="active" @endif>
                            <a href="{{ route('cities') }}">@lang('essentials::lang.cities')</a>
                        </li>   
                    @endif

                    @if(auth()->user()->can('essentials.crud_bank_accounts') )
                        <li @if(request()->segment(2) == 'bank_accounts') class="active" @endif>
                            <a href="{{ route('bank_accounts') }}">@lang('essentials::lang.bank_accounts')</a>
                        </li>
                    @endif
                
                    @if(auth()->user()->can('essentials.crud_holidays') )
                        <li @if(request()->segment(2) == 'holiday') class="active" @endif>
                            <a href="{{action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'index'])}}">@lang('essentials::lang.holiday')</a></li>
                        </li>
                    @endif
                    
                    @if(auth()->user()->can('essentials.crud_travel_categories') )
                        <li @if(request()->segment(2) == 'travel_categories') class="active" @endif>
                            <a href="{{ route('travel_categories') }}">@lang('essentials::lang.travel_ticket_categories')</a>
                        </li>
                    @endif

                    @if(auth()->user()->can('essentials.crud_basic_salary') )
                        <li @if(request()->segment(2) == 'basic_salary_types') class="active" @endif>
                            <a href="{{ route('basic_salary_types') }}">@lang('essentials::lang.basic_salary_types')</a>
                        </li>
                    @endif

                    @if(auth()->user()->can('essentials.crud_entitlements') )
                        <li @if(request()->segment(2) == 'entitlements') class="active" @endif>
                            <a href="{{ route('entitlements') }}">@lang('essentials::lang.entitlements')</a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->can('essentials.crud_allowances') )
                        <li @if(request()->segment(2) == 'allowances') class="active" @endif>
                            <a href="{{ route('allowances') }}">@lang('essentials::lang.allowances')</a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->can('essentials.crud_contract_types') )
                        <li @if(request()->segment(2) == 'contract_types') class="active" @endif>
                            <a href="{{ route('contract_types') }}">@lang('essentials::lang.contract_types')</a>
                        </li>
                    @endif
                    {{-- @if(auth()->user()->can('essentials.access_sales_target') )
                        <li @if(request()->segment(2) == 'sales_target') class="active" @endif>
                            <a href="{{ action([\Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index']) }}">@lang('essentials::lang.sales_target')</a>
                        </li>
                    @endif --}}

                    @if(auth()->user()->can('essentials.crud_designation')) 
                        <li @if(request()->get('type') == 'hrm_designation') class="active" @endif>
                            <a href="{{action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=hrm_designation'}}">@lang('essentials::lang.designations')</a></li>

                        </li>
                    @endif
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>