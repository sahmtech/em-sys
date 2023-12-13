@extends('layouts.app')
@section('title', __('internationalrelations::lang.visa_cards'))

@section('content')
    <style>
        .custom-btn {
            padding: 0.50rem 0.5rem;
        }
    </style>

    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.visa_workers')
        </h1>

    </section>

    <!-- Main content -->
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addWorker">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="table-responsive">

                <table class="table table-bordered table-striped ajax_view hide-footer" id="employees">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>@lang('internationalrelations::lang.worker_name')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.profession')</th>
                            <th>@lang('internationalrelations::lang.passport_number')</th>
                            <th>@lang('internationalrelations::lang.date_of_offer')</th>
                            <th>@lang('internationalrelations::lang.medical_examination')</th>
                            <th>@lang('internationalrelations::lang.fingerprinting')</th>
                            <th>@lang('internationalrelations::lang.passport_stamped')</th>


                        </tr>
                    </thead>
                </table>
                <div style="margin-bottom: 10px;">
                    <button type="button" class="btn btn-success btn-sm custom-btn" id="medical_examination-selected">
                        @lang('internationalrelations::lang.medical_examination')
                    </button>
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="fingerprinting-selected">
                        @lang('internationalrelations::lang.fingerprinting')
                    </button>
                    <button type="button" class="btn btn-primary btn-sm custom-btn" id="worker_visa-selected">
                        @lang('internationalrelations::lang.worker_visa')
                    </button>
                    <button type="button" class="btn btn-danger btn-sm custom-btn" id="passport_stamped-selected">
                        @lang('internationalrelations::lang.passport_stamped')
                    </button>

                </div>

            </div>

            <div class="modal fade" id="uploadFilesModal" tabindex="-1" role="dialog" aria-labelledby="uploadFilesModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadFilesModalLabel">Upload Files for Selected Rows</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form id="uploadFilesForm">

                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />

                                <div id="fileInputsContainer"></div>



                                {{ csrf_field() }}
                            </form>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-primary" id="submitFilesBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="passportModal" tabindex="-1" role="dialog" aria-labelledby="passportModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passportModalLabel">Update Arrival Dates for Selected Rows</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="updateArrivalDatesForm">
                                <input type="hidden" name="selectedRowsData" id="selectedRowsData2" />
                                <div  id="arrivalDatesInputsContainer"></div>
                                {{ csrf_field() }}
                            </form>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-primary" id="submitPassport">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        <div class="modal fade" id="addWorker" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeVisaWorker']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.addWorker')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <input type="hidden" value={{ $visaId }} name=visaId>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('worker_id', __('followup::lang.worker_name') . ':*') !!}
                                    {!! Form::select('worker_id[]', $workersOptions, null, [
                                        'class' => 'form-control select2',
                                        'multiple',
                                        'required',
                                        'style' => 'height: 60px; width: 250px;',
                                    ]) !!}
                                </div>


                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>


    </section>

@stop
@section('javascript')



    <script type="text/javascript">
        $(document).ready(function() {
            var visaId = {{ $visaId }};
            var users_table = $('#employees').DataTable({
                processing: true,
                serverSide: true,
                info: false,

                ajax: {
                    url: "{{ route('viewVisaWorkers', ['id' => $visaId]) }}",
                    type: 'GET',
                },


                "columns": [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id +
                                '" data-full_name="' + row.full_name +
                                '">';
                        },
                        orderable: false,
                        searchable: false,
                    },

                    {
                        "data": "full_name"
                    },
                    {
                        "data": "agency_id"
                    },

                    {
                        "data": "nationality_id"
                    },

                    {
                        "data": "profession_id",

                    },
                    {
                        "data": "passport_number",

                    },
                    {
                        "data": "date_of_offer",

                    },
                    {
                        "data": "medical_examination",

                    },
                    {
                        "data": "fingerprinting",

                    },

                    {
                        "data": "is_passport_stamped"
                    }
                ],

            });

            $('#specializations-select, #professions-select, #agency_filter').change(
                function() {
                    users_table.ajax.reload();

                });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#employees').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === users_table.rows()
                    .count());
            });

            $('#medical_examination-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return $(this).data('id');
                }).get(); // Convert the jQuery object to a regular array

                $.ajax({
                    type: 'POST',
                    url: '{{ route('medical_examination') }}',
                    data: {
                        selectedRows: selectedRows,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });
            });

            $('#fingerprinting-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return $(this).data('id');
                }).get();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('fingerprinting') }}',
                    data: {
                        selectedRows: selectedRows,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });
            });


            $('#worker_visa-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                        full_name: $(this).data('full_name'),

                    };
                }).get();


                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                var modalTitle = '{{ __('internationalrelations::lang.worker_visa') }}';


                $('#uploadFilesModalLabel').text(modalTitle);

                $('#fileInputsContainer').empty();
                var selectFileLabel = '{{ __('sales::lang.uploade_file_for') }}';

                selectedRows.forEach(function(row) {

                    var fileInputHtml = '<div class="form-group">' +
                        '<label for="fileInput' + row.id + '">' + selectFileLabel + ' ' + row
                        .full_name +
                        '</label>' +
                        '<input type="file" class="form-control file-input" name="files[' + row
                        .id +
                        '][]" id="fileInput' + row.id + '" multiple />' +
                        '</div>';
                    $('#fileInputsContainer').append(fileInputHtml);

                });

                $('#uploadFilesModal').modal('show');
            });

            $('#submitFilesBtn').click(function() {
                var formData = new FormData($('#uploadFilesForm')[0]);


                $.ajax({
                    type: 'POST',
                    url: '{{ route('add_worker_visa') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });


                $('#uploadFilesModal').modal('hide');
            });

            $('#passport_stamped-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                        full_name: $(this).data('full_name'),
                   
                    };
                }).get();

                $('#selectedRowsData2').val(JSON.stringify(selectedRows));

                var modalTitle = '{{ __('internationalrelations::lang.passport_stamped') }}';
                $('#passportModalLabel').text(modalTitle);
                var selectFileLabel = '{{ __('internationalrelations::lang.arrival_date') }}';
                $('#arrivalDatesInputsContainer').empty();

                selectedRows.forEach(function(row) {
                    var arrivalDateInput = '<div class="form-group">' +
                        '<label for="arrivalDateInput' + row.id + '">' + selectFileLabel + ' ' + row
                        .full_name + '</label>' +
                        '<input type="date" class="form-control" name="arrival_dates[' + row.id +
                        ']" id="arrivalDateInput' + row.id + '" value="' + row.arrival_date +
                        '" />' +
                        '</div>';
                    $('#arrivalDatesInputsContainer').append(arrivalDateInput);
                });

                $('#passportModal').modal('show');
            });

            $('#submitPassport').click(function() {
                var formData = new FormData($('#updateArrivalDatesForm')[0]);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('passport_stamped') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {
                        // Handle error
                    }
                });

                $('#passportModal').modal('hide');
            });


        });
    </script>



@endsection
