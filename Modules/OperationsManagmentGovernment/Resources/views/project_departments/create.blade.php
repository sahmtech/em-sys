<div class="modal-dialog modal-lg" id="add_document_model" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white" style="background-color: white">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: red;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fas fa-plus"></i> @lang('operationsmanagmentgovernment::lang.add_project_department')
            </h4>
        </div>

        <div class="modal-body">
            {!! Form::open([
            'url' => action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDepartmentController@store'),
            'method' => 'post',
            'id' => 'doc_add_form',
            'files' => true
            ]) !!}




            <!-- Dynamic Fields for Document Name and Attachment -->
            <div id="dynamic-fields">
                <div class="field-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('name_ar',
                                __('operationsmanagmentgovernment::lang.project_department_name_ar'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('name_ar', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.project_department_name_ar'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                {!! Form::label('name_en',
                                __('operationsmanagmentgovernment::lang.project_department_name_en'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('name_en', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.project_department_name_en'),
                                ]) !!}
                            </div>



                        </div>

                        {{-- Contacts --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('contacts', __('sales::lang.contact_name') . ':*') !!}
                                {!! Form::select('contact_id', $contacts, null, [
                                'class' => 'form-control select2',

                                'id' => 'contactSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('sales::lang.contact_name'),
                                ]) !!}
                            </div>
                        </div>

                        {{-- Projects --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('project_name', __('sales::lang.project_name') . ':*') !!}
                                {!! Form::select('project_id', [], null, [
                                'class' => 'form-control',

                                'id' => 'projectSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('sales::lang.project_name'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-primary btn-lg">
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


        //
        $('#addItemModal').on('shown.bs.modal', function(e) {
                $('#professionSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#nationalitySearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });
                $('#specializationSearch').select2({
                    dropdownParent: $(
                        '#addItemModal'),
                    width: '100%',
                });

            });


            $(document).ready(function () {
    $('#contactSelect').change(function () {
        var contactId = $(this).val(); // Get selected contact ID

        if (contactId) {
            $.ajax({
                url: '/get-sales-projects/' + contactId, // Updated API route
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#projectSelect').empty(); // Clear existing options
                    $('#projectSelect').append('<option value="">' + "حدد مشروع" + '</option>');

                    $.each(data, function (key, value) {
                        $('#projectSelect').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        } else {
            $('#projectSelect').empty();
        }
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>