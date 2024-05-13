@extends('layouts.app')
@section('title', __('request.travel_categories_requests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('request.travel_categories_requests')</span>
        </h1>
    </section>

    <head>
        <style>
            .alert {
                animation: fadeOut 5s forwards;
                max-width: 300px;
                margin: 0 auto;
            }

            @keyframes fadeOut {
                to {
                    opacity: 0;
                    visibility: hidden;
                }
            }

            .workflow-circle {
                min-width: 110px;
                height: 110px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
                color: #fff;
                padding: 10px;

            }

            .workflow-arrow {
                position: relative;
                display: inline-block;
                width: 0;
                height: 0;
                margin: 0 10px;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;


            }

            .workflow-circle span {
                margin-top: 5px;

            }

            .workflow-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                white-space: nowrap;
                overflow-x: auto;
                margin-bottom: 20px;
            }

            .workflow-circle.pending {
                background-color: orange;
            }

            .workflow-circle.approved {
                background-color: green;
            }

            .workflow-circle.rejected {
                background-color: red;
            }

            .workflow-circle.grey {
                background-color: grey;
            }

            .pending-arrow,
            .approved-arrow,
            .rejected-arrow,
            .grey-arrow {
                color: #000;
            }

            .department-name {
                text-align: center;
                margin-top: 5px;
                font-weight: bold;
            }
        </style>
    </head>
    <!-- Main content -->
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @else
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    @endif
    <section class="content">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('request.request_number')</th>
                            <th>@lang('request.request_owner')</th>
                            <th>@lang('request.eqama_number')</th>
                            <th>@lang('request.request_type')</th>
                            <th>@lang('request.travel_categorie')</th>
                            <th>@lang('request.request_date')</th>
                            <th>@lang('request.exit_date')</th>
                            <th>@lang('request.return_date')</th>
                            <th>@lang('request.note')</th>
                            <th>@lang('request.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent


        {{-- view request --}}
        <div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('request.view_request')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="workflow-container" id="workflow-container">

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('request.request_owner')</h4>
                                <ul id="worker-list">

                                </ul>
                                <h4>@lang('request.attachments')</h4>
                                <ul id="attachments-list">

                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('request.activites')</h4>
                                <ul id="activities-list">

                                </ul>
                            </div>

                        </div>

                        <!-- Attachment Form -->
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="attachment">
                                    <h4>@lang('request.add_attachment')</h4>
                                </label>
                                <input type="file" class="form-control" style="width: 250px;" id="attachment"
                                    name="attachment">
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        </form>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="bookVisaModal" tabindex="-1" role="dialog" aria-labelledby="bookVisaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('book_visa_from_request') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('request.book_visa')</h4>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" id="requestId" name="requestId">
                            <div class="form-group col-md-6">
                                <label for="airplaneCompany">@lang('internationalrelations::lang.Airlines')</label>
                                <select class="form-control" style="height: 40px;" id="airplaneCompany"
                                    name="airplaneCompany" required>

                                    <option value="">@lang('internationalrelations::lang.select_company')</option>
                                    @foreach ($airplane_Companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->supplier_business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="tripDateTime">@lang('internationalrelations::lang.data and time of trip')</label>
                                <input type="datetime-local" class="form-control" id="tripDateTime" name="tripDateTime"
                                    required>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Visa Data Modal -->
        <div class="modal fade" id="viewVisaDataModal" tabindex="-1" role="dialog"
            aria-labelledby="viewVisaDataModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            <h4 class="modal-title">@lang('request.view_visa_data')</h4>
                        </button> --}}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('request.view_visa_data')</h4>
                    </div>
                    <div class="modal-body">
                        <!-- Placeholders for company name, trip date, and trip time -->
                        <p>@lang('internationalrelations::lang.Airline company') : <span id="companyName"></span></p>
                        <p>@lang('internationalrelations::lang.Date of Trip') : <span id="tripDate"></span></p>
                        <p>@lang('internationalrelations::lang.Time of Trip') : <span id="tripTime"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>




    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#requestType').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });
            });


            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('travel_categories') }}"
                },

                columns: [

                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },

                    {
                        data: 'request_type',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('request.exitRequest')';
                            } else if (data === 'returnRequest') {
                                return '@lang('request.returnRequest')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'categorie'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'has_travel_categorie',
                        render: function(data, type, row) {
                            var buttonsHtml = '';


                            buttonsHtml +=
                                '@if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.show_ir_request'))<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' +
                                row.id +
                                '">@lang('request.view_request')</button>@endif&nbsp;';

                            if (data != '1') {
                                buttonsHtml +=
                                    '&nbsp;@if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.book_visa'))<button class="btn btn-success btn-sm btn-book-visa" data-request-id="' +
                                    row.id +
                                    '">@lang('request.book_visa')</button>@endif';
                            }
                            if (data != '0') {
                                buttonsHtml +=
                                    '&nbsp;@if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.view_visa_data'))<button class="btn btn-warning btn-sm btn-view-visa-data" data-request-id="' +
                                    row.id +
                                    '">@lang('request.view_visa_data')</button>@endif';
                            }

                            return buttonsHtml;
                        }
                    },



                ],
            });


            $(document).on('click', '.btn-view-request', function() {
                var requestId = $(this).data('request-id');


                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'
                            .replace(
                                ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {

                            var workflowContainer = $('#workflow-container');
                            var activitiesList = $('#activities-list');
                            var workerList = $('#worker-list');
                            var attachmentsList = $('#attachments-list');

                            workflowContainer.html('');
                            workerList.html('');
                            activitiesList.html('');
                            attachmentsList.html('');

                            for (var i = 0; i < response.workflow.length; i++) {

                                var status = response.workflow[i].status ? response
                                    .workflow[i]
                                    .status.toLowerCase() : 'grey';
                                var circle = '<div class="workflow-circle ' + status + '">';
                                circle += '<p class="department-name">' + response.workflow[
                                        i]
                                    .department + '</p>';
                                circle += '</div>';

                                workflowContainer.append(circle);


                                if (i < response.workflow.length - 1) {
                                    workflowContainer.append(
                                        '<i class="fas fa-arrow-left workflow-arrow ' +
                                        status + '-arrow"></i>');
                                }
                            }

                            //  worker info
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.name') }}' + ': ' + response
                                .user_info.worker_full_name + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.nationality') }}' + ': ' + response
                                .user_info.nationality + '</p>');
                            if (response.user_info.assigned_to) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('request.project_name') }}' + ': ' +
                                    response
                                    .user_info.assigned_to + '</p>');
                            }
                            if (response.user_info.id_proof_number) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('request.eqama_number') }}' + ': ' +
                                    response
                                    .user_info.id_proof_number + '</p>');
                            }
                            if (response.user_info.contract_end_date) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('request.contract_end_date') }}' +
                                    ': ' +
                                    response.user_info.contract_end_date + '</p>');
                            }
                            if (response.user_info.eqama_end_date) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('request.eqama_end_date') }}' + ': ' +
                                    response.user_info.eqama_end_date + '</p>');
                            }
                            if (response.user_info.passport_number) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('request.passport_number') }}' + ': ' +
                                    response.user_info.passport_number + '</p>');
                            }


                            //activities

                            for (var j = 0; j < response.followup_processes.length; j++) {
                                var activity = '<li>';

                                activity += '<p>' +
                                    '{{ __('request.created_department_name') }}' +
                                    ': ' +
                                    response.request_info.started_depatment.name + '</p>';
                                activity += '<p>' +
                                    '{{ __('request.created_user_name') }}' +
                                    ': ' +
                                    response.created_user_info.created_user_full_name + '</p>';
                                activity += '<p>' +
                                    '{{ __('request.department_name') }}' + ': ' +
                                    response.followup_processes[j].department.name;

                                activity +=
                                    '<p class="{{ __('request.status') }} ' +
                                    response.followup_processes[j].status.toLowerCase() +
                                    '">' +
                                    '<strong>{{ __('request.status') }}:</strong> ' +
                                    response.followup_processes[j].status + '</p>';

                                activity += '<p>' + '{{ __('request.reason') }}' +
                                    ': ';
                                if (response.followup_processes[j].reason) {
                                    activity += '<strong>' + response.followup_processes[j]
                                        .reason + '</strong>';
                                } else {
                                    activity += '{{ __('request.not_exist') }}';
                                }
                                activity += '<p>' + '{{ __('request.note') }}' +
                                    ': ';
                                if (response.followup_processes[j].status_note) {
                                    activity += '<strong>' + response.followup_processes[j]
                                        .status_note + '</strong>';
                                } else {
                                    activity += '{{ __('request.not_exist') }}';
                                }
                                activity += '</p>';
                                activity += '<p style="color: green;">' +
                                    '{{ __('request.updated_by') }}' + ': ' + (
                                        response.followup_processes[j].updated_by ||
                                        '{{ __('request.not_exist') }}') + '</p>';


                                activity += '</li>';
                                activitiesList.append(activity);
                            }
                            for (var j = 0; j < response.attachments.length; j++) {
                                var attachment = '<li>';

                                attachment += '<p>';


                                attachment += '<a href="{{ url('uploads') }}/' + response
                                    .attachments[j].file_path +
                                    '" target="_blank" onclick="openAttachment(\'' +
                                    response
                                    .attachments[j].file_path + '\', ' + (j + 1) + ')">' +
                                    '{{ trans('request.attach') }} ' + (j + 1) +
                                    '</a>';

                                attachment += '</p>';
                                attachment += '</li>';

                                attachmentsList.append(attachment);
                            }
                            $('#attachmentForm').attr('action',
                                '{{ route('saveAttachment', ['requestId' => ':requestId']) }}'
                                .replace(':requestId', response.request_info.id));

                            $('#attachmentForm input[name="requestId"]').val(requestId);


                            $('#requestModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });



            $(document).on('click', '.btn-book-visa', function() {
                var requestId = $(this).data('request-id');
                $('#requestId').val(requestId);
                $('#bookVisaModal').modal('show');
            });



            $('#bookVisaModal form').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if (response.success == true) {
                            toastr.success(response.msg);
                            requests_table.ajax.reload();
                            $('#bookVisaModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                        }
                    }
                });
            });

            $(document).on('click', '.btn-view-visa-data', function() {
                var requestId = $(this).data('request-id');


                if (requestId) {
                    $.ajax({
                        url: '{{ route('getVisaData', ['requestId' => ':requestId']) }}'
                            .replace(
                                ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            var tripInfo = response.tripInfo;


                            $('#companyName').text(tripInfo.company ? tripInfo.company
                                .supplier_business_name :
                                'N/A'
                            );
                            $('#tripDate').text(tripInfo.tripDate || 'N/A');
                            $('#tripTime').text(tripInfo.tripTime || 'N/A');


                            $('#viewVisaDataModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                            alert('Error fetching data. Please try again.');
                        }
                    });
                }
            });
        });
    </script>


@endsection
