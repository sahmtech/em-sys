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
                {{-- <a class="navbar-brand" href="{{action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])}}"><i class="fa fas fa-users"></i> {{__('essentials::lang.employees_affairs')}}</a> --}}
                
            </div>

           
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                
                @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("essentials.crud_employee_features"))
                    <li @if(request()->segment(2) == 'featureIndex') class="active" @endif><a href="{{ route('featureIndex') }}">@lang('essentials::lang.allowances')</a></li>
                @endif  

                @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("essentials.curd_travel_categories_features"))  
                    <li @if(request()->segment(2) == 'userTravelCat') class="active" @endif><a href="{{ route('userTravelCat') }}">@lang('essentials::lang.travel_categories')</a></li>
                @endif  

                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>