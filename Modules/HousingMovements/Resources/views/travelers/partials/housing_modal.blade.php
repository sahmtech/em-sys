<!-- Modal HTML structure -->
<div id="bulkEditModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
       
        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.housed')</h4>
        </div>
        {!! Form::open(['url' => action( [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_data']), 'method' => 'post', 'id' => 'bulk_edit_form' ]) !!}
            <div class="modal-body">
           
                        <div class="form-group col-md-6">
                            {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                            {!! Form::select('htr_building', $buildings,
                                null,
                                [  'class' => 'form-control select2','style'=>'width:100%',
                                'placeholder' => __('housingmovements::lang.htr_building'), 'required',
                                 'id' => 'htr_building_select']) !!}
                        </div>

                    <div class="form-group col-md-6">
                    {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                            {!! Form::select('room_number',[],null,
                                [  'class' => 'form-control select2','style'=>'width:100%',
                                'placeholder' => __('housingmovements::lang.room_number'), 'required',
                                'id'=>'room_number']) !!}
                    </div>

                    <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('project_name2', __('followup::lang.project_name') . ':') !!}
                        {!! Form::select('project_name2', $salesProjects, null, [
                            'class' => 'form-control select2',
                            'id' => 'project_name2',
                            'style' => 'width:100%;padding:2px;',
                            'required',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('shift_name', __('housingmovements::lang.shift_name') . ':') !!}
                        {!! Form::select('shift_name', [], null, [
                            'class' => 'form-control select2',
                            'id' => 'shift_name',
                            'style' => 'width:100%;padding:2px;',
                          
                            'placeholder' => __('lang_v1.all'),
                          
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
