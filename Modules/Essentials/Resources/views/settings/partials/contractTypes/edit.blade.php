@extends('layouts.app')
@section('title', __('essentials::lang.contract_types'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.contract_types')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateContractType', $contract_type->id], 'method' => 'put', 'id' => 'add_contract_type_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_contract_type' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
           
        
              <div class="form-group col-md-6">
                {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                {!! Form::text('type', $contract_type->type, ['class' => 'form-control', 'placeholder' => __('essentials::lang.type'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', $contract_type->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' =>2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], $contract_type->is_active, ['class' => 'form-control']) !!}
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