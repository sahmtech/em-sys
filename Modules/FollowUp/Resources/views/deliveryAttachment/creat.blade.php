<div class="modal-dialog modal-lg" id="add_document_delivery_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('followup::lang.add_attachment')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">


                    {!! Form::open([
                    'url' => action('Modules\FollowUp\Http\Controllers\FollowupDeliveryAttachmentController@store'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'id' => 'doc_add_form',
                    ]) !!}

                    <div class="row">

                        <div class="col-md-6">
                            {!! Form::label('worker', __('followup::lang.attachment_owner')) !!}<span
                                style="color: red; font-size:10px"> *</span>
                            <select class="form-control" required name="user_id" id="worker__select"
                                style="padding: 2px;">
                                <option value="">{{ __('followup::lang.select_employee_or_worker') }}</option>
                                @foreach ($workers as $worker)
                                <option value="{{ $worker->id }}">
                                    {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->mid_name
                                    . ' ' . $worker->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            {!! Form::label('documents', __('followup::lang.attachment_type')) !!}<span
                                style="color: red; font-size:10px"> *</span>
                            <select class="form-control" required name="document_id" id="document_id"
                                style="padding: 2px;">
                                <option value="">{{ __('followup::lang.select_attach_type') }}</option>
                                @foreach ($documents as $document)
                                <option value="{{ $document->id }}">
                                    {{ $document->name_ar . ' - ' . $document->name_en }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="inputTitleContainer" style="display: none;">
                            {!! Form::label('title', __('followup::lang.title')) !!}<span
                                style="color: red; font-size:10px"> *</span>
                            {!! Form::text('title', null, ['class' => 'form-control', 'id' => 'title']) !!}
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('document', __('followup::lang.upload_attachment') . ' ') !!}<span
                                    style="color: red; font-size:10px"> *</span>
                                {!! Form::file('document', ['class' => 'form-control', 'required', 'accept' => 'doc/*'])
                                !!}


                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('nots', __('followup::lang.nots') . ' ') !!}
                                {!! Form::text('nots', '', [
                                'class' => 'form-control',
                                'placeholder' => __('followup::lang.nots'),
                                'id' => 'nots',
                                ]) !!}
                            </div>
                        </div>
                    </div>



                    {{-- <div class="row">
                        <div class="col-md-12">
                            <button type="submit"
                                class="btn btn-primary pull-right btn-flat ">@lang('messages.save')</button>
                        </div>
                    </div> --}}


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default"
                            data-dismiss="modal">@lang('messages.close')</button>
                    </div>


                    {!! Form::close() !!}



                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {

        $('#add_document_delivery_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#add_document_delivery_model'),
                width: '100%',
            });

            $('#document_id').select2({
                dropdownParent: $(
                    '#add_document_delivery_model'),
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
    });
</script>