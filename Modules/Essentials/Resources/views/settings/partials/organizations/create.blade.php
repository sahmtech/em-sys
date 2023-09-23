@extends('layouts.app')
@section('title', __('country.countries'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_organizations')</span>
    </h1>
</section>
<section class="content">
  
      {!! Form::open(['route' => 'storeOrganization']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_organization' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="modal-body">
            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('name', __('essentials::lang.organization_name') . ':*') !!}
                    {!! Form::text('name',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_name'), 'required']) !!}
                </div>
            
                <div class="form-group col-md-6">
                    {!! Form::label('code', __('essentials::lang.organization_code') . ':*') !!}
                    {!! Form::text('code', null , ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_code'), 'required']) !!}
                </div>
            
                <div class="form-group col-md-6">
                    {!! Form::label('level_type', __('essentials::lang.organization_level_type') . ':') !!}
                    {!! Form::select('level_type', ['one_level' => __('essentials::lang.one_level'), 'other' => __('essentials::lang.other_level')], null, ['class' => 'form-control']) !!}
                </div> 
    
                <div class="form-group col-md-6">
                    {!! Form::label('parent_level', __('essentials::lang.organization_parent_level') . ':') !!}
                    {!! Form::text('parent_level', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_parent_level')]) !!}
                </div>
    
                <div class="form-group col-md-6">
                    {!! Form::label('account_number', __('essentials::lang.organization_account_number') . ':') !!}
                    {!! Form::text('account_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_account_number')]) !!}
                </div>
               
                <div class="form-group col-md-6">
                    {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                    {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')],null, ['class' => 'form-control']) !!}
                </div> 
                <div class="form-group col-md-12">
                    {!! Form::label('bank', __( 'essentials::lang.organization_bank' ) . ':') !!}
                      {!! Form::select('bank', $banks, null, ['class' => 'form-control select2', 'placeholder' => __( 'essentials::lang.bank' ) ]); !!}
                  </div>
                <div class="form-group col-md-6">
                    {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                    {!! Form::textarea('details',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' =>2]) !!}
                </div>
            
                
            </div>
            
          </div>
        
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
      @endsection