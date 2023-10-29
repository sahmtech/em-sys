@extends('layouts.app')
@section('title', __('essentials::lang.insurance_contracts'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.insurance_contracts')</span>
    </h1>
</section>


<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateInsuranceContract', $contract->id], 'method' => 'put', 'id' => 'add_contract_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_contract' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                {!! Form::select('insurance_company', $insuramce_companies,$contract->insurance_company_id, ['class' => 'form-control select2','placeholder' => __('essentials::lang.insurance_company'),  'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('policy_number', __('essentials::lang.insurance_policy_number') . ':*') !!}
                {!! Form::number('policy_number', $contract->policy_number, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_policy_number'), 'required']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('policy_value', __('essentials::lang.insurance_policy_value') . ':*') !!}
                {!! Form::number('policy_value', $contract->policy_number, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_policy_value'), 'required']) !!}
            </div>

            
            <div class="form-group col-md-6">
                {!! Form::label('insurance_employees_count', __('essentials::lang.insurance_employees_count') . ':*') !!}
                {!! Form::number('insurance_employees_count', $contract->employees_count, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_employees_count'), 'required']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('insurance_dependents_count', __('essentials::lang.insurance_dependents_count') . ':*') !!}
                {!! Form::number('insurance_dependents_count', $contract->dependents_count, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_dependents_count'), 'required']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('insurance_start_date', __('essentials::lang.insurance_start_date') . ':*') !!}
                {!! Form::date('insurance_start_date', $contract->insurance_start_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_start_date'), 'required']) !!}
            </div>

      
            <div class="form-group col-md-6">
                {!! Form::label('insurance_end_date', __('essentials::lang.insurance_end_date') . ':*') !!}
                {!! Form::date('insurance_end_date', $contract->insurance_end_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_end_date'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('insurance_attachments', __('essentials::lang.insurance_attachments') . ':') !!}
                {!! Form::file('insurance_attachments', $contract->attachments2, ['class' => 'form-control', 'placeholder' => __('essentials::lang.insurance_attachments'), 'required']) !!}
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