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

            .modal-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }

            .modal-title {
                font-weight: bold;
                color: #495057;
            }

            .modal-body {
                background-color: #ffffff;
                color: #495057;
            }

            .request-details,
            .activity {
                border: 1px solid #dee2e6;
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 4px;
            }

            .request-details strong,
            .activity strong {
                color: #007bff;
            }

            .modal-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }

            .modal-title {
                font-weight: bold;
                color: #495057;
            }

            .modal-body {
                background-color: #ffffff;
                color: #495057;
            }

            .card {
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
                border-radius: 4px;
                width: 90%;

            }

            .card-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                padding: 10px;
                font-weight: bold;
                color: #495057;
            }

            .card-body {
                padding: 10px;
            }

            .card-body p {
                margin: 5px 0;
            }

            .arrow-down {
                width: 0;
                height: 0;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;
                border-top: 10px solid #dee2e6;
                margin: 0 auto;
            }

            .modal-header .close {
                color: #007bff;
                opacity: 1;
            }

            .modal-header .close:hover,
            .modal-header .close:focus {
                color: #0056b3;
                text-decoration: none;
                opacity: 1;
            }

            .modal-footer .btn-secondary {
                background-color: #007bff;
                border-color: #007bff;
                color: #fff;
            }

            .modal-footer .btn-secondary:hover,
            .modal-footer .btn-secondary:focus {
                background-color: #0056b3;
                border-color: #0056b3;
                color: #fff;
            }

            .modal-header .close {
                color: #007bff;
                opacity: 1;
            }

            .modal-header .close:hover,
            .modal-header .close:focus {
                color: #0056b3;
                text-decoration: none;
                opacity: 1;
            }

            .modal-footer .btn-secondary {
                background-color: #007bff;
                border-color: #007bff;
                color: #fff;
            }

            .modal-footer .btn-secondary:hover,
            .modal-footer .btn-secondary:focus {
                background-color: #0056b3;
                border-color: #0056b3;
                color: #fff;
            }

            .card {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .card-header {
                background-color: #f7f7f7;
                border-bottom: 1px solid #dee2e6;
                font-weight: bold;
            }

            .card-body {
                padding: 1rem;
            }

            .card-footer {
                background-color: #f7f7f7;
                border-top: 1px solid #dee2e6;
                text-align: right;
            }

            .workflow-rectangle {
                min-width: 150px;
                height: 100px;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
                color: #fff;
                padding: 10px;
                text-align: center;
                margin-bottom: 10px;
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

            .workflow-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                white-space: nowrap;
                overflow-x: auto;
            }

            .workflow-rectangle.pending {
                background-color: orange;
            }

            .workflow-rectangle.approved {
                background-color: green;
            }

            .workflow-rectangle.rejected {
                background-color: red;
            }

            .workflow-rectangle.grey {
                background-color: grey;
            }

            .pending-arrow,
            .approved-arrow,
            .rejected-arrow,
            .grey-arrow {
                color: #000;
            }

            .department-name {
                margin-top: 5px;
                font-weight: bold;
            }

            .updated-by {
                font-size: 12px;
                margin-top: 5px;
            }

            .workflow-rectangle.green {
                background-color: #4CAF50;
            }

            .attachment-item {
                margin-bottom: 10px;
            }

            .attachment-link {
                color: #007bff;
                text-decoration: none;
            }

            .attachment-link:hover {
                text-decoration: underline;
            }

            #attachmentForm .attachment-group {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            #attachmentForm .form-control {
                width: 100%;
                max-width: 150px;
                margin-right: 10px;
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
        @component('components.filters', ['title' => __('request.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('request.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        collect($all_status)->mapWithKeys(fn($status) => [$status => trans("request.$status")]),
                        null,
                        [
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'status_filter',
                        ],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="type_filter">@lang('request.request_type'):</label>
                    {!! Form::select(
                        'type_filter',
                        collect($allRequestTypes)->mapWithKeys(fn($type) => [$type => trans("request.$type")]),
                        null,
                        [
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'type_filter',
                        ],
                    ) !!}
                </div>
            </div>
        @endcomponent
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
                            <th>@lang('request.created_by')</th>
                            <th>@lang('request.status')</th>
                            <th>@lang('request.note')</th>
                            <th>@lang('request.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent




        {{-- return request --}}
        <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnModalLabel">@lang('request.return_the_request')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="returnModalForm">
                            <div class="form-group">
                                <label for="reasonInput">@lang('request.reason')</label>
                                <input type="text" class="form-control" id="reasonInput" required>
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('request.update')</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('request.close')</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- view request details --}}

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
                            <div class="workflow-container" id="workflow-container"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('request.request_owner')</h4>
                                <ul id="worker-list"></ul>
                                <h4>@lang('request.attachments')</h4>
                                <ul id="attachments-list"></ul>
                            </div>
                            <div class="col-md-6">
                                <h4>@lang('request.request_info')</h4>
                                <ul id="request-info"></ul>
                            </div>
                        </div>
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div id="attachmentContainer"></div>
                            <button type="button" class="btn btn-primary" id="addAttachment">@lang('request.add_attachment')</button>
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- view request activities --}}
        <div class="modal fade" id="activitiesModal" tabindex="-1" role="dialog" aria-labelledby="activitiesModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="activitiesModalLabel">@lang('request.activities')</h4>

                    </div>
                    <div class="modal-body">
                        <!-- Activities will be injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>

        @include('request.change_request_status')

    </section>
    <!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('GMO_president_requests') }}",
                    data: function(d) {
                        d.status = $('#status_filter').val();
                        d.type = $('#type_filter').val();
                    }
                },
                columns: [{
                        data: 'company_id'
                    }, {
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
                            // Custom render logic based on request type
                            const requestTypeMap = {
                                'exitRequest': '@lang('request.exitRequest')',
                                'returnRequest': '@lang('request.returnRequest')',
                                'escapeRequest': '@lang('request.escapeRequest')',
                                'advanceSalary': '@lang('request.advanceSalary')',
                                'leavesAndDepartures': '@lang('request.leavesAndDepartures')',
                                'atmCard': '@lang('request.atmCard')',
                                'residenceRenewal': '@lang('request.residenceRenewal')',
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
                        data: 'created_user'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'note'
                    },
                    {
                        data: 'can_return',

                    }


                ]
            });
            $('#status_filter, #type_filter').change(function() {
                requests_table.ajax.reload();
            });

            $(document).on('click', '.btn-view-activities', function() {
                var requestId = $(this).data('request-id');
                viewRequestActivities(requestId);
            });

            function viewRequestActivities(requestId) {
                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'.replace(
                            ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {

                            $('#activitiesModal .modal-body').html(renderRequestActivities(response));
                            $('#activitiesModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            }

            function renderRequestActivities(data) {
                let activitiesHtml = `
                      <div class="activity-header card mb-3">
                      
                          <div class="card-body">
                              <p><strong>@lang('request.started_department'):</strong> ${data.request_info.started_depatment.name || '@lang('request.not_exist')'}</p>
                              <p><strong>@lang('request.created_by'):</strong> ${data.created_user_info.created_user_full_name || '@lang('request.not_exist')'}</p>
                          </div>
                      </div>
                  `;

                activitiesHtml += data.followup_processes.reverse().map((process, index) => `
                      <div class="activity card mb-3">
                          <div class="card-body">
                              <p><strong>@lang('request.department'):</strong> ${process.department.name || '@lang('request.not_exist')'}</p>
                              <p><strong>@lang('request.status'):</strong> ${process.status || '@lang('request.not_exist')'}</p>
                              <p><strong>@lang('request.updated_by'):</strong> ${process.updated_by || '@lang('request.not_exist')'}</p>
                          
                              <p><strong>@lang('request.status_note'):</strong> ${process.status_note || '@lang('request.not_exist')'}</p>
                          </div>
                          ${index < data.followup_processes.length - 1 ? '<div class="arrow-down"></div>' : ''}
                      </div>
                  `).join('');

                return activitiesHtml;
            }
        });

        $(document).ready(function() {


            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#requestType').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });
            });

            $(document).on('click', 'a.change_status', function(e) {
                e.preventDefault();

                $('#change_status_modal').find('select#status_dropdown').val($(this)
                        .data('orig-value'))
                    .change();
                $('#change_status_modal').find('#request_id').val($(this).data(
                    'request-id'));
                $('#change_status_modal').modal('show');


            });


            $(document).on('submit', 'form#change_status_form', function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector(
                    '.update-offer-status'));
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
            $('#requests_table').on('click', '.btn-return', function() {
                var requestId = $(this).data('request-id');
                $('#returnModal').modal('show');
                $('#returnModal').data('id', requestId);
            });


            $('#returnModalForm').submit(function(e) {
                e.preventDefault();

                var requestId = $('#returnModal').data('id');
                var reason = $('#reasonInput').val();

                $.ajax({
                    url: "{{ route('returnRequest') }}",
                    method: "POST",
                    data: {
                        requestId: requestId,
                        reason: reason
                    },
                    success: function(result) {

                        if (result.success == true) {
                            $('#returnModal').modal('hide');
                            toastr.success(result.msg);
                            requests_table.ajax.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            $(document).on('click', '.btn-view-request-details', function() {
                var requestId = $(this).data('request-id');

                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'
                            .replace(':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            console.log(response);
                            var workflowContainer = $('#workflow-container');
                            var workerList = $('#worker-list');
                            var attachmentsList = $('#attachments-list');
                            var requestInfoList = $('#request-info');
                            workflowContainer.html('');
                            workerList.html('');
                            attachmentsList.html('');
                            requestInfoList.html('');

                            response.workflow.forEach(function(step, i) {
                                var status = step.status ? step.status.toLowerCase() :
                                    'grey';
                                if (step.process_id != null) {
                                    console.log(step.process_id);

                                    var updatedBy = response.followup_processes.find(
                                            process => process.id === step
                                            .process_id)?.updated_by ||
                                        '{{ __('request.not_exist') }}';
                                } else {
                                    var updatedBy = '{{ __('request.not_exist') }}';
                                }
                                var rectangle = `
                                          <div class="workflow-rectangle ${status}">
                                              <p class="department-name">${step.department}</p>
                                          <p class="updated-by">@lang('request.updated_by'): ${updatedBy}</p>
                                          </div>
                                      `;
                                workflowContainer.append(rectangle);

                                if (i < response.workflow.length - 1) {
                                    workflowContainer.append(
                                        `<i class="fas fa-arrow-left workflow-arrow ${status}-arrow"></i>`
                                    );
                                }
                            });

                            workerList.append(
                                `<p class="worker-info">{{ __('request.name') }}: ${response.user_info.worker_full_name}</p>`
                            );
                            workerList.append(
                                `<p class="worker-info">{{ __('request.nationality') }}: ${response.user_info.nationality}</p>`
                            );
                            if (response.user_info.company) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.company') }}: ${response.user_info.company}</p>`
                                );
                            }
                            if (response.user_info.assigned_to) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.project_name') }}: ${response.user_info.assigned_to}</p>`
                                );
                            }
                            if (response.user_info.id_proof_number) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.eqama_number') }}: ${response.user_info.id_proof_number}</p>`
                                );
                            }
                            if (response.user_info.contract_end_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.contract_end_date') }}: ${response.user_info.contract_end_date}</p>`
                                );
                            }
                            if (response.user_info.eqama_end_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.eqama_end_date') }}: ${response.user_info.eqama_end_date}</p>`
                                );
                            }
                            if (response.user_info.passport_number) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.passport_number') }}: ${response.user_info.passport_number}</p>`
                                );
                            }
                            if (response.user_info.admission_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.admission_date') }}: ${response.user_info.admission_date}</p>`
                                );
                            }
                            response.attachments.forEach(function(attachment, j) {
                                attachmentsList.append(`
                                  <li class="attachment-item">
                                      <a href="{{ url('uploads') }}/${attachment.file_path}" target="_blank" class="attachment-link">
                                          ${attachment.name || '@lang('request.attach') ' + (j + 1)}
                                      </a>
                                  </li>
                              `);
                            });
                            // Populate request info list
                            var requestInfo = response.request_info;
                            var requestInfoData = [{
                                    label: '{{ __('request.type') }}',
                                    value: requestInfo.type
                                },
                                {
                                    label: '{{ __('request.request_no') }}',
                                    value: requestInfo.request_no
                                },
                                {
                                    label: '{{ __('request.exit_date') }}',
                                    value: requestInfo.start_date
                                },
                                {
                                    label: '{{ __('request.end_date') }}',
                                    value: requestInfo.end_date
                                },
                                {
                                    label: '{{ __('request.escape_time') }}',
                                    value: requestInfo.escape_time
                                },
                                {
                                    label: '{{ __('request.advSalaryAmount') }}',
                                    value: requestInfo.advSalaryAmount
                                },
                                {
                                    label: '{{ __('request.monthlyInstallment') }}',
                                    value: requestInfo.monthlyInstallment
                                },
                                {
                                    label: '{{ __('request.installmentsNumber') }}',
                                    value: requestInfo.installmentsNumber
                                },
                                {
                                    label: '{{ __('request.baladyCardType') }}',
                                    value: requestInfo.baladyCardType
                                },
                                {
                                    label: '{{ __('request.workInjuriesDate') }}',
                                    value: requestInfo.workInjuriesDate
                                },
                                {
                                    label: '{{ __('request.resCardEditType') }}',
                                    value: requestInfo.resCardEditType
                                },
                                {
                                    label: '{{ __('request.main_reason') }}',
                                    value: requestInfo.contract_main_reason_id
                                },
                                {
                                    label: '{{ __('request.sub_reason') }}',
                                    value: requestInfo.contract_sub_reason_id
                                },
                                {
                                    label: '{{ __('request.visa_number') }}',
                                    value: requestInfo.visa_number
                                },
                                {
                                    label: '{{ __('request.atmCardType') }}',
                                    value: requestInfo.atmCardType
                                },
                                {
                                    label: '{{ __('request.insurance_class') }}',
                                    value: requestInfo.insurance_classes_id
                                },
                                {
                                    label: '{{ __('request.status') }}',
                                    value: requestInfo.status
                                },

                                {
                                    label: '{{ __('request.started_depatment') }}',
                                    value: requestInfo.started_depatment.name
                                },
                                {
                                    label: '{{ __('request.created_at') }}',
                                    value: requestInfo.created_at
                                },
                                {
                                    label: '{{ __('request.updated_at') }}',
                                    value: requestInfo.updated_at
                                }
                            ];

                            requestInfoData.forEach(function(info) {
                                if (info.value !== null && info.value !==
                                    '') { // Check for null or empty values
                                    requestInfoList.append(
                                        `<li class="request-info-item">${info.label}: ${info.value}</li>`
                                    );
                                }
                            });
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
            $('#addAttachment').click(function() {
                var attachmentIndex = $('.attachment-group').length;
                var attachmentGroup = `
              <div class="attachment-group" style="margin-bottom: 10px;">
                  <input type="file" class="form-control attachment-input" name="attachments[${attachmentIndex}][file]" style="width: 150px; display: inline-block; margin-right: 10px;">
                  <input type="text" class="form-control attachment-name" name="attachments[${attachmentIndex}][name]" placeholder="@lang('request.attachment_name')" style="width: 150px; display: inline-block; margin-right: 10px;">
                  <button type="button" class="btn btn-danger remove-attachment">@lang('request.remove')</button>
              </div>
                   `;
                $('#attachmentContainer').append(attachmentGroup);
            });

            $(document).on('click', '.remove-attachment', function() {
                $(this).closest('.attachment-group').remove();
            });

            $('#attachmentForm').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);


                $('.attachment-group').each(function(index, element) {
                    var fileInput = $(element).find('input[type="file"]')[0];
                    var nameInput = $(element).find('input[type="text"]').val();
                    if (fileInput.files[0]) {
                        formData.append(`attachments[${index}][file]`, fileInput.files[0]);
                        formData.append(`attachments[${index}][name]`, nameInput);
                    }
                });

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            console.log(response);
                            toastr.success(response.msg);
                            $('#attachmentForm')[0].reset();
                            $('#attachmentContainer').html('');
                            $('#requestModal').modal('hide');
                            //  $('#requests_table').DataTable().ajax.reload();
                            window.location.reload();
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(response) {
                        var errorMessage = response.responseJSON ? response.responseJSON
                            .message : 'Error saving attachment.';
                        toastr.error(errorMessage);
                    }
                });
            });



        });
    </script>


    <script>
        $(document).ready(function() {
            $(document).on('change', '.task-checkbox', function() {
                var taskId = $(this).data('task-id');

                var isChecked = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '/update-task-status',
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        taskId: taskId,
                        isDone: isChecked
                    },
                    success: function(response) {
                        window.location.reload();

                        console.log('Task status updated successfully.');
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to update task status.');
                    }
                });
            });
        });
    </script>


@endsection
