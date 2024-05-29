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
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('ceomanagment.add_requests_type'))
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
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
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

                var editModal = $('#editModal');

                editModal.find('select[name="type2"] option').each(function() {
                    if ($(this).text() === typeTranslations[requestType]) {
                        $(this).parent().val($(this).val()).trigger('change');
                        return false;
                    }
                });


                editModal.find('select[name="for2"]').val(requestFor).trigger('change');
                editModal.find('input[name="request_type_id"]').val(itemId);

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
                        console.log(response);
                        var tasksContainer = $('#editRequestTypeModal #tasks-container-modal');
                        tasksContainer.empty();

                        response.requestType.tasks.forEach(function(task) {
                            var taskHtml = `
                                    <div class="form-group col-md-12 task-input-group">
                                        <label for="task">${translations.task}:</label>
                                        <div class="input-group">
                                            <div class="col-md-6">
                                                <input type="text" name="tasks[description][]" class="form-control task" placeholder="${translations.task}" style="width:100%; height:40px;" value="${task.description}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="tasks[link][]" class="form-control task-link" placeholder="${translations.task_link}" style="width:100%; height:40px;" value="${task.link}">
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
                            '<button class="btn btn-primary add-task-btn" type="button">Add New Task </button>'
                        );


                        tasksContainer.on('click', '.remove-task-btn-edit', function() {
                            $(this).closest('.task-input-group').remove();
                        });

                        $('.add-task-btn').off('click').on('click', function() {
                            var newTaskHtml = `
                                <div class="form-group col-md-12 task-input-group">
                                    <label for="task">${translations.task}:</label>
                                    <div class="input-group">
                                        <div class="col-md-6">
                                            <input type="text" name="tasks[description][]" class="form-control task" placeholder="${translations.task}" style="width:100%; height:40px;" value="">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="tasks[link][]" class="form-control task-link" placeholder="${translations.task_link}" style="width:100%; height:40px;" value="">
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


        });
    </script>

@endsection
