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
                    {!! Form::open(['route' => 'storeWorkerProcedure']) !!}
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
                                            'style' => 'width:100%;',
                                        ],
                                    ) !!}
                                </div>
                                
                                <div class="clearfix"></div>


                                <div class="form-group col-md-6">
                                    {!! Form::label('add_modal_department_id_start', __('essentials::lang.managment') . ':*') !!}
                                    {!! Form::select('add_modal_department_id_start[]', $departments, null, [
                                        'class' => 'form-control select2',
                                        'id' => 'add_modal_department_id_start',
                                        'required',
                                        'placeholder' => __('essentials::lang.selectDepartment'),
                                        'multiple' => 'multiple',
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                                <div id="workflow-step_add_modal">

                                    <div class="form-group col-md-12 entire_step" id="add_modal_step_0"
                                        style="display:none">
                                        <div class="form-group col-md-6">
                                            {!! Form::label('add_modal_department_id_steps', __('essentials::lang.managment') . ':*') !!}
                                            {!! Form::select('step[0][add_modal_department_id_steps][]', $departments, null, [
                                                'class' => 'form-control departments pull-right',
                                                'id' => 'add_modal_select_step_0',
                                                'required',
                                                'placeholder' => __('essentials::lang.selectDepartment'),
                                                'style' => 'height:40px',
                                            ]) !!}
                                        </div>

                                        <div class="clearfix"></div>
                                        <div class="form-group col-md-4">
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
                                            <button type="button"id="add_modal_remove_step_btn_0"
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


                                <div class="clearfix"></div>


                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_modal_department_id_start', __('essentials::lang.managment') . ':*') !!}
                                    {!! Form::select('edit_modal_department_id_start[]', $departments, null, [
                                        'class' => 'form-control select2',
                                        'id' => 'edit_modal_department_id_start',
                                        'required',
                                        'placeholder' => __('essentials::lang.selectDepartment'),
                                        'multiple' => 'multiple',
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                                <div id="workflow-step_edit_modal">

                                    <div class="form-group col-md-12 entire_step" id="edit_modal_step_0"
                                        style="display:none">
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

                                        <div class="clearfix"></div>
                                        <div class="form-group col-md-4">
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
                                            <button type="button"id="edit_modal_remove_step_btn_0"
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


            var procedures_table = $('#procedures_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('workersProcedures') }}",

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
                            }   else if (data === 'assetRequest') {
                                return '@lang('request.assetRequest')';
                            }else if (data === 'passportRenewal') {
                                return '@lang('request.passportRenewal')';
                            }else {
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
                                    procedures_table.ajax.reload();
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
                var clone = $(this).closest('.entire_step').find('.escalation-field-template').first()
                    .clone();

                clone.find('input').val('');
                clone.find('select').val('');
                $(this).closest('.entire_step').find('.escalations-container').append(clone);
            });

            $(document).on("click", ".add_modal_remove_escalation_steps_btn", function() {
                var entireStep = $(this).closest('.entire_step');
                var numberOfTemplates = entireStep.find('.escalation-field-template').length;
                if (numberOfTemplates > 1) {
                    $(this).closest('.escalation-field-template').remove();
                }
            });
            let stepZeroVisible = false;
            $(document).on("click", "#add_modal_add_step", function() {
                if (!stepZeroVisible) {
                    $("#add_modal_step_0").css('display', 'block');
                    stepZeroVisible = true;
                } else {
                    var stepZero = $("#add_modal_step_0");
                    stepZero.find('.escalation-field-template').not(':first').remove();
                    var newStep = stepZero.clone();
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
                    newStep.css('display', 'block');
                    newStep.find('.escalation-field-template').find('input').val(
                        '');
                    newStep.find('.escalation-field-template').find('select').val(
                        '');
                    $("#workflow-step_add_modal").append(newStep);
                }
            });


            /////////////////////////////end/////////////////////////////////////////////////////////

            /////////////////////////////start edit///////////////////////////////////////////////////////
            let edit_modal_steps_count = 0;
            $(document).on("click", ".edit_modal_remove_step_btn", function() {
                var stepDiv = $(this).closest('.entire_step');
                stepDiv.remove();
            });
            $(document).on("click", ".edit_modal_add_escalation_steps_btn", function() {
                var clone = $(this).closest('.entire_step').find('.escalation-field-template').first()
                    .clone();
                clone.find('input').val('');
                clone.find('select').val('');
                $(this).closest('.entire_step').find('.escalations-container').append(clone);
            });

            $(document).on("click", ".edit_modal_remove_escalation_steps_btn", function() {
                var entireStep = $(this).closest('.entire_step');
                var numberOfTemplates = entireStep.find('.escalation-field-template').length;
                if (numberOfTemplates > 1) {
                    $(this).closest('.escalation-field-template').remove();
                }
            });
            let editStepZeroVisible = false;
            $(document).on("click", "#edit_modal_add_step", function() {
                if (!editStepZeroVisible) {
                    $("#edit_modal_step_0").css('display', 'block');
                    editStepZeroVisible = true;
                } else {
                    var stepZero = $("#edit_modal_step_0");
                    stepZero.find('.escalation-field-template').not(':first').remove();
                    var newStep = stepZero.clone();
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
                    $("#workflow-step_edit_modal").append(newStep);
                }
            });





            function clearEditModal() {
                $('#editProceduresModal input[type="text"], #editProceduresModal textarea').val('');
                $('#editProceduresModal select').val('').trigger('change');
                $('#editProceduresModal .entire_step').not('#edit_modal_step_0').remove();
                $('#edit_modal_step_0').css('display', 'none');
            }

            $(document).on('click', '.edit-procedure', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var procedureId = $(this).data('id');
                var updateUrl = "{{ route('updateProcedure', ['id' => ':id']) }}".replace(':id', procedureId);; // Construct the update URL

                // Set the action of the form
                $('#editProcedureForm').attr('action', updateUrl);


                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        clearEditModal();
                        var procedures = typeof response.procedures === 'string' ? JSON.parse(
                            response.procedures) : response.procedures;
                        var startingDepartments = procedures.filter(function(procedure) {
                            return procedure.start === 1;
                        }).map(function(procedure) {
                            return procedure.department_id;
                        });
                        var otherDepartments = procedures.filter(function(procedure) {
                            return procedure.start !== 1;
                        }).map(function(procedure) {
                            return procedure;
                        });
                        if (procedures.length > 0) {
                            $('#edit_modal_department_id_start').val(startingDepartments)
                                .trigger('change');
                            otherDepartments.forEach(function(procedure, index) {
                                if (index === 0) {
                                    $('#edit_modal_step_0').css('display', 'block');
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

                    // Clone and populate additional escalations if they exist
                    var escalationsContainer = $(stepSelector).find('.escalations-container');
                    if (stepData.escalations.length > 1) {
                        stepData.escalations.slice(1).forEach(function(escalation, escalationIndex) {
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

                            // Append the cloned and populated escalation
                            escalationsContainer.append(escalationClone);
                        });
                    }

                    // Reinitialize any components that need it, such as select2
                    $(stepSelector).find('.select2').select2(); // Adjust as necessary
                }





                function addStepToEditModal(stepData, stepIndex) {

                    var stepTemplate = $('#edit_modal_step_0').clone();


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

                    populateStepData(stepTemplate, stepIndex, stepData);
                    stepTemplate.css('display', 'block');
                    $('#workflow-step_edit_modal').append(stepTemplate);
                }

            });
            /////////////////////////////end/////////////////////////////////////////////////////////




        });
    </script>
@endsection