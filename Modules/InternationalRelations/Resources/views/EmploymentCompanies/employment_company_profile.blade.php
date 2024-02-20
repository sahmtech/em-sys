@extends('layouts.app')

@section('title', __('internationalrelations::lang.show_employment_company_profile'))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang('internationalrelations::lang.show_employment_company_profile')</h3>
            </div>
            
        </div>

        <div class="row">
         


            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs nav-justified">
                        <li class="active">
                            <a href="#user_info_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-user"
                                    aria-hidden="true"></i> @lang('essentials::lang.employee_info')</a>
                        </li>
                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-money-check" aria-hidden="true"></i>

                                @lang('internationalrelations::lang.company_main_info')</a>
                        </li>

                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-clock" aria-hidden="true"></i>


                                @lang('internationalrelations::lang.employment_comp_requests')</a>
                        </li>

                      
                    </ul>
                 
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
   

@endsection
