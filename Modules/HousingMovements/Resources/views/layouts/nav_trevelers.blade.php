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
                 
                        <li @if(request()->segment(2) == 'travelers') class="active" @endif>
                            <a href="{{ route('travelers') }}">
                                <i class="fas fa-user-plus" aria-hidden="true" style="font-size: smaller;"></i> @lang('housingmovements::lang.travelers')
                            </a>
                        </li>
                 
                
                        <li @if(request()->segment(2) == 'housed_workers') class="active" @endif>
                            <a href="{{route('housed_workers')}}">
                                <i class="fas fa-check" aria-hidden="true"></i> @lang('housingmovements::lang.housed')
                            </a>
                        </li>
                 
                   
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>