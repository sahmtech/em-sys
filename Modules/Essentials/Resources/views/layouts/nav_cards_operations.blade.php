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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.work_cards_operation'))
                        <li @if (request()->segment(2) == 'work_cards_operation') class="active" @endif>
                            <a href="{{ route('work_cards_operation') }}">
                                <i class="fas fa-hourglass-half" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('essentials::lang.work_cards_operation')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_requests_operations'))
                        <li @if (request()->segment(2) == 'view_requests_operations') class="active" @endif>
                            <a href="{{ route('view_requests_operations') }}">
                                <i class="fas fa-check" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('essentials::lang.view_requests_operations')
                            </a>
                        </li>
                    @endif



                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
