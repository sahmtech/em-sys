@extends('layouts.app')
@section('title', __('essentials::lang.entitlement_types'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_entitlement_types')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateEntitlement', $entitlement->id], 'method' => 'put', 'id' => 'add_entitlement_form']) !!}


     
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.add_entitlement' )</h4>
    </div>

  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('name', __('essentials::lang.name') . ':') !!}
                {!! Form::text('name',$entitlement->name, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('percentage', __('essentials::lang.percentage') . ':') !!}
                {!! Form::text('percentage', $entitlement->percentage, ['class' => 'form-control', 'placeholder' => __('essentials::lang.percentage'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('from', __('essentials::lang.from') . ':') !!}
                {!! Form::text('from', $entitlement->from, ['class' => 'form-control', 'placeholder' => __('essentials::lang.from')]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', $entitlement->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')],$entitlement->is_active, ['class' => 'form-control']) !!}
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