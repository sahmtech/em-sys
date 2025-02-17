<div class="modal-dialog modal-lg" id="add_document_model" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white" style="background-color: white">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: red;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fas fa-plus"></i> @lang('operationsmanagmentgovernment::lang.add_project_report')
            </h4>
        </div>

        <div class="modal-body">
            {!! Form::open([
            'url' => action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@store'),
            'method' => 'post',
            'id' => 'doc_add_form',
            'files' => true
            ]) !!}

            <!-- Project Selection -->
            <div class="form-group">
                {!! Form::label('sales_project_id', __('operationsmanagmentgovernment::lang.select_project')) !!}
                <span class="text-danger">*</span>
                {!! Form::select('sales_project_id', $sales_projects, null, [
                'class' => 'form-control custom-select',
                'required',
                'id' => 'sales_project_id',
                'placeholder' => __('operationsmanagmentgovernment::lang.select_project')
                ]) !!}
            </div>

            {{-- Projects --}}
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('project_select_first',
                    __('operationsmanagmentgovernment::lang.project_select_first') . ':*') !!}
                    {!! Form::select('project_department_id', [], null, [
                    'class' => 'form-control',
                    'id' => 'project_department_id',
                    'style' => 'height:40px',
                    'required',
                    'placeholder' => __('operationsmanagmentgovernment::lang.project_select_first'),
                    ]) !!}
                </div>
            </div>


            <!-- Dynamic Fields for Document Name and Attachment -->
            <div id="dynamic-fields">
                <div class="field-group mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                {!! Form::label('name[]', __('operationsmanagmentgovernment::lang.project_document'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('name[]', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.project_document'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                {!! Form::label('attachment[]', __('request.attachment')) !!}
                                {!! Form::file('attachment[]', [
                                'class' => 'form-control',
                                'required'
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-danger remove-field">
                                <i class="fa fa-trash"></i> @lang('messages.delete')
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add More Attachments -->
            <div class="text-center mt-3">
                <button type="button" id="add-more" class="btn btn-success">
                    <i class="fa fa-plus"></i> @lang('messages.add_attachment')
                </button>
            </div>

            <!-- Description Field -->
            <div class="form-group mt-4">
                {!! Form::label('description', __('request.description') . ':') !!}
                {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'rows' => 4,
                'placeholder' => __('request.description')
                ]) !!}
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg" style="width: 25%; border-radius: 5px;">
                    @lang('messages.save')
                </button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Add more attachment fields dynamically
        $("#add-more").click(function () {
            let newField = `
                <div class="field-group mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="name_ar[]">@lang('followup::lang.doc_name') <span class="text-danger">*</span></label>
                                <input type="text" name="name[]" class="form-control" required placeholder="@lang('followup::lang.doc_name')">
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="attachment[]">@lang('request.attachment')</label>
                                <input type="file" name="attachment[]" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-danger remove-field">
                                <i class="fa fa-trash"></i> @lang('messages.delete')
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $("#dynamic-fields").append(newField);
        });

        // Remove the field on clicking trash icon
        $(document).on("click", ".remove-field", function () {
            $(this).closest(".field-group").remove();
        });
    });


    $(document).ready(function () {
    $('#sales_project_id').change(function () {
        var projectId = $(this).val(); // Get selected contact ID

        if (projectId) {
            $.ajax({
                url: '/get-departments-project/' + projectId, // Updated API route

                data: function(d) {
                   
                },
                
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#project_department_id').empty(); // Clear existing options
                    $('#project_department_id').append('<option value="">' + "حدد قسم المشروع" + '</option>');

                    $.each(data, function (key, value) {
                        $('#project_department_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        } else {
            $('#project_department_id').empty();
        }
    });
});
</script>