<div class="modal-dialog modal-lg" id="edit_document_delivery_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('followup::lang.edit_delivery')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <section class="content">

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

                            <div class="col-sm-6" style="margin-top: 0px;">
                                {!! Form::label('worker', __('followup::lang.worker')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="worker_id" id="worker__select"
                                    style="padding: 2px;">
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}"
                                            @if ($worker->id == $document_delivery->worker_id) selected @endif>
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-sm-6" style="margin-top: 0px;">
                                {!! Form::label('documents', __('followup::lang.document_type')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="document_id" id="document_id" style="padding: 2px;">
                                    @foreach ($documents as $document)
                                        <option value="{{ $document->id }}"
                                            @if ($document->id == $document_delivery->document_id) selected @endif>
                                            {{ $document->name_ar . ' - ' . $document->name_en }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>


                        </div>

                        <div class="row" style="margin-top:8px; ">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('document', __('followup::lang.upload_document') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::file('document', ['class' => 'form-control', 'accept' => 'doc/*']) !!}


                                </div>
                            </div>

                            <div class="col-sm-6">
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



                        <div class="row" style="margin-top: 220px;">
                            <div class="col-sm-12" style="display: flex;justify-content: center;">
                                <button type="submit" style="width:50%; border-radius: 28px;"
                                    class="btn btn-primary pull-right btn-flat ">@lang('messages.save')</button>
                            </div>
                        </div>


                        {!! Form::close() !!}
                    </section>


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
