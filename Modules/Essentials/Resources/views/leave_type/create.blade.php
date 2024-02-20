<div class="modal fade" id="add_leave_type_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            {!! Form::open([
                'url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'store']),
                'method' => 'post',
                'id' => 'add_leave_type_form',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_leave_type')</h4>
            </div>

            <div class="modal-body">
                <div class="form-group col-md-6">
                    {!! Form::label('leave_type', __('essentials::lang.leave_type') . ':*') !!}
                    {!! Form::text('leave_type', null, [
                        'class' => 'form-control',
                        'placeholder' => __('essentials::lang.leave_type'),
                    ]) !!}
                </div>


                <div class="form-group col-md-6">
                    {!! Form::label('leave_duration', __('essentials::lang.total_leave_duration') . ':*') !!}
                    {!! Form::number('duration', null, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('essentials::lang.leave_duration'),
                    ]) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('max_leave_count', __('essentials::lang.max_leave_count') . ':') !!}
                    {!! Form::number('max_leave_count', null, [
                        'class' => 'form-control',
                        'placeholder' => __('essentials::lang.max_leave_count'),
                    ]) !!}
                    <small class="form-text text-muted" style='color:red;'>
                        @lang('essentials::lang.Leave the input empty to take the value "undefined"')
                    </small>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label(
                        'due_date',
                        __('essentials::lang.due_date') .
                            ' ( ' .
                            __('essentials::lang.number of months after adminssion date') .
                            ' ) ' .
                            ':*',
                    ) !!}
                    {!! Form::number('due_date', null, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('essentials::lang.due_date'),
                    ]) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('gender', __('essentials::lang.due_categorie') . ':*') !!}
                    {!! Form::select(
                        'gender',
                        [
                            'male' => __('essentials::lang.male'),
                            'female' => __('essentials::lang.female'),
                            'both' => __('essentials::lang.both'),
                        ],
                        null,
                        [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'id' => 'gender',
                            'required',
                            'placeholder' => __('messages.please_select'),
                        ],
                    ) !!}
                </div>


                <div class="form-group col-md-6">
                    {!! Form::label('include_salary', __('essentials::lang.include_salary_percent') . ':') !!}

                    {!! Form::number('include_salary', null, [
                        'class' => 'form-control',
                        'style' => 'height:40px;',
                        'id' => 'include_salary',
                    
                        'placeholder' => __('essentials::lang.include_salary_percent'),
                    ]) !!}


                </div>

                <div class="form-group col-md-12">
                    <strong>@lang('essentials::lang.leave_count_interval')</strong><br>
                    <label class="radio-inline">
                        {!! Form::radio('leave_count_interval', 'month', false) !!} @lang('essentials::lang.current_month')
                    </label>
                    <label class="radio-inline">
                        {!! Form::radio('leave_count_interval', 'year', false) !!} @lang('essentials::lang.current_fy')
                    </label>
                    <label class="radio-inline">
                        {!! Form::radio('leave_count_interval', null, false) !!} @lang('lang_v1.none')
                    </label>
                </div>
                {{-- <div id="annualOptionsContainer" style="display: none;">
                    <div class="form-group">
                        <strong>@lang('essentials::lang.Annual_options')</strong><br>




                        {!! Form::text('allowance', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.leave_type_allowance'),
                        ]) !!}
                        </br>
                        <label class="radio-inline">
                            {!! Form::radio('Deportable', 'month', false) !!} @lang('essentials::lang.Deportable')
                        </label>
                    </div>
                </div> --}}
				<div class="form-group col-md-4">
				
					{!! Form::checkbox('extendable', 1, false, [
						'class' => 'form-check-input',
						'id' => 'extendable'
					]) !!}
					{!! Form::label('extendable', __('essentials::lang.extendable') ) !!}
				</div>


            </div>


            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

{{-- <script type="text/javascript">
    $(document).ready(function() {

        // function isCurrentYearEnded() {
        //     var currentDate = new Date();
        //     var currentYear = currentDate.getFullYear();
        //     var yearEndDate = new Date(currentYear, 11, 31);
        //     return currentDate > yearEndDate;
        // }


        // function toggleAnnualOptions() {
        //     var leaveType = $("select[name='leave_type']").val();
        //     var annualOptionsContainer = $("#annualOptionsContainer");

        //     if (leaveType === 'Annual' && isCurrentYearEnded()) {
        //         annualOptionsContainer.show();
        //     } else {
        //         annualOptionsContainer.hide();
        //     }
        // }

        // Initial check on page load
        // toggleAnnualOptions();

        // // Add an event listener to the leave_type select element
        // $("select[name='leave_type']").on('change', toggleAnnualOptions);
    });
</script> --}}
