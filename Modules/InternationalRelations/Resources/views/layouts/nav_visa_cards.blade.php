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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.view_visa_cards'))
                        <li @if (request()->segment(2) == 'visa_cards') class="active" @endif>
                            <a href="{{ route('visa_cards') }}">
                                <i class="fas fa-file-alt" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('internationalrelations::lang.supported_visa_cards')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.view_unSupported_visa_cards'))
                        <li @if (request()->segment(2) == 'unSupported_visa_cards') class="active" @endif>
                            <a href="{{ route('unSupported_visa_cards') }}">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"
                                    style="font-size: smaller;"></i>
                                @lang('internationalrelations::lang.unSupported_visa_cards')
                            </a>
                        </li>
                    @endif



                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>