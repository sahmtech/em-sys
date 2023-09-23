@extends('layouts.app')
@section('title', __('essentials::lang.cities'))

@section('content')
@include('essentials::layouts.nav_hrm')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.manage_cities')</span>
    </h1>
</section>


<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['route' => ['updateCity', $city->id], 'method' => 'put', 'id' => 'add_city_form']) !!}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'essentials::lang.add_city' )</h4>
        </div>
    
        <div class="modal-body">
          <div class="row">
              <div class="form-group col-md-6">
                  {!! Form::label('arabic_name', __('essentials::lang.city_ar_name') . ':') !!}
                  {!! Form::text('arabic_name', (json_decode($city->name,true))['ar'], ['class' => 'form-control', 'placeholder' => __('essentials::lang.city_ar_name'), 'required']) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('english_name', __('essentials::lang.city_en_name') . ':') !!}
                  {!! Form::text('english_name', (json_decode($city->name,true))['ar'], ['class' => 'form-control', 'placeholder' => __('essentials::lang.city_en_name'), 'required']) !!}
              </div>
          
              <div class="form-group col-md-12">
                  {!! Form::label('country', __( 'essentials::lang.country' ) . ':') !!}
                    {!! Form::select('country', $countries, (json_decode($country2->name,true))['ar'], ['class' => 'form-control select2' ]); !!}
                </div>
  
              <div class="form-group col-md-6">
                  {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                  {!! Form::textarea('details', $city->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('is_active', __('essentials::lang.city_is_active') . ':') !!}
                  {!! Form::select('is_active', ['1' => __('essentials::lang.city_is_active'), '0' => __('essentials::lang.city_is_unactive')], $city->is_active, ['class' => 'form-control']) !!}
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