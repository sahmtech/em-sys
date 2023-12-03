@extends('layouts.app')
@section('title', __('followup::lang.requests'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('followup::lang.requests')</span>
    </h1>
</section>
<style>

    .alert {
        animation: fadeOut 5s forwards;
    }

    @keyframes fadeOut {
        to {
            opacity: 0;
            visibility: hidden;
        }
    }
</style>

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@else
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
@endif
<section class="content">
    {!! Form::open(['route' => 'storeRequest','enctype' => 'multipart/form-data'] ) !!}

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">@lang('followup::lang.add_request')</h4>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {!! Form::label('worker_id', __('followup::lang.worker_name') . ':*') !!}
                {!! Form::select('worker_id', $workers, null, [   'class' => 'form-control select2', 'required','style'=>' height: 40px' ,  'placeholder' => __('followup::lang.select_worker')]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                {!! Form::select('type',[
                'exitRequest'=>__('followup::lang.exitRequest'),
                'returnRequest'=>__('followup::lang.returnRequest'),
                'escapeRequest'=>__('followup::lang.escapeRequest'),
                'advanceSalary'=>__('followup::lang.advanceSalary'),
                'leavesAndDepartures'=>__('followup::lang.leavesAndDepartures'),
                'atmCard'=>__('followup::lang.atmCard'),
                'residenceRenewal'=>__('followup::lang.residenceRenewal'),
                'residenceCard'=>__('followup::lang.residenceCard'),
                'workerTransfer'=>__('followup::lang.workerTransfer'),
                'workInjuriesRequest'=>__('followup::lang.workInjuriesRequest'),
                'residenceEditRequest'=>__('followup::lang.residenceEditRequest'),
                'baladyCardRequest'=>__('followup::lang.baladyCardRequest'),
                'insuranceUpgradeRequest'=>__('followup::lang.insuranceUpgradeRequest'),
                'mofaRequest'=>__('followup::lang.mofaRequest'),
                'chamberRequest'=>__('followup::lang.chamberRequest'),
                
                ], null, ['class' => 'form-control', 'required', 'style'=>' height: 40px' , 'placeholder' => __('essentials::lang.select_type'), 'id' => 'requestType']) !!}
            </div>
            <div class="form-group col-md-6" id="leaveType" style="display: none;">
                {!! Form::label('leaveType', __('followup::lang.leaveType') . ':*') !!}
                {!! Form::select('leaveType',$leaveTypes, null, [   'class' => 'form-control select2', 'style'=>' height: 40px' , 'placeholder' => __('followup::lang.select_leaveType'), 'id' => 'leaveType']) !!}
            </div>

            <div class="form-group col-md-6" id="start_date" style="display: none;">
                {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                {!! Form::date('start_date', null, ['class' => 'form-control', 'style'=>' height: 40px' , 'placeholder' => __('essentials::lang.start_date'), 'id' => 'startDateField']) !!}
            </div>
      
         
            <div class="form-group col-md-6" id="end_date" style="display: none;">
                {!! Form::label('end_date', __('essentials::lang.end_date') . ':*') !!}
                {!! Form::date('end_date', null, ['class' => 'form-control','style'=>' height: 40px' ,  'placeholder' => __('essentials::lang.end_date'), 'id' => 'endDateField']) !!}
            </div>
            <div class="form-group col-md-6" id="escape_time" style="display: none;">
                {!! Form::label('escape_time', __('followup::lang.escape_time') . ':*') !!}
                {!! Form::time('escape_time', null, ['class' => 'form-control','style'=>' height: 40px' ,  'placeholder' => __('followup::lang.escape_time'), 'id' => 'escapeTimeField']) !!}
            </div>

            <div class="form-group col-md-6" id="escape_date" style="display: none;">
                {!! Form::label('escape_date', __('essentials::lang.escape_date') . ':*') !!}
                {!! Form::date('escape_date', null, ['class' => 'form-control','style'=>' height: 40px' ,  'placeholder' => __('essentials::lang.escape_date'), 'id' => 'escapeDateField']) !!}
            </div>
            <div class="form-group col-md-6" id="amount" style="display: none;">
                {!! Form::label('amount', __('followup::lang.advSalaryAmount') . ':*') !!}
                {!! Form::number('amount', null, ['class' => 'form-control','style'=>' height: 40px' ,  'placeholder' => __('followup::lang.advSalaryAmount'), 'id' => 'advSalaryAmountField']) !!}
            </div>
            <div class="form-group col-md-6" id="installmentsNumber" style="display: none;">
                {!! Form::label('installmentsNumber', __('followup::lang.installmentsNumber') . ':*') !!}
                {!! Form::number('installmentsNumber', null, ['class' => 'form-control','style'=>' height: 40px' ,  'placeholder' => __('followup::lang.installmentsNumber'), 'id' => 'installmentsNumberField']) !!}
            </div>
            <div class="form-group col-md-6" id="monthlyInstallment" style="display: none;">
                {!! Form::label('monthlyInstallment', __('followup::lang.monthlyInstallment') . ':*') !!}
                {!! Form::number('monthlyInstallment', null, ['class' => 'form-control', 'style'=>' height: 40px' , 'placeholder' => __('followup::lang.monthlyInstallment'), 'id' => 'monthlyInstallmentField']) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('note', __('followup::lang.note') . ':') !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __('followup::lang.note'), 'rows' => 3]) !!}
            </div>

            <div class="form-group col-md-6">
                {!! Form::label('reason', __('followup::lang.reason') . ':') !!}
                {!! Form::textarea('reason', null, ['class' => 'form-control', 'required', 'placeholder' => __('followup::lang.reason'), 'rows' => 3]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('attachment', __('followup::lang.attachment') . ':*') !!}
                {!! Form::file('attachment', null, ['class' => 'form-control', 'placeholder' => __('followup::lang.attachment')]) !!}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}
</section>   
  
@endsection
 
@section('javascript')
<script>
    $(document).ready(function () {
        
        handleTypeChange();
        $('#requestType').change(handleTypeChange);

        function handleTypeChange() {
            var selectedType = $('#requestType').val();
            console.log(selectedType);
            if (selectedType === 'exitRequest' || selectedType === 'returnRequest' || selectedType === 'leavesAndDepartures') {
                $('#start_date').show();
            } else {
                $('#start_date').hide();
            }

            if (selectedType === 'returnRequest' || selectedType === 'leavesAndDepartures') {
                $('#end_date').show();
            } else {
                $('#end_date').hide();
            }
            if (selectedType === 'leavesAndDepartures') {
                $('#leaveType').show();
            } else {
                $('#leaveType').hide();
            }

          
            if (selectedType === 'escapeRequest') {
                $('#escape_time').show();
                $('#escape_date').show();
               
            } else {
                $('#escape_time').hide(); 
                $('#escape_date').hide();
            }
            if (selectedType === 'advanceSalary') {
                $('#installmentsNumber').show();
                $('#monthlyInstallment').show();
                $('#amount').show();

            } else {
                $('#installmentsNumber').hide();
                $('#monthlyInstallment').hide();
                $('#amount').hide();
            }
        }
        
    });
  
</script>
@endsection
