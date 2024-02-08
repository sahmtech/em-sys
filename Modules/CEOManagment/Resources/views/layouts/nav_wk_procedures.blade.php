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
              
                
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                
                    
                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('ceomanagment.view_procedures_for_employee'))
                    <li @if(request()->segment(2) == 'employeesProcedures') class="active" @endif><a href="{{action([\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'employeesProcedures'])}}">  <i class="fa fa-user-tie" aria-hidden="true" style="font-size: smaller;"></i> @lang('ceomanagment::lang.employees')</a></li>
                    @endif
                  
                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('ceomanagment.view_procedures_for_workers'))
                        <li @if(request()->segment(2) == 'workersProcedures') class="active" @endif><a href="{{action([\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'workersProcedures'])}}">  <i class="fas fa-user-cog" aria-hidden="true" style="font-size: smaller;"></i> @lang('ceomanagment::lang.workers')</a></li>
                    @endif
                  

                    
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>