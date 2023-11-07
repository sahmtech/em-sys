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
            {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\WorkCardsController::class, 'store']), 'method' => 'post']) !!}       


<div class="col-md-9">
    <div class="form-group">
     {!! Form::label('employees', __('essentials::lang.select_employee') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
          
		        {!! Form::select('employees[]', $employees, null,
					 ['class' => 'form-control select2', 'style' => 'width: 100%;', 
					 'id' => 'employees', 'required' ]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Residency_no', __('essentials::lang.Residency_no') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('Residency_no', [], null, ['class' => 'form-control', 'style' => 'height:36px', 'placeholder' => __('essentials::lang.Residency_no'), 'required']) !!}
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
            {!! Form::text('Residency_end_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.Residency_end_date')]); !!}
        </div>
    </div>
</div>


				
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('project', __('essentials::lang.project') . ':*') !!}
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
        {!! Form::label('work_card_duration', __('essentials::lang.work_card_duration') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            
            {!! Form::text('work_card_duration', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.work_card_duration')]); !!}
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
            {!! Form::text('pay_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.pay_number')]); !!}
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