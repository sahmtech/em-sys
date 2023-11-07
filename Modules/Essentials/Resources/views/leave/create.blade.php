<div class="modal-dialog" role="document">
	
  <div class="modal-content">

{!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'store']), 'method' => 'post', 'id' => 'add_leave_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.add_leave' )</h4>
    </div>

    <div class="modal-body">
    	<div class="row">
    		@can('essentials.crud_all_leave')
    		<div class="form-group col-md-12">
		        {!! Form::label('employees', __('essentials::lang.select_employee') . ':') !!}
		        {!! Form::select('employees[]', $employees, null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'employees', 'required' ]); !!}
    		</div>
    		@endcan
			{!! Form::hidden('employee_id', null, ['id' => 'employee_id']) !!}
			
<div class="form-group col-md-12 date-of-admission-section" style="display: none;">
    {!! Form::label('admission_date', 'Admission Date:') !!}
    {!! Form::text('admission_date', null, ['class' => 'form-control', 'id' => 'admission_date', 'readonly' => 'readonly']) !!}
</div>

    		<div class="form-group col-md-12">
	        	{!! Form::label('essentials_leave_type_id', __( 'essentials::lang.leave_type' ) . ':*') !!}
	          	{!! Form::select('essentials_leave_type_id', $leave_types, null, ['class' => 'form-control select2', 'required', 'placeholder' => __( 'messages.please_select' ) ]); !!}
	      	</div>

	      	<div class="form-group col-md-6">
	        	{!! Form::label('start_date', __( 'essentials::lang.start_date' ) . ':*') !!}
				{!! Form::date('start_date', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.start_date'), 'required' ]); !!}
	        	{{-- <div class="input-group data">

	        		{!! Form::text('start_date', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.start_date' ), 'readonly' ]); !!}
	        		<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	        	</div> --}}
	      	</div>

	      	<div class="form-group col-md-6">
	        	{!! Form::label('end_date', __( 'essentials::lang.end_date' ) . ':*') !!}
				{!! Form::date('end_date', null , ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.end_date') , 'required']); !!}
		        	{{-- <div class="input-group data">
		          	{!! Form::text('end_date', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.end_date' ), 'readonly', 'required' ]); !!}
		          	<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
	        	</div> --}}
	      	</div>
			  <div class="form-group col-md-6">
                                {!! Form::label('attachments_path', __('essentials::lang.attachments_path') . ':') !!}
                                {!! Form::file('attachments_path', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.attachments_path')]) !!}
            </div>

			<div class="form-group col-md-12">
		        {!! Form::label('alt_employee', __('essentials::lang.select_altemployee') . ':') !!}
		        {!! Form::select('alt_employee[]', $alt_employee, null, ['class' => 'form-control select2', 'style' => 'width: 100%;' ]); !!}
    		</div>

			<div class="form-group col-md-12">
               <label for="travel_destination">@lang('essentials::lang.travel_destination'):</label>
               <select class="form-control select2" name="travel_destination" required id="travel_destination" style="width: 100%;">
                   <option value="all">@lang('lang_v1.all')</option>
                   <option value="external">@lang('sales::lang.external')</option>
                   <option value="internal">@lang('sales::lang.internal')</option>
               </select>
           </div>

		   <div class="form-group col-md-12 travel-ticket-category-section" style="display: none;">
    {!! Form::label('travel_ticket_categorie', __('essentials::lang.travel_ticket_categorie') . ':') !!}
    {!! Form::select('travel_ticket_categorie', $travel_ticket_categorie, null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.travel_ticket_categorie')]) !!}
</div>

	      	<div class="form-group col-md-12">
	        	{!! Form::label('reason', __( 'essentials::lang.reason' ) . ':') !!}
	          	{!! Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.reason' ), 'rows' => 4, 'required' ]); !!}
	      	</div>
	      	<hr>
	      	<div class="col-md-12">
    			{!! $instructions !!}
    		</div>
    	</div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary ladda-button add-leave-btn" data-style="expand-right">
      	<span class="ladda-label">@lang( 'messages.save' )</span>
      </button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->


<script>
 $(document).ready(function () {
    $('#employees').on('change', function () {
        var selectedEmployeeIds = $(this).val();
		console.log(selectedEmployeeIds);
        if (selectedEmployeeIds) {
            $.ajax({
                type: 'GET', 
                url: '{{ action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'getAdmissionDate']) }}', 
                data: { employeeIds: selectedEmployeeIds
			 },
			
                success: function (response) {
                  
                    $('#admission_date').val(response);
				
                },
                error: function (xhr, status, error) {
                    console.log(error);
					
					
                }
            });
        }
    });
});

</script>

<script>
$(document).ready(function () {
    $('#employee').on('change', function () {
        var selectedEmployeeId = $(this).val();
        $('#employee_id').val(selectedEmployeeId);
    });

   
});
</script>
<script>
$(document).ready(function () {
    $('#travel_destination').on('change', function () {
        var selectedDestination = $(this).val();
        var admissionDateInput = $('#admission_date');
        var travelTicketCategorySection = $('.travel-ticket-category-section');

        if (selectedDestination === 'external') {
            var admissionDate = admissionDateInput.val();

            if (admissionDate) {
                var currentDate = new Date();
                var admissionDateObj = new Date(admissionDate);
                admissionDateObj.setMonth(admissionDateObj.getMonth() + 11);

                if (currentDate > admissionDateObj) {
                   
                    travelTicketCategorySection.show();
                } else {
                
                    travelTicketCategorySection.hide();
                }
            } else {
               
                travelTicketCategorySection.hide();
            }
        } else {
         
            travelTicketCategorySection.hide();
        }
    });
});
</script>

