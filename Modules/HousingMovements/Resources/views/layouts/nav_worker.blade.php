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

                    <li @if (request()->segment(3) == 'available-shopping') class="active" @endif>
                        <a href="{{ route('workers.available_shopping') }}">
                            <i class="fa fa-users" aria-hidden="true" style="font-size: smaller;"></i> @lang('housingmovements::lang.available_shopping')
                        </a>
                    </li>

                    <li @if (request()->segment(3) == 'reserved-shopping') class="active" @endif>
                        <a href="{{ route('workers.reserved_shopping') }}">
                            <i class="fa fa-lock" aria-hidden="true"></i> @lang('housingmovements::lang.reserved_shopping')
                        </a>
                    </li>

                    <li @if (request()->segment(3) == 'final-exit') class="active" @endif>
                        <a href="{{ route('workers.final_exit') }}">
                            <i class="fa fa-plane" aria-hidden="true" style="rotate: -30deg;"></i> @lang('housingmovements::lang.final_exit')
                        </a>
                    </li>

                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
