@extends('layouts.app')
@section('title',__('sales::lang.create_sale_operation'))

@section('content')
<section class="content-header">
    <h1>@lang('sales::lang.edit_sale_operation')</h1>
</section>


<!-- Main content -->
<section class="content no-print">

<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-solid'])
            {!! Form::open(['url' => action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'update']), 'method' => 'post']) !!}       
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('contact_id', __('sales::lang.customer') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('contact_id', $leads, $operation->contact_id, ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.select_customer'), 'required', 'id' => 'customer-select']) !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('operation_order_number', __('sales::lang.operation_order_number') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('operation_order_no', $operation->operation_order_no, ['class' => 'form-control', 'placeholder' => __('sales::lang.example_operation_order_number')]); !!}
        </div>
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('contract_id', __('sales::lang.contract') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('sale_contract_id', [], null, ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.select_contacts'), 'required', 'id' => 'contact-select']) !!}
        </div>
    </div>
</div>



<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Industry', __('sales::lang.Industry') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Industry',  $operation->Industry, ['class' => 'form-control', 'placeholder' => __('sales::lang.Industry')]); !!}
        </div>
    </div>
</div>


				
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('operation_order_type', __('sales::lang.operation_order_type') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            
            {!! Form::select('operation_order_type', ['Internal' => __('sales::lang.Internal'), 'External' => __('sales::lang.external')], $operation->operation_order_type, ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.operation_order_type')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Interview', __('sales::lang.Interview') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            
            {!! Form::select('Interview', ['Client ' => __('sales::lang.Client'), 'Company' => __('sales::lang.Company')], $operation->Interview, ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.Interview')]); !!}
        </div>
    </div>
</div>
		
<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Location', __('sales::lang.Location') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Location', $operation->Location, ['class' => 'form-control', 'placeholder' => __('sales::lang.Location')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Delivery', __('sales::lang.Delivery') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Delivery', $operation->Delivery, ['class' => 'form-control', 'placeholder' => __('sales::lang.Delivery')]); !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Note', __('sales::lang.Note') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Note',  $operation->Note, ['class' => 'form-control', 'placeholder' => __('sales::lang.Note')]); !!}
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

  
<script>
  $(document).ready(function () {
    $('#customer-select').change(function () {
        var selectedCustomerId = $(this).val();
        if (selectedCustomerId) {
            $.ajax({
                url: '{{ route('get-contracts') }}',
                type: 'POST',
                data: { customer_id: selectedCustomerId },
                success: function (response) {
                    var contactSelect = $('#contact-select');
                    contactSelect.empty();
                    
                  
                    contactSelect.append('<option value="">اختر العقد</option>');
                    
              
                   // Populate the contact dropdown with contracts
                   $.each(response, function (index, contract) {
                        contactSelect.append(new Option(contract.number_of_contract, contract.id));
                    });

                 console.log( contactSelect);
                    contactSelect.find('option').first().attr('selected', 'selected');
                },
                error: function () {
                    console.error('An error occurred.');
                }
            });
        }
    });
});

</script>


@endsection