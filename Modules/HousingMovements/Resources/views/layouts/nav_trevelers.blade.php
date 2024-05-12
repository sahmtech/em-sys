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
                    <!--1 -->
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.new_arrival_for_workers'))
                        <li @if (request()->segment(2) == 'travelers') class="active" @endif>
                            <a href="{{ route('travelers') }}">
                                <i class="fas fa-user-plus" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('housingmovements::lang.travelers')
                            </a>
                        </li>
                    @endif
                    <!--2 -->
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.housed'))
                        <li @if (request()->segment(2) == 'housed-workers') class="active" @endif>
                            <a href="{{ route('housed_workers') }}">
                                <i class="fas fa-check" aria-hidden="true"></i> @lang('housingmovements::lang.housed')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.advanceSalaryRequest'))
                        <!--3 -->
                        <li @if (request()->segment(2) == 'advanceSalaryRequest') class="active" @endif>
                            {{-- {{ route('advanceSalaryRequest') }} --}}
                            <a href="">
                                <i class="fas fa-hand-holding-usd" aria-hidden="true"></i> @lang('housingmovements::lang.advanceSalaryRequest')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.medicalExamination'))
                        <!-- 4 -->
                        <li @if (request()->segment(2) == 'medicalExamination') class="active" @endif>
                            <a href="{{ route('medicalExamination') }}">
                                <i class="fas fa-stethoscope" aria-hidden="true"></i> @lang('housingmovements::lang.medicalExamination')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.medicalInsurance'))
                        <!-- 5 -->
                        <li @if (request()->segment(2) == 'medicalInsurance') class="active" @endif>
                            <a href="{{ route('medicalInsurance') }}">
                                <i class="fas fa-briefcase-medical" aria-hidden="true"></i> @lang('housingmovements::lang.medicalInsurance')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.workCardIssuing'))
                        <!-- 6 -->
                        <li @if (request()->segment(2) == 'workCardIssuing') class="active" @endif>
                            <a href="{{ route('workCardIssuing') }}">
                                <i class="fas fa-id-card" aria-hidden="true"></i> @lang('housingmovements::lang.workCardIssuing')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.SIMCard'))
                        <!-- 7 -->
                        <li @if (request()->segment(2) == 'SIMCard') class="active" @endif>
                            <a href="{{ route('SIMCard') }}">
                                <i class="fas fa-sim-card" aria-hidden="true"></i> @lang('housingmovements::lang.SIMCard')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.bankAccount'))
                        <!-- 8 -->
                        <li @if (request()->segment(2) == 'bankAccount') class="active" @endif>
                            <a href="{{ route('bankAccountsForLabors') }}">
                                <i class="fas fa-university" aria-hidden="true"></i> @lang('housingmovements::lang.bankAccount')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.Contract'))
                        <!-- 9 -->
                        <li @if (request()->segment(2) == 'QiwaContract') class="active" @endif>
                            <a href="{{ route('QiwaContract') }}">
                                <i class="fas fa-file-contract" aria-hidden="true"></i> @lang('housingmovements::lang.contract')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.residencyAdd&Print'))
                        <!-- 10 -->
                        <li @if (request()->segment(2) == 'residencyPrint') class="active" @endif>
                            <a href="{{ route('residencyPrint') }}">
                                <i class="fas fa-print" aria-hidden="true"></i> @lang('housingmovements::lang.residencyAdd&Print')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.residencyDelivery'))
                        <!-- 11 -->
                        <li @if (request()->segment(2) == 'residencyDelivery') class="active" @endif>
                            <a href="{{ route('residencyDelivery') }}">
                                <i class="fas fa-handshake" aria-hidden="true"></i> @lang('housingmovements::lang.residencyDelivery')
                            </a>
                        </li>
                    @endif


                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
