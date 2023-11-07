@extends('layouts.app')
@section('title', __('essentials::lang.regions'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')
<!-- Content Header (Page header) -->


<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['route' => ['updateRegion', $region->id], 'method' => 'put', 'id' => 'add_region_form']) !!}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'essentials::lang.add_region' )</h4>
        </div>
    
        <div class="modal-body">
          <div class="row">
              <div class="form-group col-md-6">
                  {!! Form::label('arabic_name', __('essentials::lang.ar_name') . ':') !!}
                  {!! Form::text('arabic_name', (json_decode($region->name,true))['ar'], ['class' => 'form-control', 'placeholder' => __('essentials::lang.ar_name'), 'required']) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('english_name', __('essentials::lang.en_name') . ':') !!}
                  {!! Form::text('english_name', (json_decode($region->name,true))['en'], ['class' => 'form-control', 'placeholder' => __('essentials::lang.en_name'), 'required']) !!}
              </div>
          
              <div class="form-group col-md-12">
                  {!! Form::label('city', __( 'essentials::lang.city' ) . ':') !!}
                    {!! Form::select('city', $cities, (json_decode($city2->name,true))['ar'], ['class' => 'form-control select2' ]); !!}
                </div>
  
              <div class="form-group col-md-6">
                  {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                  {!! Form::textarea('details', $region->details, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details'), 'rows' => 2]) !!}
              </div>
          
              <div class="form-group col-md-6">
                  {!! Form::label('is_active', __('essentials::lang.is_active') . ':') !!}
                  {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], $region->is_active, ['class' => 'form-control']) !!}
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