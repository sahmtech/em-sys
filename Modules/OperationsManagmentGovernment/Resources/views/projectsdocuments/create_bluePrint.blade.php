<style>
    

/* Placeholder visibility */
.custom-input::placeholder {
    color: #6c757d; /* Slightly muted color for placeholder */
    font-style: italic; /* Make placeholder text italic */
}

/* Custom select input with placeholder */


    /* General Input Styling */
    .custom-input,
    .custom-input-file,
    .custom-textarea {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 10px;
        font-size: 16px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .custom-input:focus,
    .custom-input-file:focus,
    .custom-textarea:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    /* File input custom style */
    .custom-input-file {
        padding: 8px;
        font-size: 14px;
        background-color: #f8f9fa;
    }

    .custom-input-file:focus {
        border-color: #007bff;
    }

    /* Buttons */
    .btn {
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    /* Custom label styling */
    .form-label {
        font-size: 14px;
        font-weight: bold;
        color: #333;
    }

    /* Modal specific styles */
    .modal-body {
        padding: 30px;
    }

    .modal-dialog {
        max-width: 900px;
    }

    .single-field {
        margin-bottom: 15px;
    }

    .text-danger {
        font-size: 12px;
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {

        .col-md-5,
        .col-md-6 {
            max-width: 100%;
            margin-bottom: 10px;
        }

        .btn {
            width: 100%;
            margin-top: 15px;
        }
    }
</style>

<div class="modal-dialog modal-lg" id="add_document_BluePrint_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fas fa-plus"></i> @lang('operationsmanagmentgovernment::lang.add_project_report')
            </h4>
        </div>

        <div class="modal-body py-4">
            <div class="row">
                <div class="col-md-12">
                    <section class="content">
                        {!! Form::open([
                        'url' =>
                        action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@storeBluePrint'),
                        'method' => 'post',
                        'id' => 'doc_add_form',
                        'files' => true
                        ]) !!}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('sales_project_id',
                                    __('operationsmanagmentgovernment::lang.select_project'), ['class' => 'form-label'])
                                    !!}
                                    <span class="text-danger" style="font-size: 10px;"> *</span>
                                    {!! Form::select('sales_project_id', $sales_projects, null, [
                                    'class' => 'form-control custom-input rounded-3',
                                    'required',
                                    'id' => 'sales_project_id',
                                    'placeholder' => __('operationsmanagmentgovernment::lang.select_project'),
                                    'aria-label' => __('operationsmanagmentgovernment::lang.select_project')
                                    ]) !!}
                                </div>
                            </div>
                        </div>


                        <div id="dynamic-fields">
                            <div class="row single-field mb-3">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('name[]', __('followup::lang.doc_name'), ['class' =>
                                        'form-label']) !!}
                                        <span class="text-danger" style="font-size: 10px;"> *</span>
                                        {!! Form::text('name[]', null, [
                                        'class' => 'form-control custom-input rounded-3',
                                        'required',
                                        'placeholder' => __('followup::lang.doc_name'),
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('attachment[]', __('request.attachment'), ['class' =>
                                        'form-label']) !!}
                                        {!! Form::file('attachment[]', [
                                        'class' => 'form-control custom-input-file rounded-3',
                                        'required'
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex align-items-center justify-content-between">
                                    <button type="button" class="btn btn-danger remove-field rounded-3">
                                        <i class="fa fa-trash"></i> @lang('messages.delete')
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="button" id="add-more" class="btn btn-success rounded-3">
                                    <i class="fa fa-plus"></i> @lang('messages.add_attachment')
                                </button>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('description', __('request.description') . ':', ['class' =>
                                    'form-label']) !!}
                                    {!! Form::textarea('description', null, [
                                    'class' => 'form-control custom-textarea rounded-3',
                                    'rows' => 4,
                                    'placeholder' => __('request.description')
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3" style="width: 20%;">
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
        // Add new dynamic field
        $("#add-more").click(function () {
            let newField = `
                <div class="row single-field mt-3">
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
                        <button type="button" class="btn btn-danger remove-field">  <i class="fa fa-trash"></i> @lang('messages.delete') </button>
                    </div>
                </div>
            `;
            $("#dynamic-fields").append(newField);
        });

        // Remove dynamic field
        $(document).on("click", ".remove-field", function () {
            $(this).closest(".single-field").remove();
        });
    });
</script>