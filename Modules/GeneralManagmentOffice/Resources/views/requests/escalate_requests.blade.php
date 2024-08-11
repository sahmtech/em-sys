@extends('layouts.app')
@section('title', __('request.allRequests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('request.allRequests')</span>
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
        @include('generalmanagmentoffice::layouts.nav_requests')

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('request.company')</th>
                            <th>@lang('request.request_number')</th>
                            <th>@lang('request.request_owner')</th>
                            <th>@lang('request.eqama_number')</th>
                            <th>@lang('request.request_type')</th>
                            <th>@lang('request.request_date')</th>
                            <th>@lang('request.status')</th>
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('request.view_request')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="workflow-container" id="workflow-container">
                                <!-- Workflow circles will be dynamically added here -->
                            </div>


                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('request.worker_details')</h4>
                                <ul id="worker-list">
                                    <!-- Worker info will be dynamically added here -->
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('request.activites')</h4>
                                <ul id="activities-list">
                                    <!-- Activities will be dynamically added here -->
                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('request.attachments')</h4>
                                <ul id="attachments-list">

                                </ul>
                            </div>
                        </div>
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


        @include('generalmanagmentoffice::requests.change_escalation_status')

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        var langStrings = {
            approved: '{{ __('request.approved') }}',
            rejected: '{{ __('request.rejected') }}'
        };
    </script>
    <script type="text/javascript">
        $(document).ready(function() {



            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('GMO_escalate_requests') }}"
                },

                columns: [{
                        data: 'company_id'
                    },
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
                        data: 'request_type_id',
                        render: function(data, type, row) {

                            const requestTypeMap = {
                                'exitRequest': '@lang('request.exitRequest')',
                                'returnRequest': '@lang('request.returnRequest')',
                                'escapeRequest': '@lang('request.escapeRequest')',
                                'advanceSalary': '@lang('request.advanceSalary')',
                                'leavesAndDepartures': '@lang('request.leavesAndDepartures')',
                                'atmCard': '@lang('request.atmCard')',
                                'residenceRenewal': '@lang('request.residenceRenewal')',
                                'residenceIssue': '@lang('request.residenceIssue')',
                                'workerTransfer': '@lang('request.workerTransfer')',
                                'residenceCard': '@lang('request.residenceCard')',
                                'workInjuriesRequest': '@lang('request.workInjuriesRequest')',
                                'residenceEditRequest': '@lang('request.residenceEditRequest')',
                                'baladyCardRequest': '@lang('request.baladyCardRequest')',
                                'mofaRequest': '@lang('request.mofaRequest')',
                                'insuranceUpgradeRequest': '@lang('request.insuranceUpgradeRequest')',
                                'chamberRequest': '@lang('request.chamberRequest')',
                                'WarningRequest': '@lang('request.WarningRequest')',
                                'cancleContractRequest': '@lang('request.cancleContractRequest')',
                                'passportRenewal': '@lang('request.passportRenewal')',
                                'AjirAsked': '@lang('request.AjirAsked')',
                                'AlternativeWorker': '@lang('request.AlternativeWorker')',
                                'TransferringGuaranteeFromExternalClient': '@lang('request.TransferringGuaranteeFromExternalClient')',
                                'Permit': '@lang('request.Permit')',
                                'FamilyInsurace': '@lang('request.FamilyInsurace')',
                                'Ajir_link': '@lang('request.Ajir_link')',
                                'ticketReservationRequest': '@lang('request.ticketReservationRequest')',
                                'authorizationRequest': '@lang('request.authorizationRequest')',
                                'salaryInquiryRequest': '@lang('request.salaryInquiryRequest')',
                                'interviewsRequest': '@lang('request.interviewsRequest')',
                                'moqimPrint': '@lang('request.moqimPrint')',
                                'salaryIntroLetter': '@lang('request.salaryIntroLetter')',
                                'QiwaContract': '@lang('request.QiwaContract')',
                                'ExitWithoutReturnReport': '@lang('request.ExitWithoutReturnReport')',

                            };

                            return requestTypeMap[data] || data;
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },
                    {

                        render: function(data, type, row) {
                            var buttonsHtml = '';

                            buttonsHtml += `
                            <button class="btn btn-primary btn-sm btn-view-request" data-request-id="${row.id}">
                                @lang('request.view_request')
                            </button>`;

                            return buttonsHtml;
                        }
                    }


                ],
            });

            $(document).on('click', 'a.change_status', function(e) {
                e.preventDefault();

                $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value'))
                    .change();
                $('#change_status_modal').find('#request_id').val($(this).data('request-id'));
                $('#change_status_modal').modal('show');


            });


            $(document).on('submit', 'form#change_status_form', function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector('.update-offer-status'));
                ladda.start();
                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        ladda.stop();
                        if (result.success == true) {
                            $('div#change_status_modal').modal('hide');
                            toastr.success(result.msg);
                            requests_table.ajax.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });



            $(document).on('click', '.btn-view-request', function() {
                var requestId = $(this).data('request-id');

                // var data = requests_table.row(this).data();
                // var requestId = data.id;

                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'
                            .replace(
                                ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            console.log(response);

                            var workflowContainer = $('#workflow-container');
                            var activitiesList = $('#activities-list');
                            var attachmentsList = $('#attachments-list');
                            var workerList = $('#worker-list');

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
                                '{{ __('request.worker_name') }}' + ': ' + response
                                .user_info.worker_full_name + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.nationality') }}' + ': ' + response
                                .user_info.nationality + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.project_name') }}' + ': ' + response
                                .user_info.assigned_to + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.eqama_number') }}' + ': ' + response
                                .user_info.id_proof_number + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.contract_end_date') }}' + ': ' +
                                response.user_info.contract_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.eqama_end_date') }}' + ': ' +
                                response.user_info.eqama_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.passport_number') }}' + ': ' +
                                response.user_info.passport_number + '</p>');



                            //activities

                            // activitiesList.append('<p class="worker-info">' + '{{ __('request.created_by') }}' + ': ' + created_user_info.created_user_full_name + '</p>');    

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
                                    '{{ trans('request.attach') }} ' + (j + 1) + '</a>';

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




        });
    </script>





@endsection
