  <div class="modal-dialog modal-lg" id="edit_attendance_status" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i>@lang('essentials::lang.edit_attendance_status')</h4>
         
          </div>
    {!! Form::open(['route' => ['updateAttendanceStatus', $attendanceStatus->id], 'method' => 'put', 'id' => 'add_travel_categorie_form']) !!}

        <div class="modal-body">
          <div class="row">
            <div class="form-group col-md-6">
              {!! Form::label('name', __('essentials::lang.status') . ':*') !!}
              {!! Form::text('name', $attendanceStatus->name, [
                  'class' => 'form-control',
                  'placeholder' => __('essentials::lang.status'),
                  'required',
              ]) !!}
          </div>
          </div>
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    
    </div>

</div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->

