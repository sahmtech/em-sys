@extends('layouts.app')

@section('title', __('internationalrelations::lang.show_employment_company_profile'))
<head>
        <style>
        .profile-info {
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 10px; 
        }

        .profile-info ul {
            list-style-type: none; 
            margin: 0;
            padding: 0;
        }

        .profile-info ul li {
            font-size: 16px; 
            margin-bottom: 5px;
        }
    </style>
</head>
@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <h3>@lang('internationalrelations::lang.show_employment_company_profile')</h3>
            </div>
        </div>
<br>
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#company_info_tab" data-toggle="tab">
                                <i class="fas fa-building" aria-hidden="true"></i>
                                @lang('internationalrelations::lang.company_main_info')
                            </a>
                        </li>
                        <li>
                            <a href="#employment_requests_tab" data-toggle="tab">
                                <i class="fas fa-briefcase" aria-hidden="true"></i>
                                @lang('internationalrelations::lang.employment_comp_requests')
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="company_info_tab">
                            <div class="clearfix"></div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.Office_name'):</strong>
                                        {{ $employment_companies->supplier_business_name ?? '' }}
                                    </p>
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.Office_representative'):</strong>
                                        {{ $employment_companies->name ?? '' }}
                                    </p>
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.mobile'):</strong>
                                        {{ $employment_companies->mobile ?? '' }}
                                    </p>
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.email'):</strong>
                                        {{ $employment_companies->email ?? '' }}
                                    </p>
                                  <p class="profile-info"><strong>@lang('internationalrelations::lang.Evaluation'):</strong>
                                        @if ($employment_companies->evaluation)
                                            @if ($employment_companies->evaluation === 'good')
                                                @lang('internationalrelations::lang.good')
                                            @elseif ($employment_companies->evaluation === 'bad')
                                                @lang('internationalrelations::lang.bad')
                                            @else
                                                {{ $employment_companies->evaluation }}
                                            @endif
                                      
                                        @endif
                                    </p>
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.adderss'):</strong>
                                        {{ $employment_companies->landline ?? '' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.country'):</strong>
                                        {{ $comp_country_name ?? '' }}
                                    </p>
                                    <p class="profile-info"><strong>@lang('internationalrelations::lang.nationalities'):</strong></p>
                                    @if ($nationalities != null)
                                        <ul class="profile-info">
                                            @foreach ($nationalities as $id => $name)
                                                <li>{{ $name }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="employment_requests_tab">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection



@section('javascript')
    <!-- Any additional JavaScript goes here -->
@endsection
