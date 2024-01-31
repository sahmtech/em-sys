@extends('layouts.app')
@section('title', __('essentials::lang.official_documents'))

@section('content')

    <section class="content-header">
        <h1>@lang('essentials::lang.official_documents')</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    @if (!empty($users))
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('user_id_filter', __('essentials::lang.doc_owner') . ':') !!}
                                {!! Form::select('user_id_filter', $users, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('user_type_filter', __('essentials::lang.doc_owner_type') . ':') !!}
                            {!! Form::select(
                                'user_type_filter',
                                [
                                   
                                    'worker' => __('essentials::lang.worker'),
                                    'employee' => __('essentials::lang.employee'),
                                    'manager' => __('essentials::lang.a_manager'),
                                ],
                                null,
                                [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('lang_v1.all'),
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_type_filter', __('essentials::lang.doc_type') . ':') !!}
                            <select class="form-control select2" name="doc_type_filter" id="doc_type_filter"
                                style="width: 100%;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="national_id">@lang('essentials::lang.national_id')</option>
                                <option value="passport">@lang('essentials::lang.passport')</option>
                                <option value="residence_permit">@lang('essentials::lang.residence_permit')</option>
                                <option value="drivers_license">@lang('essentials::lang.drivers_license')</option>
                                <option value="car_registration">@lang('essentials::lang.car_registration')</option>
                                <option value="international_certificate">@lang('essentials::lang.international_certificate')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status_filter">@lang('essentials::lang.status'):</label>
                            <select class="form-control select2" name="status_filter" id="status_filter" style="width: 100%;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="valid">@lang('essentials::lang.valid')</option>
                                <option value="expired">@lang('essentials::lang.expired')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addDocModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="official_documents_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.doc_owner')</th>
                                    <th>@lang('essentials::lang.doc_number')</th>
                                    <th>@lang('essentials::lang.doc_type')</th>
                                    <th>@lang('essentials::lang.issue_date')</th>
                                    <th>@lang('essentials::lang.issue_place')</th>
                                    <th>@lang('essentials::lang.expired_date')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>



            <div class="modal fade" id="addDocModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeOfficialDoc', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_Doc')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employees2', __('essentials::lang.doc_owner') . ':*') !!}
                                    {!! Form::select('employees2', $users, null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.select_doc_owner'),
                                        'required',
                                        'style' => 'height:40px',
                                        'id' => 'employees_select',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                                    {!! Form::select(
                                        'doc_type',
                                        [
                                            'national_id' => __('essentials::lang.national_id'),
                                            'passport' => __('essentials::lang.passport'),
                                            'residence_permit' => __('essentials::lang.residence_permit'),
                                            'drivers_license' => __('essentials::lang.drivers_license'),
                                            'car_registration' => __('essentials::lang.car_registration'),
                                            'international_certificate' => __('essentials::lang.international_certificate'),
                                            'Iban' => __('essentials::lang.Iban'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'placeholder' => __('essentials::lang.select_type'),
                                            'required',
                                        ],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':') !!}
                                    {!! Form::number('doc_number', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.doc_number'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':') !!}
                                    {!! Form::date('issue_date', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.issue_date'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':') !!}
                                    {!! Form::text('issue_place', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.issue_place'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                                {{-- <div class="form-group col-md-6">
                                    {!! Form::label('status', __('essentials::lang.status') . ':') !!}
                                    {!! Form::select(
                                        'status',
                                        [
                                            'valid' => __('essentials::lang.valid'),
                                            'expired' => __('essentials::lang.expired'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                            'placeholder' => __('essentials::lang.select_status'),
                                      
                                        ],
                                    ) !!}
                                </div> --}}
                                <div class="form-group col-md-6">
                                    {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                                    {!! Form::date('expiration_date', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.expiration_date'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                    {!! Form::file('file', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.file'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="modal fade" id="addDocFileModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeDocFile', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_doc_file')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="modal-body">
                                    <iframe id="iframeDocViewer" width="100%" height="300px" frameborder="0"></iframe>
                                </div>
                            </div>

                            <div class="row">
                                {!! Form::hidden('delete_file', '0', ['id' => 'delete_file_input']) !!}
                                {!! Form::hidden('doc_id', null, ['id' => 'doc_id']) !!}
                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                    {!! Form::file('file', null, [
                                        'class' => 'form-control',
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-danger deleteFile">@lang('messages.delete')</button>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary saveFile" disabled>@lang('messages.save')</button>
                            <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>


    @include('essentials::employee_affairs.official_docs.edit')
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $(document).on('click', '.view_doc_file_modal', function(e) {
                e.preventDefault();

                // Get the data-href attribute containing the URL
                var fileUrl = $(this).data('href') ?? null;
                var doc_id = $(this).data('id');
                $('#doc_id').val(doc_id);
                if (fileUrl != null) {
                    console.log(fileUrl);
                    $('#iframeDocViewer').attr('src', fileUrl);

                    // Show the iframe and hide any other content
                    $('#iframeDocViewer').show();

                } else {
                    // Hide the iframe and show other content
                    $('#iframeDocViewer').hide();

                }


                // Open the modal
                $('#addDocFileModal').modal('show');
            });
            $('#addDocFileModal').on('hidden.bs.modal', function() {
                $('#iframeDocViewer').attr('src', '');
            });
            let fileChanged = false;
            $('.deleteFile').on('click', function() {
                $('#iframeDocViewer').attr('src', ''); // Remove image source
                $('input[type="file"]').val(''); // Clear file input
                $('#delete_file_input').val('1'); // Indicate that the image should be deleted
                $('#iframeDocViewer').hide();
                fileChanged = true;
                enableSaveButton();
            });


            function enableSaveButton() {
                $('.saveFile').prop('disabled', !fileChanged);
            }

            $('input[type="file"]').on('change', function(event) {
                var file = event.target.files[0];

                if (file) {
                    var fileType = file.type;
                    var url = '';

                    // Check file type and create URL accordingly
                    if (fileType.match(/image.*/)) {
                        // If the file is an image
                        url = URL.createObjectURL(file);
                    } else if (fileType === 'application/pdf') {
                        // If the file is a PDF - you might want to use PDF.js here
                        url = URL.createObjectURL(file);
                    } else {
                        // Handle other file types or show an error message
                        alert('File type not supported for preview');
                        return;
                    }

                    // Update the iframe src to show the file
                    $('#iframeDocViewer').attr('src', url).show();
                } else {

                }
                fileChanged = true;
                enableSaveButton();
            });

            $('#addDocModal').on('shown.bs.modal', function(e) {
                $('#employees_select').select2({
                    dropdownParent: $(
                        '#addDocModal'),
                    width: '100%',
                });


            });
            var official_documents_table;

            function reloadDataTable() {
                official_documents_table.ajax.reload();
            }

            official_documents_table = $('#official_documents_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ action([\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'index']) }}",
                    "data": function(d) {
                        if ($('#user_id_filter').val() && $('#user_id_filter').val() != 'all') {
                            d.user_id = $('#user_id_filter').val();
                        }
                        if ($('#user_type_filter').val() && $('#user_type_filter').val() != 'all') {
                            d.user_type = $('#user_type_filter').val();
                        }
                        d.status = $('#status_filter').val();
                        d.doc_type = $('#doc_type_filter').val();
                        if ($('#doc_filter_date_range').val() && $('#doc_filter_date_range').val() !=
                            'all') {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },

                columns: [{
                        data: 'user'
                    },
                    {
                        data: 'number'
                    },
                    {
                        data: 'type',
                        render: function(data, type, row) {
                            if (data === 'national_id') {
                                return '@lang('essentials::lang.national_id')';
                            } else if (data === 'passport') {
                                return '@lang('essentials::lang.passport')';
                            } else if (data === 'residence_permit') {
                                return '@lang('essentials::lang.residence_permit')';
                            } else if (data === 'drivers_license') {
                                return '@lang('essentials::lang.drivers_license')';
                            } else if (data === 'car_registration') {
                                return '@lang('essentials::lang.car_registration')';
                            } else if (data === 'Iban') {
                                return '@lang('essentials::lang.Iban')';
                            } else {
                                return '@lang('essentials::lang.international_certificate')';
                            }
                        }
                    },
                    {
                        data: 'issue_date'
                    },
                    {
                        data: 'issue_place'
                    },

                    {
                        data: 'expiration_date'
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'valid') {
                                return '@lang('essentials::lang.valid')';
                            } else if (data === 'expired') {
                                return '@lang('essentials::lang.expired')';
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'action'
                    },
                ],
            });

            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });

            $(document).on('change', '#user_type_filter, #user_id_filter, #status_filter, #doc_filter_date_range, #doc_type_filter',
                function() {
                    reloadDataTable();
                });

            $(document).on('click', '.open-edit-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var doc_id = $(this).data('id');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var doc = response.doc;
                        console.log(doc);
                        $('#editdocModal').find('[name="status"]').val(doc.status);
                        $('#editdocModal').find('[name="expiration_date"]').val(doc
                            .expiration_date);
                        $('#editdocModal').find('[name="docId"]').val(doc_id);
                        $('#editdocModal').modal('show');
                    },
                    error: function(xhr, status, error) {

                        console.error("Error in AJAX request:", error);
                    }
                });


            })



            $(document).on('click', 'button.delete_doc_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_doc,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    official_documents_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });


        });
    </script>
@endsection
