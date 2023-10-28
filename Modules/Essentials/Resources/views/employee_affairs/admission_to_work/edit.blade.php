@extends('layouts.app')
@section('title', __('essentials::lang.admissions_to_work'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.admissions_to_work')</h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateAdmissionToWork', $work->id], 'method' => 'put', 'id' => 'add_work_form']) !!}


      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_work' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                {!! Form::select('employee',$users, $work->employee_id, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
            </div>
         
    
          
            <div class="form-group col-md-6">
                {!! Form::label('admissions_type', __('essentials::lang.admissions_type') . ':*') !!}
                {!! Form::select('admissions_type', [
                'first_time' => __('essentials::lang.first_time'),
                'after_vac' => __('essentials::lang.after_vac'),
          
                ], $work->admissions_type, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_type'), 'required']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('admissions_status', __('essentials::lang.admissions_status') . ':*') !!}
                {!! Form::select('admissions_status', [
                'on_date' => __('essentials::lang.on_date'),
                'delay' => __('essentials::lang.delay'),
          
                ], $work->admissions_status, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_status'), 'required']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('admissions_date', __('essentials::lang.admissions_date') . ':*') !!}
                {!! Form::date('admissions_date', $work->admissions_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.admissions_date'), 'required']) !!}
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