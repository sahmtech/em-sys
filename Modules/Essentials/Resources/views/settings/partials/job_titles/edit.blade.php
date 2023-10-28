@extends('layouts.app')
@section('title', __('essentials::lang.job_titles'))
@include('essentials::layouts.nav_hrm_setting')
@section('content')
<section class="content-header">
    <h1>@lang('essentials::lang.job_titles')
        <small>@lang('essentials::lang.manage_job_titles')</small>
    </h1>
</section>
<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateJob_title', $job_title->id], 'method' => 'put', 'id' => 'add_job_title_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_country' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('job_title', __('essentials::lang.job_title') . ':') !!}
                {!! Form::text('job_title', $job_title->job_title, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('job_code', __('essentials::lang.job_code') . ':') !!}
                {!! Form::text('job_code', $job_title->job_code, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_code'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('responsibilities', __('essentials::lang.responsibilities') . ':') !!}
                {!! Form::textarea('responsibilities', $job_title->responsibilities, ['class' => 'form-control', 'placeholder' => __('essentials::lang.responsibilities'), 'rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('supervision_scope', __('essentials::lang.supervision_scope') . ':') !!}
                {!! Form::text('supervision_scope', $job_title->supervision_scope, ['class' => 'form-control', 'placeholder' => __('essentials::lang.supervision_scope')]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('authorization_and_permissions', __('essentials::lang.authorization_and_permissions') . ':') !!}
                {!! Form::textarea('authorization_and_permissions', $job_title->authorization_and_permissions, ['class' => 'form-control', 'placeholder' => __('essentials::lang.authorization_and_permissions'), 'rows' => 2]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', $job_title->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
            
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.contry_is_active') . ':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.contry_is_unactive')], $job_title->is_active, ['class' => 'form-control']) !!}
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