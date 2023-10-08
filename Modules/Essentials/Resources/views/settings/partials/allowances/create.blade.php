@extends('layouts.app')
@section('title', __('essentials::lang.allowances'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_allowances_types')</span>
    </h1>
</section>
<section class="content">
  
      {!! Form::open(['route' => 'storeAllowance']) !!}

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_allowance' )</h4>
      </div>
  
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                {!! Form::text('name',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                {!! Form::text('type',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.type'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('allowance_value', __('essentials::lang.allowance_value') . ':*') !!}
                {!! Form::text('allowance_value',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.allowance_value'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
              {!! Form::label('number_of_months', __('essentials::lang.number_of_months') .':*') !!}
              {!! Form::text('number_of_months', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.number_of_months'), 'required']) !!}
          </div>
          <div class="form-group col-md-6">
            {!! Form::label('added_to_salary', __('essentials::lang.added_to_salary') . ':*') !!}
      
            {!! Form::select('added_to_salary', ['1' => __('essentials::lang.added_to_salary'), '0' => __('essentials::lang.not_added_to_salary')],null, ['class' => 'form-control']) !!}
        </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') .':*') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')],null, ['class' => 'form-control']) !!}
            </div>
        </div>
        
      </div>
  
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
      @endsection