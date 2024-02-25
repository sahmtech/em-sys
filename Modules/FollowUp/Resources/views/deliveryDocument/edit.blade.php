<div class="modal-dialog modal-lg" id="edit_document_delivery_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
           
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('followup::lang.edit_delivery')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                  

                        {!! Form::open([
                            'url' => action(
                                'Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController@update',
                                $document_delivery->id,
                            ),
                            'method' => 'put',
                            'enctype' => 'multipart/form-data',
                            'id' => 'doc_add_form',
                        ]) !!}

                        <div class="row">

                            <div class="col-md-6" >
                                {!! Form::label('worker', __('followup::lang.worker')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select">
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}"
                                            @if ($worker->id == $document_delivery->user_id) selected @endif>
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                                <div class="col-md-6">
                                {!! Form::label('documents', __('followup::lang.document_type')) !!}<span style="color: red; font-size:10px"> *</span>
                                <select class="form-control" name="document_id" id="document_id">
                                    @foreach ($documents as $document)
                                        <option value="{{ $document->id }}"
                                            @if ($document->id == $document_delivery->document_id) selected @endif>
                                            {{ $document->name_ar . ' - ' . $document->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="inputTitleContainer" @if ($document_delivery->document_id != 11) style="display: none;" @endif>
                                {!! Form::label('title', __('followup::lang.title')) !!}<span style="color: red; font-size:10px"> *</span>
                                {!! Form::text('title', $document_delivery->title, ['class' => 'form-control', 'id' => 'title']) !!}
                            </div>


                        </div>

                        <div class="row" >
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('document', __('followup::lang.upload_document') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::file('document', ['class' => 'form-control', 'accept' => 'doc/*']) !!}


                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('nots', __('followup::lang.nots') . '  ') !!}
                                    {!! Form::text('nots', $document_delivery->nots, [
                                        'class' => 'form-control',
                                        'placeholder' => __('followup::lang.nots'),
                                        'id' => 'nots',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                     <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>



                        {!! Form::close() !!}
                


                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {

        $('#edit_document_delivery_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#edit_document_delivery_model'),
                width: '100%',
            });

            $('#document_id').select2({
                dropdownParent: $(
                    '#edit_document_delivery_model'),
                width: '100%',
            });
        });

    });
</script>



<script>
    $(document).ready(function() {
        $('#document_id').change(function() {
            var selectedOption = $(this).val();
            if (selectedOption == 11) {
                $('#inputTitleContainer').show();
            } else {
                $('#inputTitleContainer').hide();
            }
        });

      
        $('#document_id').trigger('change');
    });
</script>