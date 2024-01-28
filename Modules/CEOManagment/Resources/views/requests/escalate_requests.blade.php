@extends('layouts.app')
@section('title', __('followup::lang.allRequests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.allRequests')</span>
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
        @include('ceomanagment::layouts.nav_requests')

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.request_number')</th>
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('followup::lang.eqama_number')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.request_type')</th>
                            <th>@lang('followup::lang.request_date')</th>
                            <th>@lang('followup::lang.status')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.action')</th>


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
                        <h4 class="modal-title">@lang('followup::lang.view_request')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="workflow-container" id="workflow-container">
                                <!-- Workflow circles will be dynamically added here -->
                            </div>


                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('followup::lang.worker_details')</h4>
                                <ul id="worker-list">
                                    <!-- Worker info will be dynamically added here -->
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('followup::lang.activites')</h4>
                                <ul id="activities-list">
                                    <!-- Activities will be dynamically added here -->
                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('followup::lang.attachments')</h4>
                                <ul id="attachments-list">

                                </ul>
                            </div>
                        </div>
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="attachment">
                                    <h4>@lang('followup::lang.add_attachment')</h4>
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

        {{-- return request --}}
        <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnModalLabel">@lang('followup::lang.return_the_request')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="returnModalForm">
                            <div class="form-group">
                                <label for="reasonInput">@lang('followup::lang.reason')</label>
                                <input type="text" class="form-control" id="reasonInput" required>
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('followup::lang.update')</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('followup::lang.close')</button>
                    </div>
                </div>
            </div>
        </div>

        @include('generalmanagement::requests.change_status_modal')

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        var langStrings = {
            approved: '{{ __('followup::lang.approved') }}',
            rejected: '{{ __('followup::lang.rejected') }}'
        };
    </script>
    <script type="text/javascript">
        $(document).ready(function() {



            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('ceomanagment.escalate_requests') }}"
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
                        data: 'assigned_to'
                    },
                    {
                        data: 'type',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('followup::lang.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('followup::lang.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('followup::lang.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('followup::lang.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('followup::lang.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('followup::lang.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('followup::lang.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('followup::lang.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('followup::lang.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('followup::lang.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('followup::lang.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('followup::lang.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('followup::lang.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('followup::lang.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('followup::lang.chamberRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('followup::lang.cancleContractRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('followup::lang.WarningRequest')';
                            } else {
                                return data;
                            }
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
                                @lang('followup::lang.view_request')
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
                    url: "{{ route('ess_returnReq') }}",
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


            $(document).on('click', '.btn-view-request', function() {
                var requestId = $(this).data('request-id');

                // var data = requests_table.row(this).data();
                // var requestId = data.id;

                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewCEORequest', ['requestId' => ':requestId']) }}'.replace(
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
                                '{{ __('followup::lang.worker_name') }}' + ': ' + response
                                .user_info.worker_full_name + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.nationality') }}' + ': ' + response
                                .user_info.nationality + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.project_name') }}' + ': ' + response
                                .user_info.assigned_to + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.eqama_number') }}' + ': ' + response
                                .user_info.id_proof_number + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.contract_end_date') }}' + ': ' +
                                response.user_info.contract_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.eqama_end_date') }}' + ': ' +
                                response.user_info.eqama_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('followup::lang.passport_number') }}' + ': ' +
                                response.user_info.passport_number + '</p>');



                            //activities

                            // activitiesList.append('<p class="worker-info">' + '{{ __('followup::lang.created_by') }}' + ': ' + created_user_info.created_user_full_name + '</p>');    

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
                                    '{{ trans('followup::lang.attach') }} ' + (j + 1) + '</a>';

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


    <script>
        $(document).ready(function() {
            var mainReasonSelect = $('#mainReasonSelect');
            var subReasonContainer = $('#sub_reason_container');
            var subReasonSelect = $('#subReasonSelect');

            handleTypeChange();
            $('#requestType').change(handleTypeChange);

            function handleTypeChange() {
                var selectedType = $('#requestType').val();

                console.log(selectedType);
                if (selectedType === 'leavesAndDepartures') {
                    $('#start_date').show();

                } else {
                    $('#start_date').hide();
                }

                if (selectedType === 'leavesAndDepartures') {
                    $('#end_date').show();
                } else {
                    $('#end_date').hide();
                }
                if (selectedType === 'returnRequest') {
                    $('#exit_date').show();
                    $('#return_date').show();

                } else {
                    $('#exit_date').hide();
                    $('#return_date').hide();

                }
                if (selectedType === 'leavesAndDepartures') {
                    $('#leaveType').show();
                } else {
                    $('#leaveType').hide();
                }
                if (selectedType === 'workInjuriesRequest') {
                    $('#workInjuriesDate').show();
                } else {
                    $('#workInjuriesDate').hide();
                }


                if (selectedType === 'escapeRequest') {
                    $('#escape_time').show();
                    $('#escape_date').show();

                } else {
                    $('#escape_time').hide();
                    $('#escape_date').hide();
                }
                if (selectedType === 'advanceSalary') {
                    $('#installmentsNumber').show();
                    $('#monthlyInstallment').show();
                    $('#amount').show();

                } else {
                    $('#installmentsNumber').hide();
                    $('#monthlyInstallment').hide();
                    $('#amount').hide();
                }
                if (selectedType === 'residenceEditRequest') {
                    $('#resEditType').show();


                } else {
                    $('#resEditType').hide();

                }
                if (selectedType === 'baladyCardRequest') {
                    $('#baladyType').show();


                } else {
                    $('#baladyType').hide();

                }
                if (selectedType === 'insuranceUpgradeRequest') {
                    $('#ins_class').show();


                } else {
                    $('#ins_class').hide();

                }
                if (selectedType === 'cancleContractRequest') {
                    $('#main_reason').show();


                } else {
                    $('#main_reason').hide();

                }
                if (selectedType === 'chamberRequest' || selectedType === 'mofaRequest') {
                    $('#visa_number').show();


                } else {
                    $('#visa_number').hide();

                }
                if (selectedType === 'atmCard') {
                    $('#atmType').show();


                } else {
                    $('#atmType').hide();

                }
            }

            mainReasonSelect.on('change', function() {
                var selectedMainReason = $(this).val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: '{{ route('getSubReasons') }}',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        main_reason: selectedMainReason
                    },
                    success: function(data) {
                        subReasonSelect.empty();

                        if (data.sub_reasons.length > 0) {
                            subReasonContainer.show();

                            $.each(data.sub_reasons, function(index, subReason) {
                                subReasonSelect.append($('<option>', {
                                    value: subReason.id,
                                    text: subReason.name
                                }));
                            });
                        } else {
                            subReasonContainer.hide();
                        }
                    }
                });

            });
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#worker').select2({

                ajax: {
                    url: '{{ route('search_proofname') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results,
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 1,
                templateResult: formatResult,
                templateSelection: formatSelection,
                escapeMarkup: function(markup) {
                    return markup;
                },
            });

            function formatResult(result) {
                if (result.loading) return result.text;

                var markup = "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" + result.full_name + "</div>" +

                    "</div>";

                return markup;
            }

            function formatSelection(result) {
                return result.full_name || result.text;
            }


        });
    </script>

@endsection
