
<div class="modal fade"  id="editbuildingModal" tabindex="-1" role="dialog" aria-labelledby="editbuildingModal">
<div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateBuilding', 'buildingId'], 'method' => 'post', 'id' => 'edit_building_form']) !!}

        <input type="hidden" id="buildingIdInput" name="buildingIdInput">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">@lang( 'housingmovements::lang.edit_building' )</h4>
        </div>
    
        <div class="modal-body">
            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('name', __('housingmovements::lang.building_name') . ':*') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.building_name'), 'required']) !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('address', __('housingmovements::lang.address') . ':') !!}
                    {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.address'),'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('city', __('housingmovements::lang.city') . ':*') !!}
                    {!! Form::select('city', $cities, null, ['class' => 'form-control select2', 'style'=>'height:40px; width:100%',
                         'placeholder' => __('housingmovements::lang.city'), 'required']) !!}
                </div>

               
                <div class="form-group col-md-6">
                    {!! Form::label('guard', __('housingmovements::lang.building_guard') . ':*') !!}
                    {!! Form::select('guard', $users2, null, ['class' => 'form-control select2','style'=>'height:40px; width:100%', 'placeholder' => __('housingmovements::lang.building_guard'), 'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('supervisor', __('housingmovements::lang.building_supervisor') . ':*') !!}
                    {!! Form::select('supervisor', $users2, null, ['class' => 'form-control select2','style'=>'height:40px; width:100%', 'placeholder' => __('housingmovements::lang.building_supervisor'), 'required']) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('cleaner', __('housingmovements::lang.building_cleaner') . ':*') !!}
                    {!! Form::select('cleaner', $users2, null, ['class' => 'form-control select2','style'=>'height:40px; width:100%', 'placeholder' => __('housingmovements::lang.building_cleaner'), 'required']) !!}
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
