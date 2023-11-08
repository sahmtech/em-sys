@extends('layouts.app')
@section('title', __('essentials::lang.work_cards'))
@section('content')

<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.create_work_cards')</span>
    </h1>
</section>

<section class="content no-print">



<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-solid'])
    {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'store']), 'method' => 'post','id' => 'workCardForm']) !!}       
          

            <div class="col-md-9">
    <div class="form-group">
        {!! Form::label('employees', __('essentials::lang.select_employee') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('employees[]', $employees, null, [
                'class' => 'form-control select2',
                'style' => 'width: 100%;',
                'placeholder' => __('lang_v1.all'),
                'id' => 'employees', 'required'
            ]) !!}
        </div>
    </div>
</div>
<div class="form-group">
    {!! Form::hidden('employee_id', null, ['id' => 'employee_id']) !!}
</div>

<div class="form-group">
    {!! Form::hidden('Residency_id', null, ['id' => 'Residency_id']) !!}
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Residency_no', __('essentials::lang.Residency_no') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Residency_no', null, [
                'class' => 'form-control',
                'style' => 'height:36px',
                'placeholder' => __('essentials::lang.Residency_no'),
                'required',
                'style' => 'width: 100%;',
                'data-residency-url' => route('getResidencyData') 
            ]) !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Residency_end_date', __('essentials::lang.Residency_end_date') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::date('Residency_end_date', null, [
                'class' => 'form-control',
                'style' => 'width: 100%;',
                'placeholder' => __('essentials::lang.Residency_end_date')
            ]) !!}
        </div>
    </div>
</div>



				
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('project', __('essentials::lang.project') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            
            {!! Form::text('project', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.project')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('workcard_duration', __('essentials::lang.work_card_duration') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            
            {!! Form::text('workcard_duration', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.work_card_duration')]); !!}
        </div>
    </div>
</div>
		
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('pay_number', __('essentials::lang.pay_number') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Payment_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.pay_number')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('fees', __('essentials::lang.fees') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('fees', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.fees')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('company_name', __('essentials::lang.company_name') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('company_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.company_name')]); !!}
        </div>
    </div>
</div>



		
@endcomponent


	
	<div class="row">
		
		<div class="col-sm-12 text-center">
			<button  type="submit" class="btn btn-primary btn-big">@lang('messages.save')</button>
			
		</div>
	</div>
	
	
{!! Form::close() !!}
</section>

@endsection
@section('javascript')
<script type="text/javascript">
   $(document).ready(function () {
    $('#employees').on('change', function () {
        var employeeId = $(this).val();
        var $residencyNoField = $('#Residency_no');
        var $residencyEndDateField = $('#Residency_end_date');
        var $residencyidField = $('#Residency_id');
        if (employeeId) {
            $.ajax({
                url: $residencyNoField.data('residency-url'),
                method: 'GET',
                data: { employee_id: employeeId },
                success: function (data) {
                    if (data) {
                        $residencyNoField.val(data.residency_no);
                        $residencyEndDateField.val(data.residency_end_date);
                        $residencyidField.val(data.id);
                    } else {
                        $residencyNoField.val('');
                        $residencyEndDateField.val('');
                        $residencyidField.val('');
                    }
                },
                error: function () {
                    $residencyNoField.val('');
                    $residencyEndDateField.val('');
                    $residencyidField.val('');
                }
            });
        } else {
            $residencyNoField.val('');
            $residencyEndDateField.val('');
            $residencyidField.val('');
        }
    });
});
 



</script>

<script  type="text/javascript">
    $(document).ready(function () {
    $('#employees').on('change', function () {
        var employeeId = $(this).val();
        $('#employee_id').val(employeeId);
    });
});

</script>
@endsection