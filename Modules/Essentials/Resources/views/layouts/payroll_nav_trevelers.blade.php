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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.new_arrival_for_workers') || auth()->user()->can('payrolls.new_arrival_for_workers'))
                        <li @if (request()->segment(2) == 'pay_travelers') class="active" @endif>
                            <a href="{{ route('pay_travelers') }}">
                                <i class="fas fa-user-plus" aria-hidden="true" style="font-size: smaller;"></i>
                                @lang('housingmovements::lang.travelers')
                            </a>
                        </li>
                    @endif
                    <!--2 -->
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.housed') || auth()->user()->can('payrolls.housed'))
                        <li @if (request()->segment(2) == 'pay_housed_workers') class="active" @endif>
                            <a href="{{ route('pay_housed_workers') }}">
                                <i class="fas fa-check" aria-hidden="true"></i> @lang('housingmovements::lang.housed')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.advanceSalaryRequest') || auth()->user()->can('payrolls.advanceSalaryRequest'))
                        <!--3 -->
                        <li @if (request()->segment(2) == 'pay_advanceSalaryRequest') class="active" @endif>

                            <a href="{{ route('pay_advanceSalaryRequest') }}">
                                <i class="fas fa-hand-holding-usd" aria-hidden="true"></i> @lang('housingmovements::lang.advanceSalaryRequest')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.medicalExamination') || auth()->user()->can('payrolls.medicalExamination'))
                        <!-- 4 -->
                        <li @if (request()->segment(2) == 'pay_medicalExamination') class="active" @endif>
                            <a href="{{ route('pay_medicalExamination') }}">
                                <i class="fas fa-stethoscope" aria-hidden="true"></i> @lang('housingmovements::lang.medicalExamination')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.medicalInsurance')|| auth()->user()->can('payrolls.medicalInsurance'))
                        <!-- 5 -->
                        <li @if (request()->segment(2) == 'pay_medicalInsurance') class="active" @endif>
                            <a href="{{ route('pay_medicalInsurance') }}">
                                <i class="fas fa-briefcase-medical" aria-hidden="true"></i> @lang('housingmovements::lang.medicalInsurance')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.workCardIssuing')|| auth()->user()->can('payrolls.workCardIssuing'))
                        <!-- 6 -->
                        <li @if (request()->segment(2) == 'pay_workCardIssuing') class="active" @endif>
                            <a href="{{ route('pay_workCardIssuing') }}">
                                <i class="fas fa-id-card" aria-hidden="true"></i> @lang('housingmovements::lang.workCardIssuing')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.SIMCard') || auth()->user()->can('payrolls.SIMCard'))
                        <!-- 7 -->
                        <li @if (request()->segment(2) == 'pay_SIMCard') class="active" @endif>
                            <a href="{{ route('pay_SIMCard') }}">
                                <i class="fas fa-sim-card" aria-hidden="true"></i> @lang('housingmovements::lang.SIMCard')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.bankAccount') || auth()->user()->can('payrolls.bankAccount') )
                        <!-- 8 -->
                        <li @if (request()->segment(2) == 'pay_bankAccountsForLabors') class="active" @endif>
                            <a href="{{ route('pay_bankAccountsForLabors') }}">
                                <i class="fas fa-university" aria-hidden="true"></i> @lang('housingmovements::lang.bankAccount')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.contract') || auth()->user()->can('payrolls.contract'))
                        <!-- 9 -->
                        <li @if (request()->segment(2) == 'pay_QiwaContract') class="active" @endif>
                            <a href="{{ route('pay_QiwaContract') }}">
                                <i class="fas fa-file-contract" aria-hidden="true"></i> @lang('housingmovements::lang.contract')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.residencyAdd&Print') || auth()->user()->can('payrolls.residencyAdd&Print'))
                        <!-- 10 -->
                        <li @if (request()->segment(2) == 'pay_residencyPrint') class="active" @endif>
                            <a href="{{ route('pay_residencyPrint') }}">
                                <i class="fas fa-print" aria-hidden="true"></i> @lang('housingmovements::lang.residencyAdd&Print')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.residencyDelivery') || auth()->user()->can('payrolls.residencyDelivery'))
                        <!-- 11 -->
                        <li @if (request()->segment(2) == 'pay_residencyDelivery') class="active" @endif>
                            <a href="{{ route('pay_residencyDelivery') }}">
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
