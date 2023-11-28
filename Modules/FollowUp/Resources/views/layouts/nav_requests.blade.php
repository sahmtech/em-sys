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
                    <li @can('followup::lang.viewExitRequests') class="{{ request()->segment(2) == 'exitRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex']) }}">
                        @lang('followup::lang.exitRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Return Request -->
                    <li @can('followup::lang.viewReturnRequest') class="{{ request()->segment(2) == 'returnRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex']) }}">
                        @lang('followup::lang.returnRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Escape Request -->
                    <li @can('followup::lang.viewEscapeRequest') class="{{ request()->segment(2) == 'escapeRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex']) }}">
                        @lang('followup::lang.escapeRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Advance Salary -->
                    <li @can('followup::lang.viewAdvanceSalary') class="{{ request()->segment(2) == 'advanceSalary' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex']) }}">
                        @lang('followup::lang.advanceSalary')
                   </a>
                    @endcan
                        </li>

                        <!-- Leaves and Departures -->
                    <li @can('followup::lang.viewLeavesAndDepartures') class="{{ request()->segment(2) == 'leavesAndDepartures' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex']) }}">
                        @lang('followup::lang.leavesAndDepartures')
                   </a>
                    @endcan
                        </li>

                        <!-- Atm Card -->
                    <li @can('followup::lang.viewAtmCard') class="{{ request()->segment(2) == 'atmCard' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex']) }}">
                        @lang('followup::lang.atmCard')
                   </a>
                    @endcan
                        </li>

                        <!-- Residence Renewal -->
                    <li @can('followup::lang.viewResidenceRenewal') class="{{ request()->segment(2) == 'residenceRenewal' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex']) }}">
                        @lang('followup::lang.residenceRenewal')
                   </a>
                    @endcan
                        </li>

                        <!-- Residence Card -->
                    <li @can('followup::lang.viewResidenceCard') class="{{ request()->segment(2) == 'residenceCard' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex']) }}">
                        @lang('followup::lang.residenceCard')
                   </a>
                    @endcan
                        </li>

                        <!-- Worker Transfer -->
                    <li @can('followup::lang.viewWorkerTransfer') class="{{ request()->segment(2) == 'workerTransfer' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex']) }}">
                        @lang('followup::lang.workerTransfer')
                   </a>
                    @endcan
                        </li>

                        <!-- Chamber Request -->
                    <li @can('followup::lang.viewChamberRequest') class="{{ request()->segment(2) == 'chamberRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'chamberRequestIndex']) }}">
                        @lang('followup::lang.chamberRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Mofa Request -->
                    <li @can('followup::lang.viewMofaRequest') class="{{ request()->segment(2) == 'mofaRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'mofaRequestIndex']) }}">
                        @lang('followup::lang.mofaRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Insurance Upgrade Request -->
                    <li @can('followup::lang.viewInsuranceUpgradeRequest') class="{{ request()->segment(2) == 'insuranceUpgradeRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'insuranceUpgradeRequestIndex']) }}">
                        @lang('followup::lang.insuranceUpgradeRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Balady Card Request -->
                    <li @can('followup::lang.viewBaladyCardRequest') class="{{ request()->segment(2) == 'baladyCardRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'baladyCardRequestIndex']) }}">
                        @lang('followup::lang.baladyCardRequest')
                   </a>
                    @endcan
                        </li>

                        <!-- Residence Edit Request -->
                    <li @can('followup::lang.viewResidenceEditRequest') class="{{ request()->segment(2) == 'residenceEditRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceEditRequestIndex']) }}">
                        @lang('followup::lang.residenceEditRequest')
                  </a>

                    @endcan
                        </li>

                        <!-- Work Injuries Request -->
                    <li @can('followup::lang.viewWorkInjuriesRequest') class="{{ request()->segment(2) == 'workInjuriesRequest' ? 'active' : '' }}">
                    <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workInjuriesRequestIndex']) }}">
                        @lang('followup::lang.workInjuriesRequest')
                   </a>

                    @endcan
                    </li>

                        <!-- ؤancle Contract Request -->
                    <li @can('followup::lang.viewCancleContractRequest') class="{{ request()->segment(2) == 'cancleContractRequest' ? 'active' : '' }}">
                        <a href="{{ action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'cancleContractRequestIndex']) }}">
                            @lang('followup::lang.cancleContractRequest')
                       </a>
    
                        @endcan
                        </li>


                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
