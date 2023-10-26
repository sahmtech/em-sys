@extends('layouts.app')
@section('title', __('essentials::lang.travel_categories'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_travel_categories')</span>
    </h1>
</section>
<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateTravel_categorie', $travel_categorie->id], 'method' => 'put', 'id' => 'add_travel_categorie_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.editTravel_categorie' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('name', __('essentials::lang.name') . ':') !!}
                {!! Form::text('name', $travel_categorie->name, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('employee_ticket_value', __('essentials::lang.employee_ticket_value') . ':') !!}
                {!! Form::text('employee_ticket_value',$travel_categorie->employee_ticket_value, ['class' => 'form-control', 'placeholder' => __('essentials::lang.employee_ticket_value'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('wife_ticket_value', __('essentials::lang.wife_ticket_value') . ':') !!}
                {!! Form::text('wife_ticket_value', $travel_categorie->wife_ticket_value, ['class' => 'form-control', 'placeholder' => __('essentials::lang.wife_ticket_value')]) !!}
            </div>
            <div class="form-group col-md-6">
              {!! Form::label('children_ticket_value', __('essentials::lang.children_ticket_value') . ':') !!}
              {!! Form::text('children_ticket_value', $travel_categorie->children_ticket_value, ['class' => 'form-control', 'placeholder' => __('essentials::lang.children_ticket_value')]) !!}
          </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                {!! Form::textarea('details', $travel_categorie->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' =>2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], $travel_categorie->is_active, ['class' => 'form-control']) !!}
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