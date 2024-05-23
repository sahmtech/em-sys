<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">

                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('generalmanagmentoffice.view_president_requests'))
                        <li @if (request()->segment(2) == 'GMO_president_requests') class="active" @endif>
                            <a href="{{ route('GMO_president_requests') }}">
                                <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('generalmanagmentoffice::lang.requests_by_sequential')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('generalmanagmentoffice.view_GM_escalate_requests'))
                        <li @if (request()->segment(2) == 'GMO_escalate_requests') class="active" @endif>
                            <a href="{{ route('GMO_escalate_requests') }}">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"
                                    style="font-size: smaller;"></i>
                                @lang('generalmanagmentoffice::lang.escalate_requests')
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
