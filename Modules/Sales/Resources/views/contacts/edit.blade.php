@extends('layouts.app')
@section('title', __('sales::lang.edit_customer'))

@section('content')
<section class="content-header">
    <h1>
        <span>@lang('sales::lang.edit_customer')</span>
    </h1>
</section>
<section class="content">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['sale.UpdateCustomer', $contact->id], 'method' => 'put', 'id' => 'add_country_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'sales::lang.customer_info' )</h4>
      </div>
  
    <div class="modal-body">
      <div class="row">

                        <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                {!! Form::select('type', $types, $contact->type, ['class' => 'form-control', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
                            </div>
                        </div>
                        </div>                            
                    


                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                            <input type="hidden" id="hidden_id" value="{{$contact->id}}">
                            {!! Form::text('contact_id', $contact->contact_id, ['class' => 'form-control','placeholder' => __('lang_v1.contact_id')]); !!}
                        </div>
                        <p class="help-block">
                            @lang('lang_v1.leave_empty_to_autogenerate')
                        </p>
                    </div>
                    </div>

                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('first_name', __('sales::lang.first_name') . ':*') !!}
                                <div class="input-group">
                                  
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                                </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->first_name}}">
                                    {!! Form::text('first_name', $contact->first_name, ['class' => 'form-control','placeholder' => __('sales::lang.first_name')]); !!}
                                </div>
                               
                            </div>
                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('last_name', __('sales::lang.last_name') . ':*') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->first_name}}">
                                    {!! Form::text('last_name', $contact->last_name, ['class' => 'form-control','placeholder' => __('sales::lang.last_name')]); !!}
                                </div>
                               
                            </div>      

                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('name_en', __('sales::lang.name_en') . ':*') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->english_name}}">
                                    {!! Form::text('name_en', $contact->english_name, ['class' => 'form-control','placeholder' => __('sales::lang.name_en')]); !!}
                                </div>
                               
                            </div>      

                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('supplier_business_name', __('business.business_name') . ':*') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->supplier_business_name}}">
                                    {!! Form::text('supplier_business_name', $contact->supplier_business_name, ['class' => 'form-control','placeholder' => __('business.business_name')]); !!}
                                </div>
                               
                            </div>      

                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('commercial_register_no', __('sales::lang.commercial_register_no') . ':') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->commercial_register_no}}">
                                    {!! Form::text('commercial_register_no', $contact->commercial_register_no, ['class' => 'form-control','placeholder' => __('sales::lang.commercial_register_no')]); !!}
                                </div>
                               
                            </div>      

                </div>

                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->mobile}}">
                                    {!! Form::text('mobile', $contact->mobile, ['class' => 'form-control','placeholder' => __('sales::lang.mobile')]); !!}
                                </div>
                               
                            </div>      

                </div>
                <div class="col-md-6">
                            <div class="form-group">
                            {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                                <div class="input-group">
                                <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                                    <input type="hidden" id="hidden_id" value="{{$contact->alternate_number}}">
                                    {!! Form::text('alternate_number', $contact->alternate_number, ['class' => 'form-control','placeholder' => __('sales::lang.alternate_number')]); !!}
                                </div>
                               
                            </div>      
                          
                </div>
                <div class="clearfix"></div>
               
      <h4 class="modal-title">@lang( 'sales::lang.Contract_signer_details' )</h4>
      <div class="col-md-12"><hr/></div>
                <br>
                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('first_name', __('sales::lang.first_name_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('first_name_cs', $contactSigners[0]->first_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('first_name_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cs')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        
                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('last_name', __('sales::lang.last_name_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->last_name) ? $contactSigners->last_name : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('last_name_cs', $contactSigners[0]->last_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('last_name_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('english_name', __('sales::lang.english_name_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->english_name) ? $contactSigners->english_name : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('english_name', $contactSigners[0]->english_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('english_name', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div> 


                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('capacity_cs', __('sales::lang.capacity_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->capacity_cs) ? $contactSigners->capacity_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('capacity_cs', $contactSigners[0]->capacity_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.capacity_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('capacity_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.capacity_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('nationality_cs', __('sales::lang.nationality_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->nationality_cs) ? $contactSigners->nationality_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('nationality_cs', $contactSigners[0]->nationality_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.nationality_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('nationality_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.nationality_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div> 
                                                        
                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('email_cs', __('sales::lang.email_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->email_cs) ? $contactSigners->email_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('email_cs', $contactSigners[0]->email_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('email_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('identityNO_cs', __('sales::lang.identityNO_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->identityNO_cs) ? $contactSigners->identityNO_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('identityNO_cs', $contactSigners[0]->identityNO_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.identityNO_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('identityNO_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.identityNO_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                                                            <div class="form-group">
                                                                                        
                                                                                                <div class="input-group">
                                                                                            
                                                                                                <label>
                                                                                                @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                                {!! Form::checkbox('allow_login',  $contactSigners[0]->allow_login, ['class' => 'input-icheck', 'id' => 'allow_login']); !!}
                                                                                                        <strong>@lang('sales::lang.allow_login')</strong>
                                                                                                @else
                                                                                                {!! Form::checkbox('allow_login', 1,  false, ['class' => 'input-icheck', 'id' => 'allow_login']); !!}
                                                                                                        <strong>@lang('sales::lang.allow_login')</strong>
                                                                                                @endif
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>
                                                        </div>

                                                            <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('username_cs', __('sales::lang.username_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->username_cs) ? $contactSigners->username_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('username_cs', $contactSigners[0]->username_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('username_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('password_cs', __('sales::lang.password_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->password_cs) ? $contactSigners->password_cs : '' }}">

                                                                            @if(isset($contactSigners) && $contactSigners->isNotEmpty())
                                                                                {!! Form::text('password_cs', $contactSigners[0]->password_cs ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cs')]); !!}
                                                                            @else
                                                                                {!! Form::text('password_cs', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cs')]); !!}
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                            </div>
                                                        
                                                       
      <div class="clearfix"></div>
               
      <h4 class="modal-title">@lang( 'sales::lang.Contract_follower_details' )</h4>
      <div class="col-md-12"><hr/></div>
                <br>
                                                        <div class="col-md-6">
                                                                         <div class="form-group">
                                                                        {!! Form::label('first_name', __('sales::lang.first_name_cf') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactFollower) && $contactFollower->isNotEmpty())
                                                                                {!! Form::text('first_name_cf', $contactFollower[0]->first_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cf')]); !!}
                                                                            @else
                                                                                {!! Form::text('first_name_cf', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cf')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('last_name', __('sales::lang.last_name_cf') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactFollower) && $contactFollower->isNotEmpty())
                                                                                {!! Form::text('last_name_cf', $contactFollower[0]->last_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cf')]); !!}
                                                                            @else
                                                                                {!! Form::text('last_name_cf', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cf')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('english_name', __('sales::lang.english_name_cf') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactFollower) && $contactFollower->isNotEmpty())
                                                                                {!! Form::text('english_name_cf', $contactFollower[0]->english_name ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cf')]); !!}
                                                                            @else
                                                                                {!! Form::text('english_name_cf', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cf')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('email_cf', __('sales::lang.email_cf') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactFollower) && $contactFollower->isNotEmpty())
                                                                                {!! Form::text('email_cf', $contactFollower[0]->email ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cf')]); !!}
                                                                            @else
                                                                                {!! Form::text('email_cf', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cf')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                        <div class="col-md-6">
                                                                        <div class="form-group">
                                                                        {!! Form::label('mobile_cf', __('sales::lang.mobile_cf') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            <input type="hidden" id="hidden_id" value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                                                            @if(isset($contactFollower) && $contactFollower->isNotEmpty())
                                                                                {!! Form::text('mobile_cf', $contactFollower[0]->mobile ?? "", ['class' => 'form-control', 'placeholder' => __('sales::lang.mobile_cf')]); !!}
                                                                            @else
                                                                                {!! Form::text('mobile_cf', "", ['class' => 'form-control', 'placeholder' => __('sales::lang.mobile_cf')]); !!}
                                                                            @endif


                                                                            </div>
                                                                        </div>
                                                        </div> 

                                                       





        </div>
       
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
   </div><!-- /.modal-content -->

</div><!-- /.modal-dialog -->
</section>
@endsection
