@extends('layouts.app')
@section('title', __('organization.organizations'))

@section('content')
<section class="content-header">
    <h1>@lang('essentials::lang.organizations')
        <small>@lang('essentials::lang.manage_organizations')</small>
    </h1>
</section>
<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateOrganization', $organization->id], 'method' => 'put', 'id' => 'add_organization_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_organization' )</h4>
      </div>
    
            
      <div class="modal-body">
        <div class="modal-body">
            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('name', __('essentials::lang.organization_name') . ':*') !!}
                    {!! Form::text('name', $organization->name, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_name'), 'required']) !!}
                </div>
            
                <div class="form-group col-md-6">
                    {!! Form::label('code', __('essentials::lang.organization_code') . ':*') !!}
                    {!! Form::text('code',  $organization->code , ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_code'), 'required']) !!}
                </div>
            
                
                <div class="form-group col-md-6">
                    {!! Form::label('level_type', __('essentials::lang.organization_level_type') . ':') !!}
                    {!! Form::select('level_type', ['one_level' => __('essentials::lang.one_level'), 'other' => __('essentials::lang.other_level')], $organization->level_type, ['class' => 'form-control']) !!}
                </div> 
                <div class="form-group col-md-6">
                    {!! Form::label('parent_level', __('essentials::lang.organization_parent_level') . ':') !!}
                    {!! Form::text('parent_level',  $organization->parent_level, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_parent_level')]) !!}
                </div>
    
                <div class="form-group col-md-6">
                    {!! Form::label('account_number', __('essentials::lang.organization_account_number') . ':') !!}
                    {!! Form::text('account_number',  $organization->account_number, ['class' => 'form-control', 'placeholder' => __('essentials::lang.organization_account_number')]) !!}
                </div>
               
                <div class="form-group col-md-6">
                    {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                    {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], $organization->is_active, ['class' => 'form-control']) !!}
                </div> 
                <div class="form-group col-md-12">
                    {!! Form::label('bank', __( 'essentials::lang.organization_bank' ) . ':') !!}
                      {!! Form::select('bank', $banks, $bank->name, ['class' => 'form-control select2', 'placeholder' => __( 'essentials::lang.bank' ) ]); !!}
                  </div>
                <div class="form-group col-md-6">
                    {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                    {!! Form::textarea('details', $organization->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 3]) !!}
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
  @endsection