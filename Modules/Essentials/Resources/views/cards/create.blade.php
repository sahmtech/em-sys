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
    {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'store']), 
    'method' => 'post','id' => 'workCardForm']) !!}       
          
    <div class="col-md-9">
    <div class="form-group">
        {!! Form::label('employees', __('essentials::lang.choose_card_owner') . ':*') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('employees[]', $employees, null, [
                'class' => 'form-control select2',
                'style' => 'width: 100%;',
                'placeholder' => __('lang_v1.all'),
                'id' => 'employees',
                'required',
                'onchange' => 'getData(this.value)',
            ]) !!}
        </div>
    </div>
</div>



<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('all_responsible_users', __('essentials::lang.select_responsible_users') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('all_responsible_users[]', [], null, [
                'class' => 'form-control select2',
                'style' => 'width: 100%;',
                'id' => 'responsible_users',
                'required',
            ]) !!}
        </div>
    </div>
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('responsible_client', __('essentials::lang.responsible_client') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::select('responsible_client[]', [], null,
                 ['class' => 'form-control select2',
                  'style' => 'height:40px',
                   'id' => 'responsible_client',
                    'multiple' => 'multiple']) !!}
        </div>
    </div>
</div>


<div class="form-group">
    {!! Form::hidden('employee_id', null, ['id' => 'employee_id']) !!}
</div>

<div class="form-group">
    {!! Form::hidden('responsible_user_id', null, ['id' => 'responsible_user_id']) !!}
</div>

<div class="form-group">
    {!! Form::hidden('Residency_id', null, ['id' => 'Residency_id']) !!}
</div>

<div class="form-group">
    {!! Form::hidden('border_id', null, ['id' => 'border_id']) !!}
</div>

<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('Residency_no', __('essentials::lang.Residency_no') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('Residency_no', null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.Residency_no'),
                'style' => 'width: 100%;',
                'id' => 'Residency_no',
            ]) !!}
        </div>
    </div>
</div>

<div class="col-md-9 border_no">
    <div class="form-group">
        {!! Form::label('border_no', __('essentials::lang.border_number') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::text('border_no', null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.border_number'),
                'style' => 'width: 100%;',
                'id' => 'border_no',
            ]) !!}
        </div>
    </div>
</div>

<div class="col-md-9" id="Residency_end_date_id">
    <div class="form-group">
        {!! Form::label('Residency_end_date', __('essentials::lang.Residency_end_date') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-id-badge"></i>
            </span>
            {!! Form::date('Residency_end_date', null, [
                'class' => 'form-control',
                'style' => 'width: 100%;',
                'placeholder' => __('essentials::lang.Residency_end_date'),
                'id' => 'Residency_end_date',
            ]) !!}
        </div>
    </div>
</div>


<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('business', __('essentials::lang.business') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-building"></i>
            </span>
            {!! Form::select('business', 
                $companies, null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.business'),
                'id' => 'business',
            ]) !!}
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
            {!! Form::select('workcard_duration',
                 $durationOptions, null, ['class' => 'form-control',
                 'style'=>'height:40px','id'=>'workcard_duration',
                  'placeholder' => __('essentials::lang.work_card_duration')]); !!}
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
            {!! Form::text('fees', null,
                 ['class' => 'form-control','id'=>'fees',
                  'placeholder' => __('essentials::lang.fees')]); !!}
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
        $(document).ready(function() {
            $('#workcard_duration').on('change', function() {
                var selectedDuration = $(this).find(':selected').attr('value');
                 console.log(selectedDuration);
                var feesInput = $('#fees');
                console.log(feesInput);
                
                var feesMapping = {
                    '3': 2425,
                    '6': 4850,
                    '9': 7275,
                    '12': 9700,
                };
                console.log(feesMapping[selectedDuration]);

                $('#fees').val(feesMapping[selectedDuration]);
            });
        });
 </script>


<script>
    function getData(employeeId) {
        getResponsibleData(employeeId);
        getResidencyData(employeeId);
    }
</script>

<script>
   var translations = {
    management: @json(__('essentials::lang.management'))

};
</script>

<script>
    
function getResponsibleData(employeeId) {
  
    $.ajax({
        url: "{{ route('get_responsible_data') }}",
        type: 'GET',
        data: { employeeId: employeeId },
        success: function (data) {
            console.log(data);

          
            $('#responsible_users').empty();
            $('#responsible_users').append($('<option>', {
                value: data.all_responsible_users.id,
                text: data.all_responsible_users.name
            }));

            if (data.responsible_client.length > 0) {
                $('#responsible_client').empty();
                $.each(data.responsible_client, function (index, item) {
                    $('#responsible_client').append($('<option>', {
                        value: item.id,
                        text: item.name,
                        selected: true 
                    }));
                });

               
                $('#responsible_client').select2();
            } 
            else 
            {
                $('#responsible_client').empty();
                $('#responsible_client').append($('<option>', {
                    value: null,
                    text: translations['management'],
                    selected: true
                }));
            }
               

          
            $('#business').empty();
            $('#business').append($('<option>', {
                value: data.company.id,
                text: data.company.name
            }));

           
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}
</script>


<script>
    function getResidencyData(employeeId) {
    
        $.ajax({
            url: '{{ route("getResidencyData") }}',
            type: 'GET',
            data: { employee_id: employeeId },
            success: function(response) {
               
                $('#Residency_no').val(response.residency_no);
                $('#border_no').val(response.border_no);
                $('#Residency_end_date').val(response.residency_end_date);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>


<script  type="text/javascript">
$(document).ready(function () {
    $('#employees').on('change', function () {
        var employeeId = $(this).val();
        $('#employee_id').val(employeeId);
        console.log(employeeId);
    });
});


$(document).ready(function () {
    $('#all_responsible_users').on('change', function () {
        var employee = $(this).val();
        $('#responsible_user_id').val(employee);
        console.log(employee);
    });
});


</script>
@endsection