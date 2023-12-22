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

                    <!-- Exit Request -->
                    <li class="{{ request()->segment(2) == 'exitRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex']) }}">
                            @lang('followup::lang.exitRequest')
                        </a>

                    </li>

                    <!-- Return Request -->
                    <li class="{{ request()->segment(2) == 'returnRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex']) }}">
                            @lang('followup::lang.returnRequest')
                        </a>

                    </li>

                    <!-- Escape Request -->
                    <li class="{{ request()->segment(2) == 'escapeRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex']) }}">
                            @lang('followup::lang.escapeRequest')
                        </a>

                    </li>

                    <!-- Advance Salary -->
                    <li class="{{ request()->segment(2) == 'advanceSalary' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex']) }}">
                            @lang('followup::lang.advanceSalary')
                        </a>

                    </li>

                    <!-- Leaves and Departures -->
                    <li class="{{ request()->segment(2) == 'leavesAndDepartures' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex']) }}">
                            @lang('followup::lang.leavesAndDepartures')
                        </a>

                    </li>

                    <!-- Atm Card -->
                    <li class="{{ request()->segment(2) == 'atmCard' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex']) }}">
                            @lang('followup::lang.atmCard')
                        </a>

                    </li>

                    <!-- Residence Renewal -->
                    <li class="{{ request()->segment(2) == 'residenceRenewal' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex']) }}">
                            @lang('followup::lang.residenceRenewal')
                        </a>

                    </li>

                    <!-- Residence Card -->
                    <li class="{{ request()->segment(2) == 'residenceCard' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex']) }}">
                            @lang('followup::lang.residenceCard')
                        </a>

                    </li>

                    <!-- Worker Transfer -->
                    <li class="{{ request()->segment(2) == 'workerTransfer' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex']) }}">
                            @lang('followup::lang.workerTransfer')
                        </a>

                    </li>

                    <!-- Chamber Request -->
                    <li class="{{ request()->segment(2) == 'chamberRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'chamberRequestIndex']) }}">
                            @lang('followup::lang.chamberRequest')
                        </a>

                    </li>

                    <!-- Mofa Request -->
                    <li class="{{ request()->segment(2) == 'mofaRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'mofaRequestIndex']) }}">
                            @lang('followup::lang.mofaRequest')
                        </a>

                    </li>

                    <!-- Insurance Upgrade Request -->
                    <li class="{{ request()->segment(2) == 'insuranceUpgradeRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'insuranceUpgradeRequestIndex']) }}">
                            @lang('followup::lang.insuranceUpgradeRequest')
                        </a>

                    </li>

                    <!-- Balady Card Request -->
                    <li class="{{ request()->segment(2) == 'baladyCardRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'baladyCardRequestIndex']) }}">
                            @lang('followup::lang.baladyCardRequest')
                        </a>

                    </li>

                    <!-- Residence Edit Request -->
                    <li class="{{ request()->segment(2) == 'residenceEditRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceEditRequestIndex']) }}">
                            @lang('followup::lang.residenceEditRequest')
                        </a>


                    </li>

                    <!-- Work Injuries Request -->
                    <li class="{{ request()->segment(2) == 'workInjuriesRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workInjuriesRequestIndex']) }}">
                            @lang('followup::lang.workInjuriesRequest')
                        </a>


                    </li>

                    <!-- ؤancle Contract Request -->
                    <li class="{{ request()->segment(2) == 'cancleContractRequest' ? 'active' : '' }}">
                        <a
                            href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'cancleContractRequestIndex']) }}">
                            @lang('followup::lang.cancleContractRequest')
                        </a>


                    </li>


                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
