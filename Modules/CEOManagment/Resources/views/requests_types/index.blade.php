@extends('layouts.app')
@section('title', __('ceomanagment::lang.requests_types'))
@section('content')
    <section class="content-header">
        <h1>@lang('ceomanagment::lang.requests_types')</h1>
    </section>

    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('generalmanagement.add_requests_type'))
                        @slot('tool')
                            <div class="box-tools">

                                <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                    data-target="#addRequestTypeModal">
                                    <i class="fa fa-plus"></i> @lang('ceomanagment::lang.add_requests_type')
                                </button>
                            </div>
                        @endslot
                    @endif

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped" id="requests_types">
                            <thead>
                                <tr>
                                    <th>@lang('ceomanagment::lang.request_type')</th>
                                    <th>@lang('ceomanagment::lang.request_prefix')</th>
                                    <th>@lang('ceomanagment::lang.request_for')</th>
                                    <th>@lang('ceomanagment::lang.selfish_service')</th>
                                    <th>@lang('ceomanagment::lang.tasks')</th>
                                    <th>@lang('ceomanagment::lang.action')</th>


                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <div class="modal fade" id="addRequestTypeModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeRequestType', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('ceomanagment::lang.add_requests_type')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="form-group col-md-4">
                                    {!! Form::label('type', __('essentials::lang.request_type') . ':*') !!}
                                    {!! Form::select(
                                        'type',
                                        array_combine($missingTypes, array_map(fn($type) => trans("ceomanagment::lang.$type"), $missingTypes)),
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'placeholder' => __('ceomanagment::lang.request_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-4">
                                    {!! Form::label('for', __('ceomanagment::lang.request_for') . ':*') !!}
                                    {!! Form::select(
                                        'for',
                                        [
                                            'worker' => __('ceomanagment::lang.worker'),
                                            'employee' => __('ceomanagment::lang.employee'),
                                            'both' => __('ceomanagment::lang.both'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => __('ceomanagment::lang.select_type'),
                                            'required',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-12 task-select-container">
                                    {!! Form::label('task', __('ceomanagment::lang.task') . ':') !!}
                                    <div class="input-group">
                                        <div class="col-md-6">
                                            {!! Form::text('tasks[]', null, [
                                                'class' => 'form-control task',
                                                'placeholder' => __('ceomanagment::lang.task'),
                                                'style' => 'width:100%; height:40px',
                                            ]) !!}
                                        </div>
                                        <div class="col-md-6">
                                            {!! Form::text('task_links[]', null, [
                                                'class' => 'form-control task-link',
                                                'placeholder' => __('ceomanagment::lang.task_link'),
                                                'style' => 'width:100%; height:40px',
                                            ]) !!}
                                        </div>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default add-task-btn" type="button">
                                                @lang('ceomanagment::lang.add_task')
                                            </button>
                                            <button class="btn btn-danger remove-task-btn" type="button"
                                                style="display: none;">
                                                @lang('ceomanagment::lang.remove')
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">

                                    <div class="form-group col-md-6" class="checkbox">
                                        {!! Form::label('user_type', __('ceomanagment::lang.user_type') . ':*') !!}
                                        {!! Form::select(
                                            'user_type',
                                            [
                                                'resident' => __('ceomanagment::lang.resident'),
                                                'citizen' => __('ceomanagment::lang.citizen'),
                                                'both' => __('ceomanagment::lang.both'),
                                            ],
                                            null,
                                            [
                                                'class' => 'form-control',
                                                'placeholder' => __('ceomanagment::lang.select_type'),
                                                'required',
                                                'style' => 'height:37px',
                                            ],
                                        ) !!}
                                    </div>

                                </div>


                                <div class="form-group col-md-6">
                                    <div class="checkbox">
                                        <label for="selfish_service_select" class="d-flex align-items-center"
                                            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                            {!! Form::checkbox('selfish_service', '1', old('selfish_service', $selfishServiceValue ?? false), [
                                                'id' => 'selfish_service_select',
                                                'class' => 'custom-checkbox',
                                                'aria-checked' => old('selfish_service', $selfishServiceValue ?? false) ? 'true' : 'false',
                                            ]) !!}
                                            <span class="ml-2">@lang('ceomanagment::lang.selfish_service')</span>
                                        </label>
                                    </div>
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


            <div class="modal fade" id="editRequestTypeBtnModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'route' => ['updateRequestType', ':id'],
                            'method' => 'POST',
                            'id' => 'editRequestTypeFormBtn',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('ceomanagment::lang.edit_requests_type')</h4>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="requestTypeId" id="requestTypeId">

                            <div class="row">
                                <div class="form-group col-md-3">
                                    {!! Form::label('type', __('essentials::lang.request_type') . ':*') !!}
                                    {!! Form::select(
                                        'type',
                                        [
                                            'exitRequest' => __('ceomanagment::lang.exitRequest'),
                                            'returnRequest' => __('ceomanagment::lang.returnRequest'),
                                            'escapeRequest' => __('ceomanagment::lang.escapeRequest'),
                                            'advanceSalary' => __('ceomanagment::lang.advanceSalary'),
                                            'leavesAndDepartures' => __('ceomanagment::lang.leavesAndDepartures'),
                                            'atmCard' => __('ceomanagment::lang.atmCard'),
                                            'residenceRenewal' => __('ceomanagment::lang.residenceRenewal'),
                                            'residenceIssue' => __('ceomanagment::lang.residenceIssue'),
                                            'workerTransfer' => __('ceomanagment::lang.workerTransfer'),
                                            'residenceCard' => __('ceomanagment::lang.residenceCard'),
                                            'workInjuriesRequest' => __('ceomanagment::lang.workInjuriesRequest'),
                                            'residenceEditRequest' => __('ceomanagment::lang.residenceEditRequest'),
                                            'baladyCardRequest' => __('ceomanagment::lang.baladyCardRequest'),
                                            'mofaRequest' => __('ceomanagment::lang.mofaRequest'),
                                            'insuranceUpgradeRequest' => __('ceomanagment::lang.insuranceUpgradeRequest'),
                                            'chamberRequest' => __('ceomanagment::lang.chamberRequest'),
                                            'cancleContractRequest' => __('ceomanagment::lang.cancleContractRequest'),
                                            'WarningRequest' => __('ceomanagment::lang.WarningRequest'),
                                            'assetRequest' => __('ceomanagment::lang.assetRequest'),
                                            'passportRenewal' => __('ceomanagment::lang.passportRenewal'),
                                            'AjirAsked' => __('ceomanagment::lang.AjirAsked'),
                                            'AlternativeWorker' => __('ceomanagment::lang.AlternativeWorker'),
                                            'TransferringGuaranteeFromExternalClient' => __('ceomanagment::lang.TransferringGuaranteeFromExternalClient'),
                                            'Permit' => __('ceomanagment::lang.Permit'),
                                            'FamilyInsurace' => __('ceomanagment::lang.FamilyInsurace'),
                                            'Ajir_link' => __('ceomanagment::lang.Ajir_link'),
                                            'authorizationRequest' => __('ceomanagment::lang.authorizationRequest'),
                                            'ticketReservationRequest' => __('ceomanagment::lang.ticketReservationRequest'),
                                            'interviewsRequest' => __('ceomanagment::lang.interviewsRequest'),
                                            'salaryInquiryRequest' => __('ceomanagment::lang.salaryInquiryRequest'),
                                            'moqimPrint' => __('ceomanagment::lang.moqimPrint'),
                                            'salaryIntroLetter' => __('ceomanagment::lang.salaryIntroLetter'),
                                            'QiwaContract' => __('ceomanagment::lang.QiwaContract'),
                                            'ExitWithoutReturnReport' => __('ceomanagment::lang.ExitWithoutReturnReport'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-3">
                                    {!! Form::label('for', __('ceomanagment::lang.request_for') . ':*') !!}
                                    {!! Form::select(
                                        'for',
                                        [
                                            'worker' => __('ceomanagment::lang.worker'),
                                            'employee' => __('ceomanagment::lang.employee'),
                                            'both' => __('ceomanagment::lang.both'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'for_select',
                                            'placeholder' => __('ceomanagment::lang.select_type'),
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('user_type', __('ceomanagment::lang.user_type') . ':*') !!}
                                    {!! Form::select(
                                        'user_type',
                                        [
                                            'resident' => __('ceomanagment::lang.resident'),
                                            'citizen' => __('ceomanagment::lang.citizen'),
                                            'both' => __('ceomanagment::lang.both'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'user_type_select',
                                            'style' => 'height:37px',
                                        ],
                                    ) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    <label>
                                        {!! Form::checkbox('selfish_service', '1', false, ['id' => 'selfish_service_select']) !!}
                                        @lang('ceomanagment::lang.selfish_service')
                                    </label>
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


            <div class="modal fade" id="editRequestTypeModal" tabindex="-1" role="dialog"
                aria-labelledby="editRequestTypeModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['id' => 'editRequestTypeForm', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('ceomanagment::lang.edit_request_tasks')</h4>
                        </div>

                        <div class="modal-body">
                            {!! Form::hidden('request_type_id', '', ['id' => 'requestTypeId']) !!}
                            <div class="row">

                                <div class="form-group col-md-12" id="tasks-container-modal">

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


        </div>
    </section>
@endsection
@section('javascript')
    <script>
        var translations = {
            task: "{{ __('ceomanagment::lang.task') }}",
            task_link: "{{ __('ceomanagment::lang.task_link') }}",
            add_task: "{{ __('ceomanagment::lang.add_task') }}",
            remove: "{{ __('ceomanagment::lang.remove') }}"
        };
    </script>
    <script type="text/javascript">
        $(document).ready(function() {

            var typeTranslations = {
                @foreach ($missingTypes as $type)
                    '{{ $type }}': '@lang('ceomanagment::lang.' . $type)',
                @endforeach
            };

            var requests_types = $('#requests_types').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('requests_types') }}',
                columns: [{
                        data: 'type',
                        render: function(data, type, row) {
                            switch (data) {
                                case 'exitRequest':
                                    return '@lang('ceomanagment::lang.exitRequest')';
                                case 'returnRequest':
                                    return '@lang('ceomanagment::lang.returnRequest')';
                                case 'escapeRequest':
                                    return '@lang('ceomanagment::lang.escapeRequest')';
                                case 'advanceSalary':
                                    return '@lang('ceomanagment::lang.advanceSalary')';
                                case 'leavesAndDepartures':
                                    return '@lang('ceomanagment::lang.leavesAndDepartures')';
                                case 'atmCard':
                                    return '@lang('ceomanagment::lang.atmCard')';
                                case 'residenceRenewal':
                                    return '@lang('ceomanagment::lang.residenceRenewal')';
                                case 'residenceIssue':
                                    return '@lang('ceomanagment::lang.residenceIssue')';
                                case 'workerTransfer':
                                    return '@lang('ceomanagment::lang.workerTransfer')';
                                case 'residenceCard':
                                    return '@lang('ceomanagment::lang.residenceCard')';
                                case 'workInjuriesRequest':
                                    return '@lang('ceomanagment::lang.workInjuriesRequest')';
                                case 'residenceEditRequest':
                                    return '@lang('ceomanagment::lang.residenceEditRequest')';
                                case 'baladyCardRequest':
                                    return '@lang('ceomanagment::lang.baladyCardRequest')';
                                case 'mofaRequest':
                                    return '@lang('ceomanagment::lang.mofaRequest')';
                                case 'insuranceUpgradeRequest':
                                    return '@lang('ceomanagment::lang.insuranceUpgradeRequest')';
                                case 'chamberRequest':
                                    return '@lang('ceomanagment::lang.chamberRequest')';
                                case 'cancleContractRequest':
                                    return '@lang('ceomanagment::lang.cancleContractRequest')';
                                case 'WarningRequest':
                                    return '@lang('ceomanagment::lang.WarningRequest')';
                                case 'assetRequest':
                                    return '@lang('ceomanagment::lang.assetRequest')';
                                case 'passportRenewal':
                                    return '@lang('ceomanagment::lang.passportRenewal')';
                                case 'AjirAsked':
                                    return '@lang('ceomanagment::lang.AjirAsked')';
                                case 'AlternativeWorker':
                                    return '@lang('ceomanagment::lang.AlternativeWorker')';
                                case 'TransferringGuaranteeFromExternalClient':
                                    return '@lang('ceomanagment::lang.TransferringGuaranteeFromExternalClient')';
                                case 'Permit':
                                    return '@lang('ceomanagment::lang.Permit')';
                                case 'FamilyInsurace':
                                    return '@lang('ceomanagment::lang.FamilyInsurace')';
                                case 'Ajir_link':
                                    return '@lang('ceomanagment::lang.Ajir_link')';
                                case 'authorizationRequest':
                                    return '@lang('ceomanagment::lang.authorizationRequest')';
                                case 'ticketReservationRequest':
                                    return '@lang('ceomanagment::lang.ticketReservationRequest')';
                                case 'interviewsRequest':
                                    return '@lang('ceomanagment::lang.interviewsRequest')';
                                case 'salaryInquiryRequest':
                                    return '@lang('ceomanagment::lang.salaryInquiryRequest')';
                                case 'moqimPrint':
                                    return '@lang('ceomanagment::lang.moqimPrint')';
                                case 'salaryIntroLetter':
                                    return '@lang('ceomanagment::lang.salaryIntroLetter')';
                                case 'QiwaContract':
                                    return '@lang('ceomanagment::lang.QiwaContract')';
                                case 'ExitWithoutReturnReport':
                                    return '@lang('ceomanagment::lang.ExitWithoutReturnReport')';


                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'prefix'
                    },
                    {
                        data: 'for',
                        render: function(data, type, row) {
                            if (data === 'employee') {
                                return '@lang('ceomanagment::lang.employee')';
                            } else if (data === 'worker') {
                                return '@lang('ceomanagment::lang.worker')';
                            }
                        }
                    },
                    {
                        data: 'selfish_service',
                        render: function(data, type, row) {
                            if (data === 1) {
                                return '@lang('ceomanagment::lang.can_be_selfish_service')';
                            } else if (data === 0) {
                                return '<button class="btn btn-sm btn-warning toggle-selfish-service" data-id="' +
                                    row.id + '">@lang('ceomanagment::lang.make_selfish_service')</button>';
                            }
                        }
                    },
                    {
                        data: 'tasks',
                        name: 'tasks',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]

            });

            $(document).on('click', 'button.delete_item_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_item,
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
                                    requests_types.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });


            $(document).on('click', '.edit-item', function() {
                var itemId = $(this).data('id');
                var requestType = $(this).data('type-value');
                var requestPrefix = $(this).data('prefix-value');
                var requestFor = $(this).data('for-value');
                var requestSelfish_service = $(this).data('selfish_service-value');


                var editModal = $('#editModal');

                editModal.find('select[name="type2"] option').each(function() {
                    if ($(this).text() === typeTranslations[requestType]) {
                        $(this).parent().val($(this).val()).trigger('change');
                        return false;
                    }
                });


                editModal.find('select[name="for2"]').val(requestFor).trigger('change');
                editModal.find('input[name="request_type_id"]').val(itemId);
                if (requestSelfishService) {
                    editModal.find('input[name="selfish_service"]').prop('checked', true);
                } else {
                    editModal.find('input[name="selfish_service"]').prop('checked', false);
                }
                editModal.modal('show');
            });

            $(document).on('click', '.add-task-btn', function() {
                var taskContainer = $(this).closest('.task-select-container');
                var newTaskContainer = taskContainer.clone();

                newTaskContainer.find('.task').val('');
                newTaskContainer.find('.task-link').val('');
                newTaskContainer.find('.add-task-btn').hide();
                newTaskContainer.find('.remove-task-btn').show();

                taskContainer.after(newTaskContainer);
            });
            $(document).on('click', '.toggle-selfish-service', function() {
                var requestId = $(this).data('id');
                $.ajax({
                    url: '/generalmanagement/update_selfish_service/' + requestId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selfish_service: 1
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload the DataTable to reflect changes
                            requests_types.ajax.reload();
                        } else {
                            alert('Failed to update selfish service.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while updating selfish service.');
                    }
                });
            });

            $(document).on('click', '.remove-task-btn', function() {
                $(this).closest('.task-select-container').remove();
            });



            $(document).on('click', '.edit-request-type', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var requestTypeId = $(this).data('id');
                var procedureId = $(this).data('id');
                var updateUrl = "{{ route('updateRequestType', ['id' => ':id']) }}".replace(':id',
                    requestTypeId);
                $('#editRequestTypeModal form').attr('action', updateUrl);
                $('#editRequestTypeModal #requestTypeId').val(requestTypeId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var tasksContainer = $('#editRequestTypeModal #tasks-container-modal');
                        tasksContainer.empty();

                        response.requestType.tasks.forEach(function(task) {
                            var taskHtml = `
                    <div class="form-group col-md-12 task-input-group" data-task-id="${task.id}">
                        <label for="task">${translations.task}:</label>
                        <div class="input-group">
                            <div class="col-md-6">
                                <input type="hidden" name="tasks[id][old][]" value="${task.id}">
                                <input type="text" name="tasks[description][old][]" class="form-control task" placeholder="${translations.task}" style="width:100%; height:40px;" value="${task.description}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="tasks[link][old][]" class="form-control task-link" placeholder="${translations.task_link}" style="width:100%; height:40px;" value="${task.link}">
                            </div>
                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.delete_request_type_tasks'))
                            <span class="input-group-btn">
                                <button class="btn btn-danger remove-task-btn-edit" type="button" style="margin-left: 10px;">
                                    ${translations.remove}
                                </button>
                            </span>
                            @endif
                        </div>
                    </div>`;
                            tasksContainer.append(taskHtml);
                        });

                        tasksContainer.append(
                            '<button class="btn btn-primary add-task-btn" type="button">Add New Task</button>'
                        );

                        tasksContainer.on('click', '.remove-task-btn-edit', function() {
                            var taskGroup = $(this).closest('.task-input-group');
                            var taskId = taskGroup.data('task-id');

                            // Add the task ID to the hidden input field
                            if (taskId) {
                                var deletedTasksInput = $('#deleted-tasks');
                                var deletedTasks = deletedTasksInput.val() ? JSON.parse(
                                    deletedTasksInput.val()) : [];
                                deletedTasks.push(taskId);
                                deletedTasksInput.val(JSON.stringify(deletedTasks));
                            }

                            // Remove the task from the UI
                            taskGroup.remove();
                        });

                        tasksContainer.on('click', '.add-task-btn', function() {
                            var newTaskHtml = `
                    <div class="form-group col-md-12 task-input-group">
                        <label for="task">${translations.task}:</label>
                        <div class="input-group">
                            <div class="col-md-6">
                                <input type="text" name="tasks[description][new][]" class="form-control task" placeholder="${translations.task}" style="width:100%; height:40px;" value="">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="tasks[link][new][]" class="form-control task-link" placeholder="${translations.task_link}" style="width:100%; height:40px;" value="">
                            </div>
                            <span class="input-group-btn">
                                <button class="btn btn-danger remove-task-btn-edit" type="button" style="margin-left: 10px;">
                                    ${translations.remove}
                                </button>
                            </span>
                        </div>
                    </div>`;
                            tasksContainer.append(newTaskHtml);
                        });

                        $('#editRequestTypeModal').modal('show');
                    }
                });

                $('#editRequestTypeForm').submit(function(e) {
                    e.preventDefault();
                    var formAction = $(this).attr('action');
                    var formData = $(this).serialize();

                    $.ajax({
                        url: formAction,
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success == true) {
                                toastr.success(response.msg);
                                requests_types.ajax.reload();
                                location.reload();
                            } else {
                                toastr.error(response.msg);
                                $('#editRequestTypeModal').modal('hide');
                            }
                        },
                        error: function() {
                            alert('Something went wrong. Please try again.');
                        }
                    });
                });
            });

            // $(document).on('click', '.edit-request-type-btn', function(e) {
            //     e.preventDefault();

            //     var url = $(this).data('url');
            //     var requestTypeId = $(this).data('id');

            //     // Fetch data and populate modal
            //     $.ajax({
            //         url: url,
            //         type: 'GET',
            //         success: function(response) {
            //             if (response.status === 'success') {
            //                 var tasksContainer = $('#tasks-container-modal');
            //                 tasksContainer.empty();

            //                 // Populate modal fields
            //                 $('#editRequestTypeForm #requestTypeId').val(requestTypeId);
            //                 $('#editRequestTypeForm #type_select').val(response.requestType
            //                     .type);
            //                 $('#editRequestTypeForm #for_select').val(response.requestType.for);
            //                 $('#editRequestTypeForm #user_type_select').val(response.requestType
            //                     .user_type);
            //                 $('#editRequestTypeForm #selfish_service_select').prop('checked',
            //                     response.requestType.selfish_service == 1);

            //                 // Show modal
            //                 $('#editRequestTypeBtnModal').modal('show');
            //             } else {
            //                 console.error('Failed to fetch data');
            //             }
            //         },
            //         error: function() {
            //             console.error('Error fetching request type data');
            //         }
            //     });
            // });
            $(document).on('click', '.edit-request-type-btn', function(e) {
                e.preventDefault();

                var url = $(this).data('url'); // Base URL for update
                var requestTypeId = $(this).data('id'); // ID of the request type

                // Set the form action dynamically
                $('#editRequestTypeForm').attr('action', url.replace(':id', requestTypeId));

                // Fetch data and populate modal
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editRequestTypeBtnModal #requestTypeId').val(requestTypeId);
                            $('#editRequestTypeBtnModal #type_select').val(response.requestType
                                .type);
                            $('#editRequestTypeBtnModal #for_select').val(response.requestType
                                .for);
                            $('#editRequestTypeBtnModal #user_type_select').val(response
                                .requestType.user_type);
                            $('#editRequestTypeBtnModal #selfish_service_select').prop(
                                'checked', response.requestType.selfish_service == 1);

                            // Show modal
                            $('#editRequestTypeBtnModal').modal('show');
                        } else {
                            console.error('Failed to fetch data');
                        }
                    },
                    error: function() {
                        console.error('Error fetching request type data');
                    }
                });
            });


            $(document).on('click', '.edit-request-type', function(e) {
                e.preventDefault(); // Prevent the default anchor click behavior.

                var url = $(this).data('url'); // URL from the data attribute
                var requestTypeId = $(this).data('id'); // ID from the data attribute

                // Fetch request type data via AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Populate modal fields
                            $('#editRequestTypeFormBtn').attr('action', url);
                            $('#requestTypeId').val(requestTypeId);
                            $('#type_select').val(response.data.type);
                            $('#for_select').val(response.data.for);
                            $('#user_type_select').val(response.data.user_type);
                            $('#selfish_service_select').prop('checked', response.data
                                .selfish_service === 1);

                            // Show modal
                            $('#editRequestTypeBtnModal').modal('show');
                        } else {
                            alert(response.message || 'Failed to fetch data.');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error fetching request type data.');
                    },
                });
            });



            // Form Submission
            $('#editRequestTypeForm').on('submit', function(e) {
                e.preventDefault();

                var formAction = $(this).attr('action');
                var formData = $(this).serialize();

                $.ajax({
                    url: formAction,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            $('#editRequestTypeBtnModal').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while saving data.');
                    }
                });
            });





            // Add a hidden input field to store deleted task IDs
            $('#editRequestTypeForm').append(
                '<input type="hidden" id="deleted-tasks" name="deleted_tasks" value="[]">');


        });
    </script>

@endsection
