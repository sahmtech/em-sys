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

                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_lead_contacts'))
                    <li @if (request()->segment(2) == 'lead_contacts') class="active" @endif>
                        <a href="{{ route('lead_contacts') }}">
                            <i class="fas fa-bullseye" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('sales::lang.lead_contacts')
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_qualified_contacts'))
                    <li @if (request()->segment(2) == 'qualified_contacts') class="active" @endif>
                        <a href="{{ route('qualified_contacts') }}">
                            <i class="fas fa-check-circle" aria-hidden="true"></i> @lang('sales::lang.qualified_contacts')
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_unqualified_contacts'))
                    <li @if (request()->segment(2) == 'unqualified_contacts') class="active" @endif>
                        <a href="{{ route('unqualified_contacts') }}">
                            <i class="fas fa-times-circle" aria-hidden="true"></i> @lang('sales::lang.unqualified_contacts')
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.view_converted_contacts'))

                    <li @if (request()->segment(2) == 'converted_contacts') class="active" @endif>
                        <a href="{{ route('converted_contacts') }}">
                            <i class="fas fa-handshake" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('sales::lang.converted_contacts')
                        </a>
                    </li>
                    @endif

                  
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
