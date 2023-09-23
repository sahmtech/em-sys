@extends('layouts.app')
@section('title', __('essentials::lang.job_titles'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>@lang('essentials::lang.manage_job_titles')</small>
    </h1>
</section>
<section class="content">
  
      {!! Form::open(['route' => 'storeJob_title']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_job_title' )</h4>
      </div>
    
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('job_title', __('essentials::lang.job_title') . ':*') !!}
                {!! Form::text('job_title', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('job_code', __('essentials::lang.job_code') . ':*') !!}
                {!! Form::text('job_code', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_code'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('responsibilities', __('essentials::lang.responsibilities') . ':*') !!}
                {!! Form::textarea('responsibilities', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.responsibilities'),  'required','rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('supervision_scope', __('essentials::lang.supervision_scope') . ':*') !!}
                {!! Form::text('supervision_scope', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.supervision_scope'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('authorization_and_permissions', __('essentials::lang.authorization_and_permissions') . ':*') !!}
                {!! Form::textarea('authorization_and_permissions', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.authorization_and_permissions'), 'required', 'rows' => 2]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
            
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
            </div>
        </div>
        
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
      @endsection