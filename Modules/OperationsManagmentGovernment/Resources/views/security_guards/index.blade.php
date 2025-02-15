@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.security_guards'))
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
        <span>@lang('operationsmanagmentgovernment::lang.security_guards')</span>
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
                    href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController@create') }}"
                    data-href="{{ action('Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController@create') }}"
                    data-container="#add_document_model">
                    <i class="fas fa-plus"></i> @lang('messages.add')
                </a>
            </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="document_table" style="margin-bottom: 100px;">
                    <thead>
                        <tr>
                            <th>@lang('operationsmanagmentgovernment::lang.full_name')</th>
                            <th>@lang('operationsmanagmentgovernment::lang.fingerprint_no')</th>
                            <th>@lang('operationsmanagmentgovernment::lang.profession')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="modal fade" id="add_document_model" tabindex="-1" role="dialog"></div>
            @endcomponent
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
            order: [0, 'desc'],
            ajax: {
                url: '{{ route('security_guards') }}',
                data: function(d) {
                    d.carTypeSelect = $('#carTypeSelect').val();
                    d.driver_select = $('#driver_select').val();
                }
            },
            columns: [
                { data: 'full_name' },
                { data: 'fingerprint_no' },
                { data: 'profession' },
                { data: 'action' }
            ]
        });

        

        // Delete Document Handler
        $(document).on('click', '.delete_security_guard_button', function() {
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

       
        // Toastr Notifications
        showToastrMessages();
          // Show Toastr Messages
       function showToastrMessages() {
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
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
    });



      
</script>

@endsection