@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.project_report'))

<style>
    .close {
        color: red !important;
        font-size: 14px;
    }

    /* Custom styling for the select element */
    .custom-select {
        padding: 10px;
        /* Adjust padding for uniformity */
        font-size: 14px;
        /* Ensure readability */
        color: #495057;
        /* Dark text color for better contrast */
        border: 1px solid #ced4da;
        /* Border color for better visibility */
    }

    /* Style the placeholder text */
    .custom-select option:first-child {
        color: #6c757d;
        /* Lighter color for placeholder text */
        font-style: italic;
        /* Italicize placeholder text */
    }

    a.btn.btn-primary:hover {
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

    <style>.btn {
        padding: 8px 15px;
        margin: 5px;
        font-size: 14px;
        border-radius: 4px;
    }



    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .text-muted {
        color: #6c757d;
    }

    /* Custom styling for the large SweetAlert popup */
    .large-popup {
        max-width: 500px;
        /* Set maximum width */
        width: 80%;
        /* Set relative width */
        font-size: 14px;
        /* Optional: change font size for readability */
    }
</style>
@section('content')
<section class="content-header">
    <h1>
        <span>@lang('operationsmanagmentgovernment::lang.project_report')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">


    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
            <div class="box-tools">
                <a class="btn btn-primary pull-right m-5 btn-modal"
                    href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@create') }}"
                    data-href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@create') }}"
                    data-container="#add_document_model">
                    <i class="fas fa-plus"></i> @lang('messages.add')
                </a>
            </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="document_table" style="margin-bottom: 100px;">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('operationsmanagmentgovernment::lang.project_department')</th>
                            <th>@lang('followup::lang.created_by')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.attachments')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade" id="add_document_model" tabindex="-1" role="dialog"></div>
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
                url: '{{ route('projects_documents') }}',
                data: function(d) {
                    d.carTypeSelect = $('#carTypeSelect').val();
                    d.driver_select = $('#driver_select').val();
                }
            },
            columns: [
                { data: 'name' },
                {data: 'project_department'},
                { data: 'created_by' },
                { data: 'note' },
                { data: 'attachments' },
                { data: 'action' }
            ]
        });

        // File Preview Handler
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
            var content = generateFilePreviewContent(fileUrl, fileExtension);

            modal.find('#fileContent').html(content);
            modal.modal('show');
        });

        // Delete Document Handler
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
                width: '500px',
                padding: '10px',
                customClass: {
                    popup: 'large-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    handleDocumentDeletion(href);
                }
            });
        });

        // Edit Document Handler
        $(document).on('click', '.edit_document_button', function() {
            var href = $(this).data('href');
            handleDocumentEdit(href);
        });

        // Toastr Notifications
        showToastrMessages();
    });

    // Generate File Preview Content
    function generateFilePreviewContent(fileUrl, fileExtension) {
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            return `<img src="${fileUrl}" class="img-fluid" />`;
        } else if (fileExtension === 'pdf') {
            return `<iframe src="${fileUrl}" width="100%" height="600px" style="border: none;"></iframe>`;
        } else {
            return `<a href="${fileUrl}" target="_blank" class="btn btn-secondary">
                        @lang('messages.download_file')
                    </a>`;
        }
    }

    // Handle Document Deletion
    function handleDocumentDeletion(href) {
        $.ajax({
            method: "DELETE",
            url: href,
            dataType: "json",
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#document_table').DataTable().ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    }

    // Handle Document Edit
    function handleDocumentEdit(href) {
        $.ajax({
            method: "GET",
            url: href,
            dataType: "json",
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#document_table').DataTable().ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    }

    // Show Toastr Messages
    function showToastrMessages() {
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    }
</script>
@endsection