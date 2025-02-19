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

      
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                @if (auth()->user()->can('essentials.understudy_essentials_recuirements_requests'))
                        <li @if(request()->segment(2) == 'under_study_requirements_request') class="active" @endif>
                            <a href="{{ route('get-recuirements-requests') }}">
                            <i class="fas fa-hourglass-half" aria-hidden="true" style="font-size: smaller;"></i>
                            </i> @lang('essentials::lang.under_study')
                            </a>
                        </li>
                 @endif
                
                 @if (auth()->user()->can('essentials.approved_essentials_recuirements_requests'))

                        <li @if(request()->segment(2) == 'accepted_requirements_request') class="active" @endif>
                            <a href="{{route('accepted-recuirements-requests')}}">
                            <i class="fas fa-check" aria-hidden="true" style="font-size: smaller;"></i>

                                </i> @lang('essentials::lang.accepted')
                            </a>
                        </li>
                @endif

                @if (auth()->user()->can('essentials.canceled_essentials_recuirements_requests'))
                          
                        <li @if(request()->segment(2) == 'unaccepted_requirements_request') class="active" @endif>
                            <a href="{{route('unaccepted-recuirements-requests')}}">
                            <i class="fas fa-times" aria-hidden="true" style="font-size: smaller;"></i>

                                </i> @lang('essentials::lang.unacceptable')
                            </a>
                        </li>
                @endif
                   
                </ul>
            </div>
           
        </div>
    </nav>
</section>