
<div id="roomsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" >
            <div class="modal-content" >
        
            <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('housingmovements::lang.housed')</h4>
            </div>

            {!! Form::open(['url' => action( [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'room_data']), 'method' => 'post', 'id' => 'bulk_edit' ]) !!}
         
                <div class="modal-body">
                <input name="building_htr" id="building_htr" type="hidden" value="300" />
                        
                <div class="row">
                       
                
              
{{--  <div class="form-group col-md-6">
                                {!! Form::label('worker_id', __('followup::lang.worker_name') . ':*') !!}
                                {!! Form::select('worker_id[]', $workers, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'required',
                                    'id' => 'worker',
                                    'style' => 'height: 60px; width: 250px;',
                                ]) !!}
                  </div>
    --}}
              
                
                </div>



                </div>
                <div class="clearfix"></div>
                <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
