@extends('layouts.app')
@section('title', __('essentials::lang.contract_cancel_requests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.contract_cancel_requests')</span>
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
                width: 110px;
                height: 110px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
                color: #fff;

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
                            <th>@lang('essentials::lang.request_number')</th>
                            <th>@lang('essentials::lang.name')</th>
                            <th>@lang('essentials::lang.eqama_number')</th>
                            <th>@lang('essentials::lang.end_work_date')</th>
                            <th>@lang('essentials::lang.request_date')</th>
                            <th>@lang('essentials::lang.main_reason')</th>
                            <th>@lang('essentials::lang.sub_reason')</th>
                            <th>@lang('essentials::lang.note')</th>
                            <th>@lang('essentials::lang.action')</th>


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
                        <h4 class="modal-title">@lang('essentials::lang.view_request')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="workflow-container" id="workflow-container">

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('essentials::lang.request_owner')</h4>
                                <ul id="worker-list">

                                </ul>
                                <h4>@lang('essentials::lang.attachments')</h4>
                                <ul id="attachments-list">

                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('essentials::lang.activites')</h4>
                                <ul id="activities-list">

                                </ul>
                            </div>

                        </div>

                        <!-- Attachment Form -->
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="attachment">
                                    <h4>@lang('essentials::lang.add_attachment')</h4>
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

        {{-- <div class="modal fade" id="finish_procedure_model" tabindex="-1" role="dialog" aria-labelledby="finish_procedure_modelLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('finish_contract_procedure') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="finish_procedure_modelLabel">@lang('essentials::lang.finish_procedure')</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="request_id" id="request-id">
                            <div class="form-group">
                                <label for="file">Upload File</label>
                                <input type="file" class="form-control-file"  name="file" >
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}




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
                    url: "{{ route('cancel_contract_requests') }}"
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
                        data: 'contract_end_date'
                    },

                    {
                        data: 'created_at'
                    },
                    {
                        data: 'main_reason'
                    },
                    {
                        data: 'sub_reason'
                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'userStatus',
                        render: function(data, type, row) {
                            var buttonsHtml = '';


                            buttonsHtml +=
                                '@if (auth()->user()->hasRole('Admin#1') ||auth()->user()->can('essentials.show_essentials_request'))<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' +
                                row.id +
                                '">@lang('essentials::lang.view_request')</button>@endif';

                            if(data != 'inactive'){
                            buttonsHtml +=
                                '@if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.cancel_contract'))<button class="btn btn-primary btn-sm btn-finish-procedure" data-request-id="' +
                                row.id +
                                '">@lang('essentials::lang.finish_procedure')</button>@endif';
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
                        url: '{{ route('viewHrRequest', ['requestId' => ':requestId']) }}'.replace(
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

                                var status = response.workflow[i].status ? response.workflow[i]
                                    .status.toLowerCase() : 'grey';
                                var circle = '<div class="workflow-circle ' + status + '">';
                                circle += '<p class="department-name">' + response.workflow[i]
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
                                '{{ __('essentials::lang.name') }}' + ': ' + response
                                .user_info.worker_full_name + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('essentials::lang.nationality') }}' + ': ' + response
                                .user_info.nationality + '</p>');
                            if (response.user_info.assigned_to) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('essentials::lang.project_name') }}' + ': ' +
                                    response
                                    .user_info.assigned_to + '</p>');
                            }
                            if (response.user_info.id_proof_number) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('essentials::lang.eqama_number') }}' + ': ' +
                                    response
                                    .user_info.id_proof_number + '</p>');
                            }
                            if (response.user_info.contract_end_date) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('essentials::lang.contract_end_date') }}' +
                                    ': ' +
                                    response.user_info.contract_end_date + '</p>');
                            }
                            if (response.user_info.eqama_end_date) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('essentials::lang.eqama_end_date') }}' + ': ' +
                                    response.user_info.eqama_end_date + '</p>');
                            }
                            if (response.user_info.passport_number) {
                                workerList.append('<p class="worker-info">' +
                                    '{{ __('essentials::lang.passport_number') }}' + ': ' +
                                    response.user_info.passport_number + '</p>');
                            }


                            //activities

                            for (var j = 0; j < response.followup_processes.length; j++) {
                                var activity = '<li>';

                                if (j === 0) {
                                    activity += '<p>' +
                                        '{{ __('essentials::lang.created_department_name') }}' + ': ' +
                                        response.followup_processes[j].department.name + '</p>';
                                } else {
                                 
                                    activity += '<p>' +
                                        '{{ __('essentials::lang.department_name') }}' + ': ' +
                                        response.followup_processes[j].department.name;

                                    activity +=
                                        '<p class="{{ __('essentials::lang.status') }} ' +
                                        response.followup_processes[j].status.toLowerCase() +
                                        '">' +
                                        '<strong>{{ __('essentials::lang.status') }}:</strong> ' +
                                        response.followup_processes[j].status + '</p>';

                                    activity += '<p>' + '{{ __('essentials::lang.reason') }}' +
                                        ': ';
                                    if (response.followup_processes[j].reason) {
                                        activity += '<strong>' + response.followup_processes[j]
                                            .reason + '</strong>';
                                    } else {
                                        activity += '{{ __('essentials::lang.not_exist') }}';
                                    }
                                    activity += '<p>' + '{{ __('essentials::lang.note') }}' +
                                        ': ';
                                    if (response.followup_processes[j].status_note) {
                                        activity += '<strong>' + response.followup_processes[j]
                                            .status_note + '</strong>';
                                    } else {
                                        activity += '{{ __('essentials::lang.not_exist') }}';
                                    }
                                    activity += '</p>';
                                    activity += '<p style="color: green;">' +
                                        '{{ __('essentials::lang.updated_by') }}' + ': ' + (
                                            response.followup_processes[j].updated_by ||
                                            '{{ __('essentials::lang.not_exist') }}') + '</p>';
                                }

                                activity += '</li>';
                                activitiesList.append(activity);
                            }
                            for (var j = 0; j < response.attachments.length; j++) {
                                var attachment = '<li>';

                                attachment += '<p>';


                                attachment += '<a href="{{ url('uploads') }}/' + response
                                    .attachments[j].file_path +
                                    '" target="_blank" onclick="openAttachment(\'' + response
                                    .attachments[j].file_path + '\', ' + (j + 1) + ')">' +
                                    '{{ trans('essentials::lang.attach') }} ' + (j + 1) +
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


            $(document).on('click', '.btn-finish-procedure', function() {
                var requestId = $(this).data('request-id');


                if (requestId) {
                    $.ajax({
                        url: '{{ route('finish_contract_procedure', ['requestId' => ':requestId']) }}'.replace(
                            ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            console.log(response);
                            if (response.success == true) {
                                toastr.success(response.msg);
                                requests_table.ajax.reload();
                            } else {
                                toastr.error(response.msg);
                            }
                        }
                    })
                }
            });
      
        });
    
    </script>


@endsection