<!-- Modal for Return Visa -->
<div class="modal fade" id="absentreportModal" tabindex="-1" role="dialog" aria-labelledby="returnVisaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnVisaModalLabel">{{ __('essentials::lang.absent_report') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {!! Form::open(['url' => action(  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'post_absent_report_data']), 'method' => 'post', 'id' => 'bulk_absent_edit_form' ]) !!}
            <div class="modal-body">
            
             
                <!-- Add end date input -->
                <div class="form-group">
                    {!! Form::label('end_date', __('essentials::lang.end_date')) !!}
                    {!! Form::date('end_date', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off', 'required']) !!}
                </div>


            </div>



            <div class="clearfix"></div>
            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

          
        </div>
        {!! Form::close() !!}
    </div>
</div>