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
                
                    @if(auth()->user()->can('essentials.crud_all_leave') || auth()->user()->can('essentials.crud_own_leave'))
                        <li @if(request()->segment(2) == 'leave') class="active" @endif><a href="{{action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index'])}}">@lang('essentials::lang.leave')</a></li>
                    @endif
                    @can('essentials.crud_leave_type')
                    <li @if(request()->segment(2) == 'leave-type') class="active" @endif><a href="{{action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'index'])}}">@lang('essentials::lang.leave_type')</a></li>
                @endcan
                    
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>