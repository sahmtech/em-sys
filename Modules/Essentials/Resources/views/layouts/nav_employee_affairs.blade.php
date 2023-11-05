<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                 <a class="navbar-brand" href=""><i class="fa fas fa-users"></i> {{__('essentials::lang.employees_affairs')}}</a> 
                
            </div>

           
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                 

                     @if(auth()->user()->can('essentials.view_employees'))
                     <li @if((request()->segment(1) == 'hrm' && request()->segment(2) == 'employees')) class="active" @endif>
                            <a href="{{ route('employees') }}">@lang('essentials::lang.employees')</a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->can('essentials.crud_all_roles'))
                    <li @if((request()->segment(1) == 'hrm' && request()->segment(2) == 'roles')) class="active" @endif>
                       
                            <a href="{{ route('roles') }}">@lang('user.roles')</a>
                        </li>
                    @endif

                    @can('essentials.crud_employee_appointments')
                         <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'appointment') class="active" @endif>
                            <a href="{{ route('appointments') }}">@lang('essentials::lang.appointment')</a>
                        </li>
                    @endcan
                        
                       

                    @can('essentials.crud_employee_work_adminitions')
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'admissions_to_work') class="active" @endif>
                            <a href="{{ route('admissionToWork') }}">@lang('essentials::lang.admissions_to_work')</a>
                        </li>
                    @endcan  

                    @can('essentials.crud_employee_contracts')
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'employee_contracts') class="active" @endif>
                            <a href="{{ route('employeeContracts') }}">@lang('essentials::lang.employee_contracts')</a>
                        </li>
                        @endcan  
            
                        @can('essentials.crud_employee_qualifications')
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'qualifications') class="active" @endif>
                            <a href="{{ route('qualifications') }}">@lang('essentials::lang.qualifications')</a>
                        </li>
                        @endcan  
                        
                       
                    @if(auth()->user()->can('essentials.crud_official_documents'))
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'official_documents') class="active" @endif>
                            <a href="{{ route('official_documents') }}">@lang('essentials::lang.official_documents')</a>
                        </li>
                    @endif
               

                        <li @if(request()->segment(2) == 'health_insurance') class="active" @endif>
                            <a href="">@lang('essentials::lang.health_insurance')</a>
                        </li>

                        {{-- {{ route('insurances') }} --}}

                        @can('essentials.crud_employee_families')
                        <li @if(request()->segment(2) == 'employee_families') class="active" @endif>
                            <a href="{{ route('employee_families') }}">@lang('essentials::lang.employee_families')</a>
                        </li>
                        @endcan  

                        @can('essentials.crud_employee_features')
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'featureIndex') class="active" @endif>
                            <a href="{{ route('featureIndex') }}">@lang('essentials::lang.features')</a>
                        </li>
                        @endcan  
                        <li @if(request()->segment(1) == 'hrm' && request()->segment(2) == 'reports') class="active" @endif>
                            <a href="">@lang('essentials::lang.reports')</a>
                        </li>
                        {{-- {{ route('docs') }} --}}
                        
                </ul>

            </div>
        </div>
    </nav>
</section>