@extends('layouts.app')
@section('title', __('sales::lang.contract_itmes'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('sales::lang.contract_itmes')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateItem', $item->id], 'method' => 'put', 'id' => 'add_item_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'sales::lang.edit_item' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
        <div class="form-group col-md-6">
                        {!! Form::label('number_of_item', __('sales::lang.number_of_item') . ':*') !!}
                        {!! Form::number('number_of_item', $item->number_of_item, ['class' => 'form-control', 'placeholder' => __('sales::lang.number_of_item'), 'required']) !!}
                    </div>
                    
                    <div class="form-group col-md-6">
                        {!! Form::label('name_of_item', __('sales::lang.name_of_item') . ':*') !!}
                        {!! Form::text('name_of_item', $item->name_of_item, ['class' => 'form-control', 'placeholder' => __('sales::lang.name_of_item'), 'required']) !!}
                    </div>


                    <div class="form-group col-md-6">
                        {!! Form::label('details', __('sales::lang.details') . ':') !!}
                        {!! Form::textarea('details', $item->details, ['class' => 'form-control', 'placeholder' => __('sales::lang.details'), 'rows' => 2]) !!}
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