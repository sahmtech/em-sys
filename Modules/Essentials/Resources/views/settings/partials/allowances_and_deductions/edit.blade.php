@extends('layouts.app')
@section('title', __('essentials::lang.allowances_and_deductions'))

@section('content')
@include('essentials::layouts.nav_hrm_setting')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.allowances_and_deductions')</span>
    </h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'update'], $allowance->id), 'method' => 'put', 'id' => 'add_allowance_form']) !!}


     
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.edit_allowance' )</h4>
    </div>

  
      <div class="modal-body">
        <div class="row">
            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('description', __('essentials::lang.description') . ':*') !!}
                    {!! Form::text('description', $allowance->description, ['class' => 'form-control', 'placeholder' => __('essentials::lang.description'), 'required']) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                    {!! Form::select('type', ['allowance' =>__('essentials::lang.allowance'), 'deduction' => __('essentials::lang.deduction')], $allowance->type, ['class' => 'form-control', 'required']) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('amount', __('essentials::lang.amount') . ':*') !!}
                    {!! Form::number('amount', $allowance->amount, ['class' => 'form-control', 'placeholder' => __('essentials::lang.amount'),  'step' => '0.0001', 'required']) !!}
                </div>

               
                <div class="form-group col-md-6">
                    {!! Form::label('amount_type', __('essentials::lang.amount_type') . ':') !!}
                    {!! Form::select('amount_type', ['fixed' =>__('essentials::lang.fixed'), 'percent' => __('essentials::lang.percent')], $allowance->amount_type, ['class' => 'form-control', 'required']) !!}
                    
                </div>
                <div class="form-group">
                    {!! Form::label('applicable_date',__('essentials::lang.applicable_date') . ':') !!}
                    {!! Form::date('applicable_date', $allowance->applicable_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.applicable_date'), 'required']) !!}
                </div>
               
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