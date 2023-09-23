@extends('layouts.app')
@section('title', __('essentials::lang.basic_salary_types'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_basic_salary_types')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateBasicSalary', $basic_salary_type->id], 'method' => 'put', 'id' => 'add_basic_salary_type_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_basic_salary_type' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('type', __('essentials::lang.basic_salary_type') . ':') !!}
                {!! Form::text('type',$basic_salary_type->type, ['class' => 'form-control', 'placeholder' => __('essentials::lang.basic_salary_type'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.contry_details') . ':') !!}
                {!! Form::textarea('details', $basic_salary_type->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' =>2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], $basic_salary_type->is_active, ['class' => 'form-control']) !!}
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