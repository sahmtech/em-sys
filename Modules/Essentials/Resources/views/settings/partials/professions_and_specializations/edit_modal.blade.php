
<div class="modal fade"  id="editprofessionModal" tabindex="-1" role="dialog" aria-labelledby="editprofessionModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        {!! Form::open(['route' => ['professions.update', 'professionId'], 'method' => 'post', 'id' => 'edit_profession_form']) !!}
    
            <input type="hidden" id="professionIdInput" name="professionIdInput">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">@lang( 'essentials::lang.edit_professions' )</h4>
            </div>
        
            <div class="modal-body">
                <div class="row">
                    
                    <div class="form-group col-md-6">
                        {!! Form::label('name',   __('essentials::lang.job_title') .':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('en_name', __('essentials::lang.en_name') . ' (' . __('essentials::lang.optional') . '):') !!}
                        {!! Form::text('en_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.en_name')]) !!}
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
    