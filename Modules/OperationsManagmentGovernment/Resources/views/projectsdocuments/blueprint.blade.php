@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.project_diagram'))
<style>
    a.btn.btn-primary:hover {
        background-color: #0056b3;
        cursor: pointer;
        /* لون أغمق عند التمرير */
        text-decoration: none;
    }

    /* Custom styling for the large SweetAlert popup */
    .large-popup {
        max-width: 500px;
        /* Set maximum width */
        width: 80%;
        /* Set relative width */
        font-size: 18px;
        /* Optional: change font size for readability */
    }
</style>
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('operationsmanagmentgovernment::lang.project_diagram')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        {{-- Filters Section (Uncomment if needed) --}}
        {{--
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
            @endcomponent
        </div>
        --}}
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
            <div class="box-tools">
                <a class="btn btn-primary pull-right m-5 btn-modal"
                    href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@createBluePrint') }}"
                    data-href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@createBluePrint') }}"
                    data-container="#add_document_BluePrint_model">
                    <i class="fas fa-plus"></i> @lang('messages.add')
                </a>
            </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="document_table" style="margin-bottom: 100px;">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.created_by')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.attachments')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade" id="add_document_BluePrint_model" tabindex="-1" role="dialog"></div>
            <div class="modal fade" id="edit_document_model" tabindex="-1" role="dialog"></div>
            @endcomponent
        </div>

        <!-- File Preview Modal -->
        <div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="fileModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fileModalLabel">File Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="fileContent" class="text-center"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        var document_table = $('#document_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('projects_documents.blueprint') }}',
                data: function(d) {
                    d.carTypeSelect = $('#carTypeSelect').val();
                    d.driver_select = $('#driver_select').val();
                }
            },
            columns: [
                { data: 'name' },
                { data: 'created_by' },
                { data: 'note' },
                {
                    data: 'attachments',
                   
                },
                { data: 'action' }
            ]
        });

        // Handle File Preview
        $(document).on('click', '.preview-file', function(e) {
            e.preventDefault();
            var fileUrl = $(this).data('file-url');
            var fileName = $(this).data('file-name');
            var modal = $('#fileModal');
            modal.find('.modal-title').text(fileName);

            if (!fileUrl || fileUrl.trim() === '') {
                toastr.error("@lang('messages.invalid_file_url')");
                return;
            }

            var fileExtension = fileUrl.split('.').pop().toLowerCase();
            var content = '';

            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                content = `<img src="${fileUrl}" class="img-fluid" />`;
            } else if (fileExtension === 'pdf') {
                content = `<iframe src="${fileUrl}" width="100%" height="600px" style="border: none;"></iframe>`;
            } else {
                content = `<a href="${fileUrl}" target="_blank" class="btn btn-secondary">
                            @lang('messages.download_file')
                           </a>`;
            }

            modal.find('#fileContent').html(content);
            modal.modal('show');
        });

        // Delete Document
        $(document).on('click', '.delete_document_button', function() {
    var href = $(this).data('href');
    Swal.fire({
        title: "هل أنت متأكد؟",
        text: "هل أنت متأكد أنك تريد حذف هذا المستند؟",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "نعم ، احذف",
        cancelButtonText: "تراجع",
        width: '500px',  // Set the width to a larger value to make the dialog bigger
        padding: '10px',  // Optional: adjust padding for more space inside the dialog
        customClass: {
            popup: 'large-popup'  // Optionally add a custom class for further styling
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                method: "DELETE",
                url: href,
                dataType: "json",
                success: function(result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        document_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        }
    });
});

        // Edit Document (If needed)
        $(document).on('click', '.edit_document_button', function() {
            var href = $(this).data('href');
            $.ajax({
                method: "GET",
                url: href,
                dataType: "json",
                success: function(result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        document_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
    });


</script>

@endsection