@extends('layouts.app')
@section('title', __('essentials::lang.official_documents'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.official_documents')</h1>
</section>

<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateDoc', $doc->id], 'method' => 'put', 'id' => 'edit_doc_form' , 'enctype' => 'multipart/form-data']) !!}


      <div class="modal-header">
        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'essentials::lang.edit_doc' )</h4>
      </div>
  
      <div class="modal-body">
   
        <div class="row">
           
            <div class="form-group col-md-6">
                {!! Form::label('employee', __( 'essentials::lang.employee' ) . ':') !!}
                {!! Form::select('employee',$users, null, ['class' => 'form-control select2' ,'placeholder' =>$users[$doc->employee_id]]); !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                {!! Form::select('doc_type', [
                   
                    'national_id'=>__('essentials::lang.national_id'),
                    'passport'=>__('essentials::lang.passport'),
                    'residence_permit'=>__('essentials::lang.residence_permit'),
                    'drivers_license'=>__('essentials::lang.drivers_license'),
                    'car_registration'=>__('essentials::lang.car_registration'),
                    'international_certificate'=>__('essentials::lang.international_certificate'),
                ], $doc->type, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':*') !!}
                {!! Form::number('doc_number', $doc->number, ['class' => 'form-control', 'placeholder' => __('essentials::lang.doc_number')]) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':*') !!}
                {!! Form::date('issue_date', $doc->issue_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_date')]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':*') !!}
                {!! Form::text('issue_place', $doc->issue_place, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_place')]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                {!! Form::select('status', [
                'valid' => __('essentials::lang.valid'),
                'expired' => __('essentials::lang.expired'),
              
            ],  $doc->status, ['class' => 'form-control', 'placeholder' => 'Select status']) !!}
        </div>
            <div class="form-group col-md-6">
                {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                {!! Form::date('expiration_date',  $doc->expiration_date, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date')]) !!}
            </div>
        
            <div class="form-group col-md-6">
                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
               
                    @if ($doc->file_path)
                        <p><a href="/uploads/{{ $doc->file_path }}" target="_blank">{{ __('essentials::lang.view_doc') }}</a></p>
                    @else
                        {!! Form::file('file', ['class' => 'form-control', 'placeholder' => __('essentials::lang.file')]) !!}
                    @endif
                </div>
                
                {!! Form::file('file',null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file')]) !!}
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