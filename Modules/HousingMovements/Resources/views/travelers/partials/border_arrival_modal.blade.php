<!-- Modal HTML structure -->
<div id="arrivedModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
       
        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.bordar_arrival')</h4>
        </div>
            <div class="modal-body">
           

            <div class="form-group col-md-4">
                        {!! Form::label('worker_name', __('housingmovements::lang.worker_name') . ':*') !!}
                        {!! Form::number('worker_name', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.worker_name'), 'required']) !!}
            </div>


                    <div class="form-group col-md-4">
                        {!! Form::label('passport_number', __('housingmovements::lang.passport_number') . ':*') !!}
                        {!! Form::number('passport_number', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.passport_number'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('border_no', __('housingmovements::lang.border_no') . ':*') !!}
                        {!! Form::number('border_no', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.border_no'), 'required']) !!}
                      </div>

            </div>

            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            
        </div>
    </div>
</div>
