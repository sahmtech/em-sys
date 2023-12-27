<div class="modal fade"  id="editroomModal" tabindex="-1" role="dialog" aria-labelledby="editroomModal">
<div class="modal-dialog" role="document">

    <div class="modal-content">
        {!! Form::open(['route' => ['updateRoom', 'roomId'], 'method' => 'post', 'id' => 'edit_room_form']) !!}
        @csrf
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'housingmovements::lang.edit_room' )</h4>
        </div>
    
        <div class="modal-body">
            <div class="row">
            <input type="hidden" id="roomIdInput" name="roomIdInput">
                <div class="form-group col-md-6">
                    {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                    {!! Form::text('room_number',null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.room_number'), 'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('area', __('housingmovements::lang.area') . ':') !!}
                    {!! Form::text('area',null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.area'),'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                    {!! Form::select('htr_building', $buildings,null, ['class' => 'form-control select2','style'=>'width:100%; height:40px', 'placeholder' => __('housingmovements::lang.htr_building'), 'required']) !!}
                </div>

            
                <div class="form-group col-md-6">
                    {!! Form::label('beds_count', __('housingmovements::lang.beds_count') . ':*') !!}
                    {!! Form::number('beds_count',null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.beds_count'), 'required']) !!}
                </div>
                <div class="clearfix" ></div>
                <div class="form-group col-md-6">
                    {!! Form::label('contents', __('housingmovements::lang.contents') . ':*') !!}
                    {!! Form::textarea('contents', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.contents'),'row'=>'2']) !!}
                </div>
            </div>
          
        </div>
    
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    
        {!! Form::close() !!}
  
    </div>
</div>