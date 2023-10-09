@component('components.widget', ['title' => __('essentials::lang.hrm_details')])
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('location_id', __('lang_v1.primary_work_location') . ':') !!}
        {!! Form::select('location_id', $locations, !empty($user->location_id) ? $user->location_id : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
        </div>
    </div>
	<div class="col-md-6">
		<div class="form-group">
              {!! Form::label('essentials_department_id', __('essentials::lang.department') . ':') !!}
              <div class="form-group">
                  {!! Form::select('essentials_department_id', $departments, !empty($user->essentials_department_id) ? $user->essentials_department_id : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('messages.please_select') ]); !!}
              </div>
          </div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
            {!! Form::label('essentials_designation_id', __('essentials::lang.designation') . ':') !!}
            <div class="form-group">
                {!! Form::select('essentials_designation_id', $designations, !empty($user->essentials_designation_id) ? $user->essentials_designation_id : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('messages.please_select') ]); !!}
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
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.contract_file', __( 'essentials::lang.contract_file') . ':') !!}
            {!! Form::file('contract_file', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.contract_file') ]); !!}
        </div>

{{--     
        <div class="col-md-12">
            <hr>
            <h4>@lang('essentials::lang.admissions_to_work'):</h4>
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.dmissions_type', __('essentials::lang.dmissions_type') . ':') !!}
            {!! Form::select('dmissions_type', ['first_time' =>  __('essentials::lang.first_time'), 'after_vac' => __('essentials::lang.after_vac')], null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.dmissions_type')]) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.dmissions_status', __('essentials::lang.dmissions_status') . ':') !!}
            {!! Form::select('dmissions_status', ['on_date' => __('essentials::lang.on_date'), 'delay' => __('essentials::lang.delay')], null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.dmissions_status')]) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('details', __('essentials::lang.details') . ':') !!}
            {!! Form::text('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.details')]) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('essentials::lang.work_type', __('essentials::lang.work_type') . ':') !!}
            {!! Form::select('work_type',  ['full_time' => __('essentials::lang.full_time'), 
            'part_time' => __('essentials::lang.part_time'),
        
            ], null,['class' => 'form-control', 'placeholder' => __('essentials::lang.work_type')]) !!}
        
        </div>
         --}}
        
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
    

    {{-- <div class="form-group col-md-4">
        {!! Form::label('pay_components', __('essentials::lang.pay_components') . ':') !!}
        {!! Form::select('pay_components[]', $pay_comoponenets, !empty($allowance_deduction_ids) ? $allowance_deduction_ids : [], ['class' => 'form-control select2', 'multiple' ]); !!}
    </div>
    
    <div class="form-group col-md-3">
        {!! Form::label('essentials::lang.basic_salary_type', __('essentials::lang.basic_salary_type') . ':') !!}
        {!! Form::select('basic_salary_type',$basic_salary_types,null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.basic_salary_type')]) !!}
    </div>
    
    <div class="form-group col-md-3">
        {!! Form::label('essentials::lang.allowance_type', __('essentials::lang.allowance_type') . ':') !!}
        {!! Form::select('allowance_type',  $allowance_types,null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.allowance_type')]) !!}
    </div>
    
    <div class="form-group col-md-3">
        {!! Form::label('essentials::lang.entitlement_type', __('essentials::lang.entitlement_type') . ':') !!}
        {!! Form::select('entitlement_type',  $entitlement_type,null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.entitlement_type')]) !!}
    </div>
  
   
    <div class="form-group col-md-3">
        {!! Form::label('essentials::lang.travel_ticket_categorie', __('essentials::lang.travel_ticket_categorie') . ':') !!}
        {!! Form::select('travel_ticket_categorie', $travel_ticket_categorie,null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.travel_ticket_categorie')]) !!}
    </div> --}}
</div>
@endcomponent
@section('javascript')
<script type="text/javascript">
  
  const addButton = document.getElementById('addButton');
  addButton.addEventListener('click', createNewInput);

  
  function createNewInput() {
    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Enter something...';
    document.body.appendChild(input);
  }
</script>
@endsection
