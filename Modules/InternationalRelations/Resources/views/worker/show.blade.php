@extends('layouts.app')

@section('title', __('essentials::lang.view_worker'))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang('essentials::lang.view_worker')</h3>
            </div>

            <div class="row">
             

             
                    <div class="nav-tabs-custom">
                      

                        <div class="tab-content">
                            <div class="tab-pane active" id="user_info_tab">

                                <div class="col-md-12">
                                  
                                    <div class="col-md-4">

                            
                                        <p><strong>@lang( 'lang_v1.username' ):</strong> {{$user->full_name ?? ''}}</p>
                                        
                                        <p><strong>@lang( 'lang_v1.dob' ):</strong> @if(!empty($user->dob)) {{@format_date($user->dob)}} @endif</p>
                                        <p><strong>@lang( 'lang_v1.nationality' ):</strong> {{ !empty($nationality) ? json_decode($nationality, true)['nationality'] : '' }}</p>
                            
                            
                                        <p><strong>@lang( 'lang_v1.gender' ):</strong> @if(!empty($user->gender)) @lang('lang_v1.' .$user->gender) @endif</p>
                                        <p><strong>@lang( 'lang_v1.marital_status' ):</strong> @if(!empty($user->marital_status)) @lang('lang_v1.' .$user->marital_status) @endif</p>
                                        <p><strong>@lang( 'lang_v1.blood_group' ):</strong> {{$user->blood_group ?? ''}}</p>
                                        <p><strong>@lang( 'lang_v1.mobile_number' ):</strong> {{$user->contact_number ?? ''}}</p>
                                        <p><strong>@lang( 'business.alternate_number' ):</strong> {{$user->alt_number ?? ''}}</p>
                                        <p><strong>@lang( 'lang_v1.family_contact_number' ):</strong> {{$user->family_number ?? ''}}</p>
                                    </div>
                                    
		


		<div class="col-md-4">
			<p><strong>@lang('lang_v1.id_proof_number'):</strong>
			{{$user->passport_number ?? ''}}</p>
		</div>
		
		<div class="col-md-6">
			<strong>@lang('lang_v1.permanent_address'):</strong><br>
			<p>{{$user->permanent_address ?? ''}}</p>
		</div>
		<div class="col-md-6">
			<strong>@lang('lang_v1.current_address'):</strong><br>
			<p>{{$user->current_address ?? ''}}</p>
		</div>


                            </div>


                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection
