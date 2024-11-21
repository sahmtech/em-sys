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

                    <li @if (request()->segment(2) == 'president_pending_requests') class="active" @endif>
                        <a href="{{ route('president_pending_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('generalmanagement::lang.today_pending_requests')
                        </a>
                    </li>
                    <li @if (request()->segment(2) == 'president_requests') class="active" @endif>
                        <a href="{{ route('president_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('generalmanagement::lang.old_pending_requests')
                        </a>
                    </li>



                    <li @if (request()->segment(2) == 'president_done_requests') class="active" @endif>
                        <a href="{{ route('president_done_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            @lang('generalmanagement::lang.done_requests')
                        </a>
                    </li>




                    <!-- // TODO: Refactor this  is bad way i know that -->


                    <li @if (request()->segment(2) == 'ceo_dept_requests') class="active" @endif>
                        <a href="{{ route('ceo_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            الرئيس التنفيذي
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'housin_mmovement_dept_requests') class="active" @endif>
                        <a href="{{ route('housin_mmovement_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة السكن والحركة
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'operations_dept_requests') class="active" @endif>
                        <a href="{{ route('operations_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            إدارة التشغيل (قطاع الأعمال)
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'sales_dept_requests') class="active" @endif>
                        <a href="{{ route('sales_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة المبيعات
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'international_relations_dept_requests') class="active" @endif>
                        <a href="{{ route('international_relations_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة العلاقات الدولية
                        </a>
                    </li>


                    <li @if (request()->segment(2) == 'hr_dept_requests') class="active" @endif>
                        <a href="{{ route('hr_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة الموارد البشرية
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'hr_dept_apps_requests') class="active" @endif>
                        <a href="{{ route('hr_dept_apps_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة موارد بشرية تطبيقات
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'legal_affairs_dept_requests') class="active" @endif>
                        <a href="{{ route('legal_affairs_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            ادارة الشؤون القانونية
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'financial_dept_requests') class="active" @endif>
                        <a href="{{ route('financial_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            الادارة المالية
                        </a>
                    </li>


                    <li @if (request()->segment(2) == 'government_relations_dept_requests') class="active" @endif>
                        <a href="{{ route('government_relations_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            قسم العلاقات الحكومية
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'personnel_affairs_dept_requests') class="active" @endif>
                        <a href="{{ route('personnel_affairs_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            قسم شؤون الموظفين
                        </a>
                    </li>

                    <li @if (request()->segment(2) == 'payroll_dept_requests') class="active" @endif>
                        <a href="{{ route('payroll_dept_requests') }}">
                            <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                            قسم الرواتب
                        </a>
                    </li>





                    {{-- @foreach ($departments_needs as $index => $department)
                        <li id="department_filter{{ $index }}">
                            <a id="department_filter{{ $index }}" href="javascript:void(0);"
                                class="department-link" data-department-id="{{ $index }}">
                                <!-- Adding department ID to data attribute -->
                                <i class="fas fa-list" aria-hidden="true" style="font-size: smaller;"></i>
                                {{ $department }}
                            </a>
                        </li>
                    @endforeach --}}


                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
