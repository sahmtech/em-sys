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
                     
                    <li @if (request()->segment(2) == 'under_study_offer_prices') class="active" @endif>
                        <a href="{{ route('under_study_offer_prices') }}">
                            <i class="fas fa-hourglass-half" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('sales::lang.under_study_offer_prices')
                        </a>
                    </li>
                    <li @if (request()->segment(2) == 'accepted_offer_prices') class="active" @endif>
                        <a href="{{ route('accepted_offer_prices') }}">
                            <i class="fas fa-check" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('sales::lang.accepted_offer_prices')
                        </a>
                    </li>
                    <li @if (request()->segment(2) == 'unaccepted_offer_prices') class="active" @endif>
                        <a href="{{ route('unaccepted_offer_prices') }}">
                            <i class="fas fa-times" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('sales::lang.unaccepted_offer_prices')
                        </a>
                    </li>
                  
                    

                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
