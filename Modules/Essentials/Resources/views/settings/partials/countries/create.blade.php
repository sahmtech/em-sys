@extends('layouts.app')
@section('title', __('essentials::lang.countries'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_countries')</span>
    </h1>
</section>

<section class="content">
  
      {!! Form::open(['route' => 'storeCountry']) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.add_country' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('arabic_name', __('essentials::lang.country_ar_name') . ':*') !!}
                {!! Form::text('arabic_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.country_ar_name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('english_name', __('essentials::lang.country_en_name') . ':*') !!}
                {!! Form::text('english_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.country_en_name'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('nationality', __('essentials::lang.contry_nationality') . ':*') !!}
                {!! Form::text('nationality', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_nationality'), 'required']) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('details', __('essentials::lang.contry_details') . ':') !!}
                {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('is_active', __('essentials::lang.contry_is_active') . ':*') !!}
                {!! Form::select('is_active', ['1' => __('essentials::lang.contry_is_active'), '0' => __('essentials::lang.contry_is_unactive')], null, ['class' => 'form-control']) !!}
            </div>
        </div>
        
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
      @endsection