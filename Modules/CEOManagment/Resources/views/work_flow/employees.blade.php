@extends('layouts.app')
@section('title', __('essentials::lang.procedures'))
<style>
    .custom-modal-dialog.style {
        width: 72% !important;

    }

    .hidden-step-details {
        display: none;
    }

    .add-escalation {
        margin: 0 auto;
    }

    .my-button {
        margin: 0 10px 10px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.procedures')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('ceomanagment::layouts.nav_wk_procedures')
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addProceduresModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="procedures_table">
                    <thead>
                        <tr>


                            <th>@lang('essentials::lang.type')</th>
                            <th>@lang('essentials::lang.steps')</th>
                            {{-- <th>@lang('essentials::lang.escalations')</th> --}}
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade Procedures_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="addProceduresModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog custom-modal-dialog style ">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeEmployeeProcedure']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_procedure')</h4>
                    </div>

                    <div class="modal-body">
                        <div>
                            <div class="row stepsClass">

                                <div class="form-group col-md-6">
                                    {!! Form::label('type', __('essentials::lang.procedure_type') . ':*') !!}
                                    {!! Form::select(
                                        'type',
                                        collect($missingTypes)->mapWithKeys(fn($type, $id) => [$id => trans("ceomanagment::lang.$type")])->toArray(),
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'placeholder' => __('essentials::lang.procedure_type'),
                                            'required',
                                            'style' => 'width:100%; height:40px',
                                        ],
                                    ) !!}
                                    <div class="checkbox">
                                        <label style="font-weight: bold; color: red;">
                                            {!! Form::checkbox('superior_department', 1, false, ['class' => 'custom-checkbox']) !!} {{ __('essentials::lang.go_to_superior_department') }}
                                        </label>
                                    </div>
                                </div>


                                <div class="clearfix"></div>
                                <div id="workflow-step_add_modal">

                                    <div class="form-group col-md-12 entire_step" id="add_modal_step_0">
                                        {!! Form::hidden('escalation_count', 1) !!}
                                        <div class="form-group col-md-4">
                                            {!! Form::label('add_modal_department_id_steps', __('essentials::lang.managment') . ':*') !!}
                                            {!! Form::select('step[0][add_modal_department_id_steps][]', $departments, null, [
                                                'class' => 'form-control departments pull-right',
                                                'id' => 'add_modal_select_step_0',
                                                'required',
                                                'placeholder' => __('essentials::lang.selectDepartment'),
                                                'style' => 'height:40px',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {!! Form::label('action_type', __('ceomanagment::lang.action_type') . ':*') !!}
                                            {!! Form::select(
                                                'step[0][action_type]',
                                                ['accept_reject' => __('ceomanagment::lang.Accept/Reject'), 'task' => __('ceomanagment::lang.do_task')],
                                                null,
                                                [
                                                    'class' => 'form-control action_type_select',
                                                    'placeholder' => __('ceomanagment::lang.action_type'),
                                                    'style' => 'width:100%; height:40px',
                                                ],
                                            ) !!}
                                        </div>

                                        <div class="form-group col-md-6 task-select-container" style="display: none;">
                                            <div class="task_template">
                                                {!! Form::label('task', __('ceomanagment::lang.task') . ':*') !!}
                                                <div class="input-group">
                                                    {!! Form::select('step[0][tasks][]', $tasks, null, [
                                                        'class' => 'form-control task-select',
                                                        'placeholder' => __('ceomanagment::lang.task'),
                                                        'style' => 'width:100%; height:40px',
                                                    ]) !!}
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default add-task-btn" type="button">
                                                            @lang('ceomanagment::lang.add_task')</button>

                                                        <button class="btn btn-danger remove-task-btn" type="button"
                                                            style="display: none;"> @lang('ceomanagment::lang.remove')</button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="form-group col-md-4 can-reject-checkbox-container"
                                            style="display: block;">
                                            <div class="checkbox">
                                                <label>

                                                    {!! Form::checkbox('step[0][add_modal_can_reject_steps][]', 1, null, []) !!}{{ __('essentials::lang.can_reject') }}

                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <div class="checkbox">
                                                <label>

                                                    {!! Form::checkbox('step[0][add_modal_can_return_steps][]', 1, null, []) !!}{{ __('essentials::lang.can_return') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <button type="button"id="add_modal_remove_step_btn_0" style="display:none"
                                                class="btn btn-danger btn-sm add_modal_remove_step_btn">
                                                @lang('essentials::lang.remove_department')
                                            </button>
                                        </div>

                                        <div class="clearfix"></div>

                                        <div class="form-group col-md-2">
                                            <button type="button"
                                                class="btn btn-sm btn-success add_modal_add_escalation_steps_btn">
                                                @lang('essentials::lang.add_escalation')
                                            </button>
                                        </div>

                                        <div class="escalations-container col-md-12">
                                            <div class="escalation-field-template col-md-12">
                                                <div class="form-group col-md-4">
                                                    {!! Form::label('add_modal_escalates_to_steps', __('essentials::lang.escalates_to') . ':') !!}
                                                    {!! Form::select('step[0][add_modal_escalates_to_steps][]', $escalates_departments, null, [
                                                        'class' => 'form-control push',
                                                        'placeholder' => __('essentials::lang.escalates_to'),
                                                        'style' => 'height:40px',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-4">
                                                    {!! Form::label(
                                                        'add_modal_escalates_after_steps',
                                                        __('essentials::lang.escalates_after') . ' (' . __('essentials::lang.in_hours') . ')' . ':',
                                                    ) !!}

                                                    <div class="input-group">
                                                        {!! Form::number('step[0][add_modal_escalates_after_steps][]', null, [
                                                            'class' => 'form-control',
                                                        
                                                            'placeholder' => __('essentials::lang.escalates_after'),
                                                            'style' => 'height:40px',
                                                        ]) !!}

                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger add_modal_remove_escalation_steps_btn">
                                                        @lang('essentials::lang.remove_escalation')
                                                    </button>
                                                </div>
                                                {{-- <div class="additional-escalations col-md-12"></div> --}}
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <hr>
                                    </div>
                                </div>
                                <br>
                            </div>
                            <div>
                                <button type="button"class="btn btn-sm btn-warning my-button addStep"
                                    id="add_modal_add_step">
                                    @lang('essentials::lang.add_managment')
                                </button>
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
        </div>

        <div class="modal fade" id="editProceduresModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog custom-modal-dialog style ">
                <div class="modal-content">
                    {!! Form::open(['id' => 'editProcedureForm', 'method' => 'POST']) !!}
                    {!! Form::hidden('_method', 'PUT') !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.edit_procedure')</h4>
                    </div>

                    <div class="modal-body">
                        <div>
                            <div class="row stepsClass">
                                <div class="form-group col-md-6">
                                    <div class="checkbox">
                                        <label style="font-weight: bold; color: red;">
                                            {!! Form::checkbox('superior_department', 1, false, [
                                                'class' => 'custom-checkbox',
                                            ]) !!} {{ __('essentials::lang.go_to_superior_department') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="clearfix"></div>



                                <div id="workflow-step_edit_modal">

                                    <div class="form-group col-md-12 entire_step" id="edit_modal_step_0">
                                        {!! Form::hidden('escalation_count', 1) !!}
                                        <div class="form-group col-md-6">
                                            {!! Form::label('edit_modal_department_id_steps', __('essentials::lang.managment') . ':*') !!}
                                            {!! Form::select('step[0][edit_modal_department_id_steps][]', $departments, null, [
                                                'class' => 'form-control departments pull-right',
                                                'id' => 'edit_modal_select_step_0',
                                                'required',
                                                'placeholder' => __('essentials::lang.selectDepartment'),
                                                'style' => 'height:40px',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {!! Form::label('edit_action_type', __('ceomanagment::lang.action_type') . ':*') !!}
                                            {!! Form::select(
                                                'step[0][edit_action_type]',
                                                ['accept_reject' => __('ceomanagment::lang.Accept/Reject'), 'task' => __('ceomanagment::lang.do_task')],
                                                null,
                                                [
                                                    'class' => 'form-control action_type_select',
                                                    'placeholder' => __('ceomanagment::lang.action_type'),
                                                    'style' => 'width:100%; height:40px',
                                                ],
                                            ) !!}
                                        </div>

                                        <div class="form-group col-md-6 task-select-container" style="display: none;">
                                            <div class="task_template">
                                                {!! Form::label('edit_tasks', __('ceomanagment::lang.task') . ':*') !!}
                                                <div class="input-group">
                                                    {!! Form::select('step[0][edit_tasks][]', $tasks, null, [
                                                        'class' => 'form-control task-select',
                                                        'placeholder' => __('ceomanagment::lang.task'),
                                                        'style' => 'width:100%; height:40px',
                                                    ]) !!}
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default add-task-btn" type="button">
                                                            @lang('ceomanagment::lang.add_task')</button>

                                                        <button class="btn btn-danger remove-task-btn" type="button"
                                                            style="display: none;"> @lang('ceomanagment::lang.remove')</button>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="form-group col-md-4 edit-can-reject-checkbox-container"
                                            style="display: block;">
                                            <div class="checkbox">

                                                <label>

                                                    {!! Form::checkbox('step[0][edit_modal_can_reject_steps][]', 1, null, []) !!}{{ __('essentials::lang.can_reject') }}

                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <div class="checkbox">
                                                <label>

                                                    {!! Form::checkbox('step[0][edit_modal_can_return_steps][]', 1, null, []) !!}{{ __('essentials::lang.can_return') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <button type="button"id="edit_modal_remove_step_btn_0" style="display:none"
                                                class="btn btn-danger btn-sm edit_modal_remove_step_btn">
                                                @lang('essentials::lang.remove_department')
                                            </button>
                                        </div>

                                        <div class="clearfix"></div>

                                        <div class="form-group col-md-2">
                                            <button type="button"
                                                class="btn btn-sm btn-success edit_modal_add_escalation_steps_btn">
                                                @lang('essentials::lang.add_escalation')
                                            </button>
                                        </div>

                                        <div class="escalations-container col-md-12">
                                            <div class="escalation-field-template col-md-12">
                                                <div class="form-group col-md-4">
                                                    {!! Form::label('edit_modal_escalates_to_steps', __('essentials::lang.escalates_to') . ':') !!}
                                                    {!! Form::select('step[0][edit_modal_escalates_to_steps][]', $escalates_departments, null, [
                                                        'class' => 'form-control push',
                                                        'placeholder' => __('essentials::lang.escalates_to'),
                                                        'style' => 'height:40px',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-4">
                                                    {!! Form::label(
                                                        'edit_modal_escalates_after_steps',
                                                        __('essentials::lang.escalates_after') . ' (' . __('essentials::lang.in_hours') . ')' . ':',
                                                    ) !!}

                                                    <div class="input-group">
                                                        {!! Form::number('step[0][edit_modal_escalates_after_steps][]', null, [
                                                            'class' => 'form-control',
                                                        
                                                            'placeholder' => __('essentials::lang.escalates_after'),
                                                            'style' => 'height:40px',
                                                        ]) !!}

                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger edit_modal_remove_escalation_steps_btn">
                                                        @lang('essentials::lang.remove_escalation')
                                                    </button>
                                                </div>
                                                {{-- <div class="additional-escalations col-md-12"></div> --}}
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <hr>
                                    </div>
                                </div>
                                <br>
                            </div>
                            <div>
                                <button type="button"class="btn btn-sm btn-warning my-button addStep"
                                    id="edit_modal_add_step">
                                    @lang('essentials::lang.add_managment')
                                </button>
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
        </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')

    <script>
        $(document).ready(function() {
            $('#addProceduresModal').on('shown.bs.modal', function(e) {

                $('#type_select').select2({
                    dropdownParent: $(
                        '#addProceduresModal'),
                    width: '100%',
                });


            });
            $('.select2').select2({
                width: '100%'
            });

            let excilation_max_count = {{ count($escalates_departments) }};
            var procedures_table = $('#procedures_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employeesProcedures') }}",

                },
                columns: [

                    {
                        data: 'request_type_id',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('request.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('request.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('request.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('request.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('request.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('request.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('request.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('request.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('request.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('request.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('request.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('request.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('request.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('request.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('request.chamberRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('request.cancleContractRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('request.WarningRequest')';
                            } else if (data === 'assetRequest') {
                                return '@lang('request.assetRequest')';
                            } else if (data === 'passportRenewal') {
                                return '@lang('request.passportRenewal')';
                            } else if (data === 'AjirAsked') {
                                return '@lang('request.AjirAsked')';
                            } else if (data === 'AlternativeWorker') {
                                return '@lang('request.AlternativeWorker')';
                            } else if (data === 'TransferringGuaranteeFromExternalClient') {
                                return '@lang('request.TransferringGuaranteeFromExternalClient')';
                            } else if (data === 'Permit') {
                                return '@lang('request.Permit')';
                            } else if (data === 'FamilyInsurace') {
                                return '@lang('request.FamilyInsurace')';
                            } else if (data === 'Ajir_link') {
                                return '@lang('request.Ajir_link')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'steps'
                    },
                    // {
                    //     data: 'escalations'
                    // },
                    {
                        data: 'action'
                    },


                ]
            });
            $(document).on('click', 'button.delete_procedure_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_procedure,
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
                                    //  procedures_table.ajax.reload();
                                    window.location.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
            /////////////////////////////start add///////////////////////////////////////////////////////
            let add_modal_steps_count = 0;
            $(document).on("click", ".add_modal_remove_step_btn", function() {
                var stepDiv = $(this).closest('.entire_step');
                stepDiv.remove();
            });
            $(document).on("click", ".add_modal_add_escalation_steps_btn", function() {

                var stepContainer = $(this).closest('.entire_step');
                var escalationContainer = stepContainer.find('.escalations-container');
                var escalationCountInput = stepContainer.find(
                    'input[name^="escalation_count"]');
                var currentEscalationCount = parseInt(escalationCountInput.val());

                if (currentEscalationCount < excilation_max_count) {

                    var clone = $(this).closest('.entire_step').find('.escalation-field-template').first()
                        .clone();

                    clone.find('input').val('');
                    clone.find('select').val('');
                    $(this).closest('.entire_step').find('.escalations-container').append(clone);
                    escalationCountInput.val(currentEscalationCount + 1);
                }

            });

            $(document).on("click", ".add_modal_remove_escalation_steps_btn", function() {
                var entireStep = $(this).closest('.entire_step');
                var escalationCountInput = entireStep.find(
                    'input[name^="escalation_count"]');
                var currentEscalationCount = parseInt(escalationCountInput.val());

                var numberOfTemplates = entireStep.find('.escalation-field-template').length;
                if (numberOfTemplates > 1) {
                    $(this).closest('.escalation-field-template').remove();
                    var newEscalationCount = Math.max(currentEscalationCount - 1, 1);
                    escalationCountInput.val(newEscalationCount);
                }
            });
            let stepZeroVisible = false;
            $(document).on("click", "#add_modal_add_step", function() {

                var stepZero = $("#add_modal_step_0");
                var newStep = stepZero.clone();
                newStep.find('.escalation-field-template').not(':first').remove();
                newStep.find('.task_template').not(':first').remove();
                add_modal_steps_count++;
                newStep.attr('id', 'add_modal_step_' + add_modal_steps_count);
                newStep.find('[id]').each(function() {
                    var newId = $(this).attr('id').replace(/_0$/, '_' + add_modal_steps_count);
                    $(this).attr('id', newId);
                });
                newStep.find('[name]').each(function() {
                    var newName = $(this).attr('name').replace(/step\[0\]/, 'step[' +
                        add_modal_steps_count + ']');
                    $(this).attr('name', newName);
                });
                // newStep.css('display', 'block');
                newStep.find('.escalation-field-template').find('input').val(
                    '');
                newStep.find('.escalation-field-template').find('select').val(
                    '');
                newStep.find('.action_type_select').val('');
                newStep.find('.task-select-container').hide();
                newStep.find('.can-reject-checkbox-container').show();
                newStep.find('.add_modal_remove_step_btn').css('display', 'block');
                escalationCountInput = newStep.find(
                    'input[name^="escalation_count"]').val(1);

                $("#workflow-step_add_modal").append(newStep);

            });


            /////////////////////////////end/////////////////////////////////////////////////////////

            /////////////////////////////start edit///////////////////////////////////////////////////////
            let edit_modal_steps_count = 0;
            $(document).on("click", ".edit_modal_remove_step_btn", function() {
                var stepDiv = $(this).closest('.entire_step');
                stepDiv.remove();
            });
            $(document).on("click", ".edit_modal_add_escalation_steps_btn", function() {

                var stepContainer = $(this).closest('.entire_step');
                var escalationContainer = stepContainer.find('.escalations-container');
                var escalationCountInput = stepContainer.find(
                    'input[name^="escalation_count"]');
                var currentEscalationCount = parseInt(escalationCountInput.val());

                if (currentEscalationCount < excilation_max_count) {


                    var clone = $(this).closest('.entire_step').find('.escalation-field-template').first()
                        .clone();
                    clone.find('input').val('');
                    clone.find('select').val('');
                    $(this).closest('.entire_step').find('.escalations-container').append(clone);
                    escalationCountInput.val(currentEscalationCount + 1);
                }
            });

            $(document).on("click", ".edit_modal_remove_escalation_steps_btn", function() {

                var entireStep = $(this).closest('.entire_step');
                var escalationCountInput = entireStep.find(
                    'input[name^="escalation_count"]');
                var currentEscalationCount = parseInt(escalationCountInput.val());

                var numberOfTemplates = entireStep.find('.escalation-field-template').length;
                if (numberOfTemplates > 1) {


                    var entireStep = $(this).closest('.entire_step');
                    var numberOfTemplates = entireStep.find('.escalation-field-template').length;
                    if (numberOfTemplates > 1) {
                        $(this).closest('.escalation-field-template').remove();
                    }
                    var newEscalationCount = Math.max(currentEscalationCount - 1, 1);
                    escalationCountInput.val(newEscalationCount);
                }
            });

            let editStepZeroVisible = false;

            $(document).on("click", "#edit_modal_add_step", function() {
                var stepZero = $("#edit_modal_step_0");
                var newStep = stepZero.clone();
                newStep.find('.escalation-field-template').not(':first').remove();

                newStep.find('input, select').val('');
                newStep.find('.task-select-container').hide();

                edit_modal_steps_count++;
                newStep.attr('id', 'edit_modal_step_' + edit_modal_steps_count);
                newStep.find('[id]').each(function() {
                    var newId = $(this).attr('id').replace(/_0$/, '_' + edit_modal_steps_count);
                    $(this).attr('id', newId);
                });

                newStep.find('[name]').each(function() {
                    var newName = $(this).attr('name').replace(/step\[0\]/, 'step[' +
                        edit_modal_steps_count + ']');
                    $(this).attr('name', newName);
                });
                newStep.css('display', 'block');

                newStep.find('.edit_modal_remove_step_btn').css('display', 'block');
                escalationCountInput = newStep.find(
                    'input[name^="escalation_count"]').val(1);
                $("#workflow-step_edit_modal").append(newStep);

            });


            function clearEditModal() {
                $('#editProceduresModal input[type="text"], #editProceduresModal textarea').val('');
                $('#editProceduresModal select').val('').trigger('change');
                $("#workflow-step_edit_modal").find('.entire_step').find('.escalation-field-template').not(':first')
                    .remove();
                $("#workflow-step_edit_modal").find('.entire_step').find('.task_template').not(':first')
                    .remove();
                $("#workflow-step_edit_modal").find('.entire_step').not(':first').remove();

            }

            $(document).on('click', '.edit-procedure', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var procedureId = $(this).data('id');
                var updateUrl = "{{ route('updateEmployeeProcedure', ['id' => ':id']) }}".replace(':id',
                    procedureId);


                $('#editProcedureForm').attr('action', updateUrl);

                clearEditModal();
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        console.log(response.procedures);
                        console.log(response.superior_dep);
                        if (response.superior_dep) {
                            $('#editProcedureForm input[name="superior_department"]').prop(
                                'checked', true);
                        } else {
                            $('#editProcedureForm input[name="superior_department"]').prop(
                                'checked', false);
                        }
                        var procedures = typeof response.procedures === 'string' ? JSON.parse(
                            response.procedures) : response.procedures;

                        var otherDepartments = procedures;
                        if (procedures.length > 0) {

                            otherDepartments.forEach(function(procedure, index) {

                                if (index === 0) {

                                    populateStepData('#edit_modal_step_0', index,
                                        procedure);
                                } else {
                                    addStepToEditModal(procedure, index);

                                }
                            });
                            $('#editProceduresModal').modal('show');


                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error in AJAX request:", error);
                    }
                });


                function populateStepData(stepSelector, index, stepData) {

                    // Populate the step with data
                    $(stepSelector).find('[name^="step[' + index + '][edit_modal_department_id_steps]"]')
                        .val(stepData.department_id);
                    $(stepSelector).find('[name^="step[' + index + '][edit_modal_can_reject_steps]"]').prop(
                        'checked', stepData.can_reject);
                    $(stepSelector).find('[name^="step[' + index + '][edit_modal_can_return_steps]"]').prop(
                        'checked', stepData.can_return);
                    $(stepSelector).find('[name^="step[' + index + '][edit_action_type]"]')
                        .val(stepData.action_type);
                    // Populate the first escalation directly
                    if (stepData.escalations && stepData.escalations.length > 0) {
                        var firstEscalation = stepData.escalations[0];
                        var firstEscalationContainer = $(stepSelector).find('.escalation-field-template')
                            .first();
                        firstEscalationContainer.find('select[name^="step[' + index +
                            '][edit_modal_escalates_to_steps]"]').val(firstEscalation.escalates_to);
                        firstEscalationContainer.find('input[name^="step[' + index +
                            '][edit_modal_escalates_after_steps]"]').val(firstEscalation
                            .escalates_after);
                    }
                    $(stepSelector).closest('.entire_step').find('.edit-can-reject-checkbox-container')
                        .show()
                    // Clone and populate additional escalations if they exist
                    var escalationsContainer = $(stepSelector).find('.escalations-container');
                    if (stepData.escalations.length > 1) {
                        stepData.escalations.slice(1).forEach(function(escalation, escalationIndex) {

                            var stepContainer = $(stepSelector).closest('.entire_step');
                            var escalationContainer = stepContainer.find('.escalations-container');
                            var escalationCountInput = stepContainer.find(
                                'input[name^="escalation_count"]');
                            var currentEscalationCount = parseInt(escalationCountInput.val());

                            var escalationClone = $(stepSelector).find('.escalation-field-template')
                                .first().clone();

                            // Update names for the escalation fields
                            escalationClone.find('[name]').each(function() {
                                var newName = $(this).attr('name').replace(/step\[0\]/,
                                        'step[' + index + ']')
                                    .replace(/\[\]$/, '[' + (escalationIndex + 1) +
                                        ']'
                                    ); // +1 because first escalation is already there
                                $(this).attr('name', newName);
                            });

                            // Populate escalation data
                            escalationClone.find('select[name^="step[' + index +
                                '][edit_modal_escalates_to_steps]"]').val(escalation
                                .escalates_to);
                            escalationClone.find('input[name^="step[' + index +
                                '][edit_modal_escalates_after_steps]"]').val(escalation
                                .escalates_after);

                            // Remove ID attributes to avoid duplicates
                            escalationClone.find('[id]').removeAttr('id');

                            escalationCountInput.val(currentEscalationCount + 1);

                            escalationsContainer.append(escalationClone);
                        });
                    }


                    if (stepData.tasks && stepData.tasks.length > 0) {
                        var firstTask = stepData.tasks[0];
                        var parent = $(stepSelector).closest('.entire_step');
                        var container = parent.find('.task-select-container').first();
                        container.show();
                        container.find('.add-task-btn').show();
                        container.find('.remove-task-btn')
                            .hide();
                        parent.find('.edit-can-reject-checkbox-container').hide();


                        var typeId = stepData.request_type_id;

                        $.ajax({
                            url: '/ceomanagment/get-tasks-for-type',
                            type: 'GET',
                            data: {
                                typeId: typeId
                            },
                            success: function(response) {
                                var container = $(stepSelector).find('.task-select-container');

                                var tasksSelect = container.find(
                                    'select[name="step[' + index + '][edit_tasks][]"]');
                                tasksSelect.empty();
                                tasksSelect.append('<option value="">' + 'Select Task' +
                                    '</option>');
                                $.each(response, function(key, value) {
                                    tasksSelect.append('<option value="' + key + '">' +
                                        value + '</option>');
                                });
                                tasksSelect.val(firstTask.id);
                            }
                        });

                    }

                    if (stepData.tasks && stepData.tasks.length > 1) {


                        stepData.tasks.slice(1).forEach(function(task) {

                            var stepContainer = $(stepSelector).closest('.entire_step');
                            var taskContainers = stepContainer.find('.task-select-container');
                            var taskClone = taskContainers.find('.task_template').clone();
                            stepContainer.find('.edit-can-reject-checkbox-container').hide();

                            // var newIndex = index;
                            var newSelect = taskClone.find('select').val('');
                            taskClone.find('.add-task-btn').hide();
                            taskClone.find('.remove-task-btn').show().css('display',
                                'inline-block');
                            taskClone.css('display', '');


                            var typeId = stepData
                                .request_type_id;
                            $.ajax({
                                url: '/ceomanagment/get-tasks-for-type',
                                type: 'GET',
                                data: {
                                    typeId: typeId
                                },
                                success: function(response) {
                                    newSelect.empty();
                                    newSelect.append('<option value="">' +
                                        'Select Task' + '</option>');
                                    $.each(response, function(key, value) {
                                        newSelect.append($('<option>', {
                                            value: key,
                                            text: value
                                        }));
                                    });
                                    newSelect.val(task.id);
                                }
                            });
                            taskContainers.append(taskClone);

                        });
                    }



                    // Add click event listener for the remove button

                    $(stepSelector).find('.select2').select2();
                    edit_modal_steps_count++;
                }



                function addStepToEditModal(stepData, stepIndex) {

                    var stepTemplate = $('#edit_modal_step_0').clone();
                    stepTemplate.find('.task_template').not(':first').remove();
                    stepTemplate.attr('id', 'edit_modal_step_' + stepIndex);
                    stepTemplate.find('[id]').each(function() {
                        var newId = $(this).attr('id').replace(/_0$/, '_' + stepIndex);
                        $(this).attr('id', newId);
                    });
                    stepTemplate.find('[name]').each(function() {
                        var newName = $(this).attr('name').replace(/step\[0\]/, 'step[' +
                            stepIndex + ']');
                        $(this).attr('name', newName);
                    });
                    // var parent = $(stepTemplate).closest('.entire_step');
                    stepTemplate.find('.task-select-container').hide();

                    populateStepData(stepTemplate, stepIndex, stepData);
                    stepTemplate.css('display', 'block');
                    stepTemplate.find('.edit_modal_remove_step_btn').css('display', 'block');
                    $('#workflow-step_edit_modal').append(stepTemplate);

                }

            });
            /////////////////////////////end/////////////////////////////////////////////////////////


            function adjustTaskInputs(container, actionType) {
                if (actionType === 'task') {
                    container.find('.task-select-container').first().show();
                    container.find('.task-select-container').not(':first').remove();
                    container.find('.task-select-container').find('select').val('');
                    container.find('.add-task-btn').first().show();
                    container.find('.can-reject-checkbox-container').hide();
                    container.find('.edit-can-reject-checkbox-container').hide();
                } else {
                    container.find('.task-select-container').find('select').val('');
                    container.find('.task-select-container').hide();
                    container.find('.task-select-container').not(':first').remove();
                    container.find('.can-reject-checkbox-container').show();
                    container.find('.edit-can-reject-checkbox-container').show();
                }
            }

            $(document).on('change', '.action_type_select', function() {
                var selectedActionType = $(this).val();
                var stepContainer = $(this).closest('.entire_step');

                adjustTaskInputs(stepContainer, selectedActionType);
            });

            $(document).on('click', '.add-task-btn', function(e) {
                e.preventDefault();
                var taskSelectContainer = $(this).closest('.task-select-container');
                var taskTemplate = $(this).closest('.task_template');
                var newTaskSelect = taskTemplate.clone();
                newTaskSelect.find('select').val('');
                newTaskSelect.find('.add-task-btn').hide();
                newTaskSelect.find('.remove-task-btn').show();
                taskSelectContainer.append(newTaskSelect);
            });
            $(document).on('click', '.remove-task-btn', function() {
                $(this).closest('.task_template').remove();
            });

            $('.action_type_select').each(function() {
                var selectedActionType = $(this).val();
                var stepContainer = $(this).closest('.entire_step');
                adjustTaskInputs(stepContainer, selectedActionType);
            });

            $('#type_select').change(function() {
                var typeId = $(this).val();
                console.log(typeId);
                $.ajax({
                    url: '/ceomanagment/get-tasks-for-type',
                    type: 'GET',
                    data: {
                        typeId: typeId
                    },
                    success: function(response) {
                        var tasksSelect = $('select[name="step[0][tasks][]"]');
                        tasksSelect.empty();
                        tasksSelect.append('<option value="">' + 'Select Task' +
                            '</option>');
                        $.each(response, function(key, value) {
                            tasksSelect.append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                    }
                });
            });

        });
    </script>
@endsection
