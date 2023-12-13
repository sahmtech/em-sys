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
                    @can('essentials.view_candidate_workers')
                        <li @if(request()->segment(2) == 'proposed_laborIndex') class="active" @endif>
                            <a href="{{ route('proposed_laborIndex') }}">
                                <i class="fas fa-user-plus" aria-hidden="true" style="font-size: smaller;"></i> @lang('internationalrelations::lang.candidate_workers')
                            </a>
                        </li>
                    @endcan
                    @can('essentials.view_accepted_workers')
                        <li @if(request()->segment(2) == 'accepted_workers') class="active" @endif>
                            <a href="{{ route('accepted_workers') }}">
                                <i class="fas fa-check" aria-hidden="true"></i> @lang('internationalrelations::lang.accepted_workers')
                            </a>
                        </li>
                    @endcan
                  
                    @can('essentials.view_workers_under_trialPeriod')
                    <li @if(request()->segment(2) == 'workers_under_trialPeriod') class="active" @endif>
                        <a href="{{ route('workers_under_trialPeriod') }}">
                            <i class="fa fa-spinner" aria-hidden="true"></i> @lang('internationalrelations::lang.workers_under_trialPeriod')
                        </a>
                    </li>
                    @endcan
                    @can('essentials.view_unaccepted_workers')
                    <li @if(request()->segment(2) == 'unaccepted_workers') class="active" @endif>
                        <a href="{{ route('unaccepted_workers') }}">
                            <i class="fas fa-times" aria-hidden="true" style="font-size: smaller;"></i> @lang('internationalrelations::lang.unaccepted_workers')

                    </li>
                    @endcan
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>