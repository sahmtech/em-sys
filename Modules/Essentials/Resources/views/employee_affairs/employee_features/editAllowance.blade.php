@extends('layouts.app')
@section('title', __('essentials::lang.allowances'))

@section('content')
@include('essentials::layouts.nav_employee_features')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.allowances')</span>
    </h1>
</section>


<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['route' => ['updateUserAllowance', $UserAllowance->id], 'method' => 'put', 'id' => 'edit_allowance_form']) !!}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'essentials::lang.edit_allowance' )</h4>
        </div>
    
        <div class="modal-body">
          <div class="row">
             
            <div class="form-group col-md-6">
                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                {!! Form::select('employee',$users, $UserAllowance->user_id, ['class' => 'form-control select2']) !!}
              </div>
            <div class="form-group col-md-6">
                {!! Form::label('allowance', __('essentials::lang.allowance') . ':*') !!}
                {!! Form::select('allowance',$allowance_types, $UserAllowance->allowance_deduction_id, ['class' => 'form-control select2']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('amount', __('essentials::lang.amount') . ':*') !!}
                {!! Form::number('amount', $UserAllowance->amount, ['class' => 'form-control', 'placeholder' => __('essentials::lang.amount'), 'required']) !!}
            </div>
          </div>
          
        </div>
    
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    
        {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
  @endsection