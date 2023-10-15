@component('components.widget', ['title' => __('essentials::lang.hrm_details')])
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('location_id', __('lang_v1.primary_work_location') . ':*') !!}
        {!! Form::select('location_id', $locations, !empty($user->location_id) ? $user->location_id : null, ['class' => 'form-control select2', 'required','placeholder' => __('messages.please_select')]); !!}
        </div>
    </div>
	<div class="col-md-6">
		<div class="form-group">
              {!! Form::label('essentials_department_id', __('essentials::lang.department') . ':*') !!}
              <div class="form-group">
                  {!! Form::select('essentials_department_id', $departments, !empty($user->essentials_department_id) ? $user->essentials_department_id : null, ['class' => 'form-control select2', 'required','style' => 'width: 100%;', 'placeholder' => __('messages.please_select') ]); !!}
              </div>
          </div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
            {!! Form::label('essentials_designation_id', __('essentials::lang.designation') . ':') !!}
            <div class="form-group">
                {!! Form::select('essentials_designation_id', $designations, !empty($user->essentials_designation_id) ? $user->essentials_designation_id : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'required','placeholder' => __('messages.please_select') ]); !!}
            </div>
        </div>
	</div>
    {{-- <div class="form-group col-md-3">
        {!! Form::label('essentials::lang.qualification_type', __('essentials::lang.qualification_type') . ':') !!}
        {!! Form::select('qualification_type',  ['bachelors_degree' => __('essentials::lang.bachelors_degree'), 
        'master_degree' => __('essentials::lang.master_degree'),
        'doctorate' => __('essentials::lang.doctorate'),
        'diploma' => __('essentials::lang.diploma'),
        'professional_certification' => __('essentials::lang.professional_certification'),
        'language_proficiency_certificate' => __('essentials::lang.language_proficiency_certificate'),
  

        ], null,['class' => 'form-control', 'placeholder' => __('essentials::lang.qualification_type')]) !!}
    
    </div> --}}
   
    <div class="col-md-12">
        <hr>
        <h4>@lang('essentials::lang.contract_details'):</h4>
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_number', __( 'essentials::lang.contract_number') . ':') !!}
            {!! Form::text('contract_number', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_number') ]); !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_start_date', __( 'essentials::lang.contract_start_date') . ':') !!}
            {!! Form::date('contract_start_date', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_start_date') ]); !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_end_date', __( 'essentials::lang.contract_end_date') . ':') !!}
            {!! Form::date('contract_end_date', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_end_date') ]); !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_duration', __( 'essentials::lang.contract_duration') . ':') !!}
            {!! Form::text('contract_duration', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_duration') ]); !!}
        </div>   
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.probation_period', __( 'essentials::lang.probation_period') . ':') !!}
            {!! Form::text('probation_period', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.probation_period') ]); !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
            {!! Form::select('is_renewable', ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')], null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
            {!! Form::text('status',null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.status') ]); !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_file', __( 'essentials::lang.contract_file') . ':') !!}
            {!! Form::file('contract_file', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_file') ]); !!}
        </div>

    </div>
        
@endcomponent
@component('components.widget', ['title' => __('essentials::lang.payroll')])
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <div class="multi-input">
                {!! Form::label('essentials_salary', __('essentials::lang.salary') . ':') !!}
                <br/>
                {!! Form::number('essentials_salary', !empty($user->essentials_salary) ? $user->essentials_salary : null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('essentials::lang.salary')]); !!}

                {!! Form::select('essentials_pay_period', ['month' => __('essentials::lang.per'). ' '.__('lang_v1.month'), 'week' => __('essentials::lang.per'). ' '.__('essentials::lang.week'), 'day' => __('essentials::lang.per'). ' '.__('lang_v1.day')], !empty($user->essentials_pay_period) ? $user->essentials_pay_period : null, ['class' => 'form-control width-60 pull-left']); !!}
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('essentials::lang.salary') }}</th>
                <th>{{ __('essentials::lang.amount') }}</th>
            </tr>
        </thead>
        <tbody id="salary-table-body">
            <tr>
                <td>
                    {!! Form::select('salary_type[]', $allowance_types, null, ['class' => 'form-control width-60 pull-left', 'placeholder' => __('essentials::lang.extra_salary_type')]); !!}
                </td>
                <td>
                    {!! Form::text('amount[]', null, ['class' => 'form-control width-60 pull-left', 'placeholder' => __('essentials::lang.amount')]); !!}
                </td>
            </tr>
        </tbody>
    </table>
    
    <button type="button" id="add-row" class="btn btn-primary">{{ __('essentials::lang.add') }}</button>

    
    
</div>
<div class="col-md-12">
    <hr>
    <h4>@lang('essentials::lang.features'):</h4>
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('travel_ticket_categorie', __('essentials::lang.travel_ticket_categorie') . ':') !!}
        {!! Form::select('travel_ticket_categorie', $travel_ticket_categorie,null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.travel_ticket_categorie')]) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('health_insurance', __('essentials::lang.health_insurance') . ':') !!}
        {!! Form::text('health_insurance',null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.health_insurance') ]); !!}
        
    </div>


<script>
    var selectedData = [];

    function addRow() {
        var newRow = $('#salary-table-body tr:first').clone();
        newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
        newRow.find('input[name="amount[]"]').attr('name', 'amount[]');

        $('#salary-table-body').append(newRow);
    }

    $('#add-row').click(function() {
        addRow();
    });

    $(document).on('change', 'select[name="salary_type[]"]', function() {
        updateSelectedData();
    });

    $(document).on('input', 'input[name="amount[]"]', function() {
        updateSelectedData();
    });

    function updateSelectedData() {
        selectedData = [];

        $('select[name="salary_type[]"]').each(function(index) {
            var salaryType = $(this).val();
            var amount = parseFloat($('input[name="amount[]"]').eq(index).val());
            selectedData.push({ salaryType: salaryType, amount: amount });
        });

        console.log(selectedData);
        var inputElement = document.getElementById('selectedData');
        inputElement.value = JSON.stringify(selectedData);
    }
</script>


<script>
    function updateAmount(element) {
        var salaryType = $(element).val();
        console.log(salaryType);
        // Make an AJAX call to retrieve the amount for the selected salary type
        $.ajax({
            url: '/hrm/get-amount/' + salaryType, // Modify the URL according to your Laravel route
            type: 'GET',
            success: function(response) {
                // Update the corresponding amount input field
                var amountInput = $(element).closest('tr').find('input[name="amount[]"]');
                amountInput.val(response.amount);
                updateSelectedData(); // Update the selected data after updating the amount
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    // Rest of your existing JavaScript code...

    $(document).on('change', 'select[name="salary_type[]"]', function() {
        updateAmount(this); // Call the function to update the amount
    });

    // Rest of your existing JavaScript code...
</script>


<input type="hidden" id="selectedData" name="selectedData" value="">
@endcomponent