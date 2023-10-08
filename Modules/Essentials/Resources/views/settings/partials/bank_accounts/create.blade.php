@extends('layouts.app')
@section('title', __('essentials::lang.bank_accounts'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_bank_accounts')</span>
    </h1>
</section>

<section class="content">
  
      {!! Form::open(['route' => 'storeBank_account']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_bank_account' )</h4>
      </div>
    
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('name', __('essentials::lang.bank_name') . ':*') !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.bank_name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('phone_number', __('essentials::lang.phone_number') . ':*') !!}
                {!! Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.phone_number'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('mobile_number', __('essentials::lang.mobile_number') . ':*') !!}
                {!! Form::text('mobile_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.mobile_number'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('address', __('essentials::lang.address') . ':*') !!}
                {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address'), 'required']) !!}
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