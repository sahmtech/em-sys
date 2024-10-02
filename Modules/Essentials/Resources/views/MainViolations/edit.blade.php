<div class="modal-dialog modal-lg" id="edit_main-violations" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('essentials::lang.edit_main-violations')</h4>
        </div>
        {!! Form::open(['route' => 'update-main-violations', 'enctype' => 'multipart/form-data']) !!}

        <div class="modal-body">

            <div class="row">
                <div class="form-group col-md-6">
                    {!! Form::label('description', __('essentials::lang.description') . ' *') !!}

                    {!! Form::text('description', $Violations->description, [
                        'class' => 'form-control',
                        'id' => 'description',
                        'required',
                        'placeholder' => __('essentials::lang.description'),
                    ]) !!}
                </div>
                <input type="hidden" value="{{$Violations->id}}" name="id"/>
                


            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {

        $('#edit_driver_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });

            $('#car__id').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });
        });

    });
</script>
