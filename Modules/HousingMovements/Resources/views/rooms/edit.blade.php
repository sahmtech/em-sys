@extends('layouts.app')
@section('title', __('housingmovements::lang.rooms'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.rooms')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['route' => ['updateRoom', $room->id], 'method' => 'put', 'id' => 'add_room_form']) !!}

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'housingmovements::lang.edit_room' )</h4>
        </div>
    
        <div class="modal-body">
            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                    {!! Form::number('room_number', $room->room_number, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.room_number'), 'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('area', __('housingmovements::lang.area') . ':') !!}
                    {!! Form::text('area', $room->area, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.area'),'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                    {!! Form::select('htr_building', $buildings, $room->htr_building_id, ['class' => 'form-control select2', 'placeholder' => __('housingmovements::lang.htr_building'), 'required']) !!}
                </div>

            
                <div class="form-group col-md-6">
                    {!! Form::label('beds_count', __('housingmovements::lang.beds_count') . ':*') !!}
                    {!! Form::number('beds_count', $room->beds_count, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.beds_count'), 'required']) !!}
                </div>
                
                <div class="form-group col-md-6">
                    {!! Form::label('contents', __('housingmovements::lang.contents') . ':*') !!}
                    {!! Form::textarea('contents', $room->contents, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.contents'),'row'=>'2']) !!}
                </div>
            </div>
          
        </div>
    
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    
        {!! Form::close() !!}
  
    </div>
  </div>
  @endsection