<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action([\Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])}}"><i class="fa fas fa-users"></i> {{__('essentials::lang.hrm')}}</a>
                
            </div>

           
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                     @if(auth()->user()->can('essentials.view_employees'))
                        <li @if(request()->segment(2) == 'employees') class="active" @endif>
                            <a href="{{ route('employees') }}">@lang('essentials::lang.employees_affairs')</a>
                        </li>
                    @endif

                    @if(auth()->user()->can('essentials.crud_all_roles'))
                        <li @if(request()->segment(2) == 'roles') class="active" @endif>
                            <a href="{{ route('roles') }}">@lang('user.roles')</a>
                        </li>
                    @endif

                    @if(auth()->user()->can('essentials.crud_official_documents'))
                        <li @if(request()->segment(2) == 'official_documents') class="active" @endif>
                            <a href="{{ route('official_documents') }}">@lang('essentials::lang.official_documents')</a>
                        </li>
                    @endif
                    
                    
                </ul>

            </div>
        </div>
    </nav>
</section>