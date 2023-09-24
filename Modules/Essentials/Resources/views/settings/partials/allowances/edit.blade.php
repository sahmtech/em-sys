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

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateAllowance', $allowance->id], 'method' => 'put', 'id' => 'add_allowance_form']) !!}


     
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.add_allowance' )</h4>
    </div>

  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('name', __('essentials::lang.name') . ':') !!}
                {!! Form::text('name',$allowance->name, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('type', __('essentials::lang.type') . ':') !!}
                {!! Form::text('type', $allowance->type, ['class' => 'form-control', 'placeholder' => __('essentials::lang.type'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('allowance_value', __('essentials::lang.allowance_value') . ':') !!}
                {!! Form::text('allowance_value', $allowance->allowance_value, ['class' => 'form-control', 'placeholder' => __('essentials::lang.allowance_value')]) !!}
            </div>
            <div class="form-group col-md-6">
              {!! Form::label('number_of_months', __('essentials::lang.number_of_months') .':') !!}
              {!! Form::text('number_of_months', $allowance->number_of_months, ['class' => 'form-control', 'placeholder' => __('essentials::lang.number_of_months')]) !!}
          </div>
          <div class="form-group col-md-6">
            {!! Form::label('added_to_salary', __('essentials::lang.added_to_salary') . ':') !!}
            {!! Form::text('added_to_salary', $allowance->added_to_salary, ['class' => 'form-control', 'placeholder' => __('essentials::lang.added_to_salary')]) !!}
        </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', $allowance->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') .':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')],$allowance->is_active, ['class' => 'form-control']) !!}
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