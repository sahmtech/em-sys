<div class="modal fade"  id="createRoomModal" tabindex="-1" role="dialog" aria-labelledby="createRoomModal">
        <div class="modal-dialog" role="document">

                <div class="modal-content">
                    {!! Form::open(['route' => 'storeRoom']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.add_room')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                                {!! Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.room_number'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-4">
                                {!! Form::label('area', __('housingmovements::lang.area') . ':') !!}
                                {!! Form::text('area', null,
                                     ['class' => 'form-control',
                                      'placeholder' => __('housingmovements::lang.area'),'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                                {!! Form::select('htr_building',
                                     $buildings, null, ['class' => 'form-control select2','style'=>'width:100%;height:40px;',
                                     'placeholder' => __('housingmovements::lang.htr_building'), 'required']) !!}
                            </div>
        
                        
                            <div class="form-group col-md-4">
                                {!! Form::label('beds_count', __('housingmovements::lang.beds_count') . ':*') !!}
                                {!! Form::number('beds_count', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.beds_count'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-8">
                                {!! Form::label('contents', __('housingmovements::lang.contents') . ':*') !!}
                                {!! Form::textarea('contents', null, ['class' => 'form-control ', 'placeholder' => __('housingmovements::lang.contents'),'row'=>'1']) !!}
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
