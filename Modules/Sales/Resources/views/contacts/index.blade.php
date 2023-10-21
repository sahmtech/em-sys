@extends('layouts.app')
@section('title', __('sales::lang.sales'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('sales::lang.all_your_customers')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])
  
        <div class="col-md-3">
            <div class="form-group">
                <label>
                    {!! Form::checkbox('has_sell_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_due']); !!} <strong>@lang('lang_v1.sell_due')</strong>
                </label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>
                    {!! Form::checkbox('has_sell_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_return']); !!} <strong>@lang('lang_v1.sell_return')</strong>
                </label>
            </div>
        </div>

  
    <div class="col-md-3">
        <div class="form-group">
            <label>
                {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']); !!} <strong>@lang('lang_v1.advance_balance')</strong>
            </label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>
                {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']); !!} <strong>@lang('lang_v1.opening_balance')</strong>
            </label>
        </div>
    </div>
  
        <div class="col-md-3">
            <div class="form-group">
                <label for="has_no_sell_from">@lang('lang_v1.has_no_sell_from'):</label>
                {!! Form::select('has_no_sell_from', ['one_month' => __('lang_v1.one_month'), 'three_months' => __('lang_v1.three_months'), 'six_months' => __('lang_v1.six_months'), 'one_year' => __('lang_v1.one_year')], null, ['class' => 'form-control', 'id' => 'has_no_sell_from', 'placeholder' => __('messages.please_select')]); !!}
            </div>
        </div>

      
   

   

    <div class="col-md-3">
        <div class="form-group">
            <label for="status_filter">@lang('sale.status'):</label>
            {!! Form::select('status_filter', ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')], null, ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')]); !!}
        </div>
    </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addCountryModal">
                    <i class="fa fa-plus"></i> @lang('sales::lang.add_contact')
                </button>
            </div>
            @endslot
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cust_table">
                    <thead>
                        <tr>
                         <th>@lang('messages.action')</th>
                            <th>@lang('sales::lang.contact_number')</th>
                            <th>@lang('sales::lang.contact_name_ar')</th>
                            <th>@lang('sales::lang.contact_name_en')</th>                           
                            <th>@lang('sales::lang.commercial_register_no')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('sales::lang.contact_city')</th>
                         
                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent


 <div class="modal fade" id="addCountryModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                            {!! Form::open(['route' => 'sale.storeCustomer']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('sales::lang.add_contact')</h4>
                            </div>

                        <div class="modal-body">
                                            @php
                                            $type ="lead";
                                            @endphp
                                            <div class="row">            
                                                    <div class="col-md-4 contact_type_div">
                                                        <div class="form-group">
                                                            {!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
                                                            <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-user"></i>
                                                                </span>
                                                                {!! Form::select('type', $types, $type , ['class' => 'form-control', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 mt-15">
                                                    
                                                    <label class="radio-inline">
                                                        <input type="radio" name="contact_type_radio" id="inlineRadio2" value="business">
                                                        @lang('business.business')
                                                    </label>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
                                                            <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-id-badge"></i>
                                                                </span>
                                                                {!! Form::text('contact_id', null, ['class' => 'form-control','placeholder' => __('lang_v1.contact_id')]); !!}
                                                            </div>
                                                            <p class="help-block">
                                                                @lang('lang_v1.leave_empty_to_autogenerate')
                                                            </p>
                                                        </div>
                                                    </div>
                                        

                                                    <div class="col-md-4 ">
                                                            <div class="form-group">
                                                                {!! Form::label('first_name', __('sales::lang.first_name')  . ':*') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-briefcase"></i>
                                                                    </span>
                                                                    {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name')]); !!}
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="col-md-4 ">
                                                            <div class="form-group">
                                                                {!! Form::label('last_name', __('sales::lang.last_name')  . ':*') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-briefcase"></i>
                                                                    </span>
                                                                    {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name')]); !!}
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="col-md-4 ">
                                                            <div class="form-group">
                                                                {!! Form::label('name_en', __('sales::lang.name_en') . ':*') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-briefcase"></i>
                                                                    </span>
                                                                    {!! Form::text('name_en', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.name_en')]); !!}
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="col-md-4 business">
                                                            <div class="form-group">
                                                                {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-briefcase"></i>
                                                                    </span>
                                                                    {!! Form::text('supplier_business_name', null, ['class' => 'form-control', 'placeholder' => __('business.business_name')]); !!}
                                                                </div>
                                                            </div>
                                                    </div>
                                            
                                                    <div class="col-md-4 business">
                                                            <div class="form-group">
                                                                {!! Form::label('commercial_register_no', __('sales::lang.commercial_register_no') . ':') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-briefcase"></i>
                                                                    </span>
                                                                    {!! Form::text('commercial_register_no', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.commercial_register_no')]); !!}
                                                                </div>
                                                            </div>
                                                    </div>
                                            
                                                  

                                            
                                                    <div class="clearfix"></div>
                                                        
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-mobile"></i>
                                                                    </span>
                                                                    {!! Form::text('mobile', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                                            
                                            
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-phone"></i>
                                                                    </span>
                                                                    {!! Form::text('alternate_number', null, ['class' => 'form-control', 'placeholder' => __('contact.alternate_contact_number')]); !!}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                {!! Form::label('landline', __('contact.landline') . ':') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-phone"></i>
                                                                    </span>
                                                                    {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => __('contact.landline')]); !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                {!! Form::label('email', __('business.email') .  ':*') !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-envelope"></i>
                                                                    </span>
                                                                    {!! Form::email('email', null, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="clearfix"></div>
                                                       
                                                        <!-- User in create leads -->
                                                        <div class="col-md-6 lead_additional_div">
                                                            <div class="form-group">
                                                                {!! Form::label('user_id', __('lang_v1.assigned_to') . ':' ) !!}
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="fa fa-user"></i>
                                                                    </span>
                                                                    {!! Form::select('user_id[]', $users ?? [], null , ['class' => 'form-control select2', 'id' => 'user_id', 'multiple', 'required', 'style' => 'width: 100%;']); !!}
                                                                </div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>  
                                                                                                
                                        <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="button" id="moreInfoButton" class="btn btn-primary center-block more_btn" data-target="#more_div">@lang('sales::lang.add_Contract_signer') <i class="fa fa-chevron-down"></i></button>
                                                    </div>

                                                    <div id="more_div" class="hide">
                                                    {!! Form::hidden('position', null, ['id' => 'position']); !!}
                                                        <div class="col-md-12"><hr/></div>

                                                      
                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('first_name', __('sales::lang.first_name_cs') .  ':*') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('first_name_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cs'),'required']); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('last_name', __('sales::lang.last_name_cs') .  ':*') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('last_name_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cs'),'required']); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>
                                                      

                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('english_name', __('sales::lang.english_name_cs') . ':*') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('english_name_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cs'),'required']); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>
                                                
                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('capacity_cs', __('sales::lang.capacity_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('capacity_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.capacity_cs')]); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('nationality_cs', __('sales::lang.nationality_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('nationality_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.nationality_cs')]); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('email_cs', __('sales::lang.email_cs') . ':') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('email_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cs')]); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                                        <div class="form-group">
                                                                        {!! Form::label('identityNO_cs', __('sales::lang.identityNO_cs') .  ':*') !!}
                                                                            <div class="input-group">
                                                                            <span class="input-group-addon">
                                                                                <i class="fa fa-info"></i>
                                                                            </span>
                                                                            {!! Form::text('identityNO_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.identityNO_cs')]); !!}
                                                                            </div>
                                                                        </div>
                                                        </div>
                                                                            <div class="col-md-3">
                                                                                            <div class="form-group">
                                                                                            {!! Form::label('mobile_cs', __('sales::lang.mobile_cs') . ':*') !!}
                                                                                                <div class="input-group">
                                                                                                <span class="input-group-addon">
                                                                                                    <i class="fa fa-info"></i>
                                                                                                </span>
                                                                                                {!! Form::text('mobile_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.mobile_cs')]); !!}
                                                                                                </div>
                                                                                            </div>
                                                                            </div>

                                                                            <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <div class="input-group">
                                                                                            <label>
                                                                                                {!! Form::checkbox('allow_login', 1, false, ['id' => 'allow_login_checkbox']); !!} <strong>@lang('sales::lang.allow_login')</strong>
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                    <div class="col-md-3" id="username_cs_wrapper" style="display: none;">
                                                                                        <div class="form-group">
                                                                                            {!! Form::label('username_cs', __('sales::lang.username_cs') . ':') !!}
                                                                                            <div class="input-group">
                                                                                                <span class="input-group-addon">
                                                                                                    <i class="fa fa-info"></i>
                                                                                                </span>
                                                                                                {!! Form::text('username_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cs')]); !!}
                                                                                            </div>
                                                                                        </div>
                                                                                                                                                                            </div>

                                                                                        <div class="col-md-3" id="password_cs_wrapper" style="display: none;">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('password_cs', __('sales::lang.password_cs') . ':') !!}
                                                                                                <div class="input-group">
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-info"></i>
                                                                                                    </span>
                                                                                                    {!! Form::text('password_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cs')]); !!}
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="col-md-3" id="confirm_password_cs_wrapper" style="display: none;">
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('confirm_password_cs', __('sales::lang.confirm_password_cs') . ':') !!}
                                                                                                <div class="input-group">
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-info"></i>
                                                                                                    </span>
                                                                                                    {!! Form::text('confirm_password_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.confirm_password_cs')]); !!}
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>



                                                                                                                                                    </div>
                                                                                                                                                    <div class="clearfix"></div>
                                                                                                                                        </div>
                                                                                                                                    
                                                                                                <br>
                                                                                                                                        <div class="row">
                                                                                                                                                    <div class="col-md-12">
                                                                                                                                                        <button type="button" id="contract_follower" class="btn btn-primary center-block more_btn" data-target="#more_div">@lang('sales::lang.add_Contract_follower') <i class="fa fa-chevron-down"></i></button>
                                                                                                                                                    </div>

                                                                                                                                                    <div id="more_div2" class="hide">
                                                                                                                                                    {!! Form::hidden('position', null, ['id' => 'position']); !!}
                                                                                                                                                        <div class="col-md-12"><hr/></div>

                                                                                                                                                    
                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                        {!! Form::label('first_name', __('sales::lang.first_name_cf') . ':*') !!}
                                                                                                                                                                            <div class="input-group">
                                                                                                                                                                            <span class="input-group-addon">
                                                                                                                                                                                <i class="fa fa-info"></i>
                                                                                                                                                                            </span>
                                                                                                                                                                            {!! Form::text('first_name_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name_cf')]); !!}
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                        </div>
                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                        {!! Form::label('last_name', __('sales::lang.last_name_cf') . ':*') !!}
                                                                                                                                                                            <div class="input-group">
                                                                                                                                                                            <span class="input-group-addon">
                                                                                                                                                                                <i class="fa fa-info"></i>
                                                                                                                                                                            </span>
                                                                                                                                                                            {!! Form::text('last_name_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cf')]); !!}
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                        </div>

                                                                                                                                                    

                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                        {!! Form::label('english_name', __('sales::lang.english_name_cf') .  ':*') !!}
                                                                                                                                                                            <div class="input-group">
                                                                                                                                                                            <span class="input-group-addon">
                                                                                                                                                                                <i class="fa fa-info"></i>
                                                                                                                                                                            </span>
                                                                                                                                                                            {!! Form::text('english_name_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.english_name_cf')]); !!}
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                        </div>
                                                                                                                                                
                                                                                                                                                    

                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                        {!! Form::label('email_cf', __('sales::lang.email_cf') . ':') !!}
                                                                                                                                                                            <div class="input-group">
                                                                                                                                                                            <span class="input-group-addon">
                                                                                                                                                                                <i class="fa fa-info"></i>
                                                                                                                                                                            </span>
                                                                                                                                                                            {!! Form::text('email_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cf')]); !!}
                                                                                                                                                                            </div>
                                                                                                                                                                        </div>
                                                                                                                                                        </div>
                                                                                                                                                    
                                                                                                                                                                            <div class="col-md-3">
                                                                                                                                                                                            <div class="form-group">
                                                                                                                                                                                            {!! Form::label('mobile_cf', __('sales::lang.mobile_cf') . ':*') !!}
                                                                                                                                                                                                <div class="input-group">
                                                                                                                                                                                                <span class="input-group-addon">
                                                                                                                                                                                                    <i class="fa fa-info"></i>
                                                                                                                                                                                                </span>
                                                                                                                                                                                                {!! Form::text('mobile_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.mobile_cf')]); !!}
                                                                                                                                                                                                </div>
                                                                                                                                                                                            </div>
                                                                                                                                                                            </div>

                                                                                                                                                                            <div class="col-md-12">
                                                                                                    <div class="form-group">
                                                                                                        <div class="input-group">
                                                                                                            <label>
                                                                                                                {!! Form::checkbox('allow_login_cf', 1, false, [ 'id' => 'allow_login_cf_checkbox']); !!} <strong>@lang('sales::lang.allow_login')</strong>
                                                                                                            </label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-3" id="username_cf_wrapper" style="display: none;">
                                                                                                    <div class="form-group">
                                                                                                        {!! Form::label('username_cf', __('sales::lang.username_cf') . ':') !!}
                                                                                                        <div class="input-group">
                                                                                                            <span class="input-group-addon">
                                                                                                                <i class="fa fa-info"></i>
                                                                                                            </span>
                                                                                                            {!! Form::text('username_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cf')]); !!}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-3" id="password_cf_wrapper" style="display: none;">
                                                                                                    <div class="form-group">
                                                                                                        {!! Form::label('password_cf', __('sales::lang.password_cf') . ':') !!}
                                                                                                        <div class="input-group">
                                                                                                            <span class="input-group-addon">
                                                                                                                <i class="fa fa-info"></i>
                                                                                                            </span>
                                                                                                            {!! Form::text('password_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cf')]); !!}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class="col-md-3" id="confirm_password_cf_wrapper" style="display: none;">
                                                                                                    <div class="form-group">
                                                                                                        {!! Form::label('confirm_password_cf', __('sales::lang.confirm_password_cf') . ':') !!}
                                                                                                        <div class="input-group">
                                                                                                            <span class="input-group-addon">
                                                                                                                <i class="fa fa-info"></i>
                                                                                                            </span>
                                                                                                            {!! Form::text('confirm_password_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.confirm_password_cf')]); !!}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>


                                                                                                                                                    </div>
                                                                                                                                        </div>
                                                                                                                                        @include('layouts.partials.module_form_part')




                                                                                                                                            <div class="modal-footer">
                                                                                                                                                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                                                                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                                                                                                                                            </div>
                                                                                                                                {!! Form::close() !!}
                                                                                                                        </div>

                                        
                    </div>
                </div>
</div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
    var customers_table = $('#cust_table').DataTable({
        ajax:'', 
        processing: true,
        serverSide: true,
        
        
       
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'contact_id', name: 'contact_id' },
            { data: 'name', name: 'name' },
            { data: 'english_name', name: 'english_name' },
            { data: 'commercial_register_no', name: 'commercial_register_no' },
            { data: 'mobile', name: 'mobile' },
            { data: 'email', name: 'email' },
            { data: 'city', name: 'city' }
        ]
    });
   
});





    $(document).on('click', 'button.delete_country_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_country,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var token = "{{ csrf_token() }}";
                    console.log(href);
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: {
                            "_token": token,
                            "_method": "DELETE"
                        },
                        success: function (data) {
                            if (typeof cust_table !== 'undefined') {
                                window.location.reload();
                              //  cust_table.ajax.reload();
                            } else {
                                console.log('cust_table is not defined.');
                            }
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                        // success: function (result) {
                        //     if (result.success == true) {
                        //         toastr.success(result.msg);
                        //         countries_table.ajax.reload();
                        //     } else {
                        //         toastr.error(result.msg);
                        //     }
                        // }
                    });
                }
            });
        });

   

</script>


<script>
    $(document).ready(function () {
        $('#moreInfoButton').click(function () {
            $('#more_div').toggleClass('hide');
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#contract_follower').click(function () {
            $('#more_div2').toggleClass('hide');
        });
    });
</script>

<script type="text/javascript">
$(document).ready(function() {
    $('#allow_login_checkbox').change(function() {
        if(this.checked) {
            $('#username_cs_wrapper').show();
            $('#password_cs_wrapper').show();
            $('#confirm_password_cs_wrapper').show();
        } else {
            $('#username_cs_wrapper').hide();
            $('#password_cs_wrapper').hide();
            $('#confirm_password_cs_wrapper').hide();
        }
    });
});

$(document).ready(function() {
    $('#allow_login_cf_checkbox').change(function() {
        if(this.checked) {
            $('#username_cf_wrapper').show();
            $('#password_cf_wrapper').show();
            $('#confirm_password_cf_wrapper').show();
        } else {
            $('#username_cf_wrapper').hide();
            $('#password_cf_wrapper').hide();
            $('#confirm_password_cf_wrapper').hide();
        }
    });
});

</script>

@endsection