<!-- Modal HTML structure -->
<div id="bulkEditModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
       
        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.housed')</h4>
        </div>
            <div class="modal-body">
           
                        <div class="form-group col-md-6">
                            {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                            {!! Form::select('htr_building', $buildings,
                                null,
                                [  'class' => 'form-control select2','style'=>'width:100%',
                                'placeholder' => __('housingmovements::lang.htr_building'), 'required', 'id' => 'htr_building_select']) !!}
                        </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                        {!! Form::number('room_number', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.room_number'), 'required']) !!}
                    </div>
            </div>

            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            
        </div>
    </div>
</div>
