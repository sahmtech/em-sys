<div class="modal-dialog modal-lg" id="edit_document_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i>
                @lang('operationsmanagmentgovernment::lang.edit_project_report')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <section class="content">
                        {!! Form::model($document, [
                        'url' => action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@update', $document->id),
                        'method' => 'put',
                        'id' => 'doc_edit_form',
                        'files' => true
                        ]) !!}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('sales_project_id', __('operationsmanagmentgovernment::lang.select_project')) !!}
                                    <span style="color: red; font-size: 10px;"> *</span>
                                    {!! Form::select('sales_project_id', $sales_projects, $document->sales_project_id, [
                                    'class' => 'form-control',
                                    'required',
                                    'id' => 'sales_project_id',
                                    'placeholder' => __('operationsmanagmentgovernment::lang.select_project')
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div id="dynamic-fields">
                            @foreach ($document->attachments as $attachment)
                                <div class="row single-field">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            {!! Form::label('name[]', __('followup::lang.doc_name')) !!}
                                            <span style="color: red; font-size:10px"> *</span>
                                            {!! Form::text('name[]', $attachment->name, [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('followup::lang.doc_name'),
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            {!! Form::label('attachment[]', __('request.attachment')) !!}
                                            {!! Form::file('attachment[]', [
                                            'class' => 'form-control',
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger remove-field">
                                            <i class="fa fa-trash"></i> @lang('messages.delete')
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="button" id="add-more" class="btn btn-success">
                                    <i class="fa fa-plus"></i> @lang('messages.add_attachment')
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('description', __('request.description') . ':') !!}
                                    {!! Form::textarea('description', $document->description, [
                                    'class' => 'form-control',
                                    'rows' => 4,
                                    'placeholder' => __('request.description')
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-flat"
                                    style="width: 20%; border-radius: 5px;">
                                    @lang('messages.save')
                                </button>
                            </div>
                        </div>

                        {!! Form::close() !!}
                    </section>
                </div>
            </div>
        </div>
    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#add-more").click(function () {
            let newField = `
                <div class="row single-field">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="name_ar[]">@lang('followup::lang.doc_name') <span style="color: red; font-size:10px"> *</span></label>
                            <input type="text" name="name[]" class="form-control" required placeholder="@lang('followup::lang.doc_name')">
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attachment[]">@lang('request.attachment')</label>
                            <input type="file" name="attachment[]" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-field">  <i class="fa fa-trash"></i> حذف </button>
                    </div>
                </div>
            `;
            $("#dynamic-fields").append(newField);
        });

        $(document).on("click", ".remove-field", function () {
            $(this).closest(".single-field").remove();
        });
    });
</script>
