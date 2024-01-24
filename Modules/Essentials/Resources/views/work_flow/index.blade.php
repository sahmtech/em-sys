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
                    {!! Form::open(['route' => 'storeProcedure']) !!}
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
                                        array_combine($missingTypes, array_map(fn($type) => trans("followup::lang.$type"), $missingTypes)),
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'id' => 'type_select',
                                            'placeholder' => __('essentials::lang.procedure_type'),
                                            'required',
                                            'style' => 'height:35px',
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

                                    <div class="form-group col-md-12 entire_step" id="step_0" style="display:none">
                                        <div class="form-group col-md-6">
                                            {!! Form::label('add_modal_department_id_steps', __('essentials::lang.managment') . ':*') !!}
                                            {!! Form::select('add_modal_department_id_steps[]', $departments, null, [
                                                'class' => 'form-control departments pull-right',
                                                'required',
                                                'placeholder' => __('essentials::lang.selectDepartment'),
                                                'style' => 'height:40px',
                                            ]) !!}
                                        </div>

                                        <div class="clearfix"></div>
                                        <div class="form-group col-md-4">
                                            <div class="checkbox">
                                                <label>

                                                    {!! Form::checkbox('add_modal_can_reject_steps[]', 1, null, []) !!}{{ __('essentials::lang.can_reject') }}

                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <div class="checkbox">
                                                <label>

                                                    {!! Form::checkbox('add_modal_can_return_steps[]', 1, null, []) !!}{{ __('essentials::lang.can_return') }}
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
                                                    {!! Form::select('add_modal_escalates_to_steps[]', $escalates_departments, null, [
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
                                                        {!! Form::number('add_modal_escalates_after_steps[]', null, [
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
                    {!! Form::open(['route' => 'storeProcedure']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.edit_procedure')</h4>
                    </div>

                    <div class="modal-body">
                        <div>
                            <div class="row stepsClass">
                                <div class="workflow-step">

                                    <div class="form-group col-md-6">
                                        {!! Form::label('department_id', __('essentials::lang.managment') . ':*') !!}
                                        {!! Form::select('department_id', $departments, null, [
                                            'class' => 'form-control select2',
                                            'name' => 'steps[0][department_id][]',
                                            'required',
                                            'placeholder' => __('essentials::lang.selectDepartment'),
                                            'multiple' => 'multiple',
                                            'style' => 'height:40px',
                                        ]) !!}
                                    </div>

                                    <div class="clearfix"></div>
                                    <div class="form-group col-md-12 hidden-step-details">
                                        <div class="col-md-12">
                                            <div class="col-md-3">
                                                <label>@lang('essentials::lang.can_reject') </label>
                                                <div class="radio">
                                                    <label>
                                                        {!! Form::radio('steps[0][can_reject]', 1, false, ['class' => 'isRejectCheckbox']) !!} @lang('essentials::lang.yes')
                                                    </label>
                                                    <label>
                                                        {!! Form::radio('steps[0][can_reject]', 0, true, ['class' => 'isRejectCheckbox']) !!} @lang('essentials::lang.no')
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>@lang('essentials::lang.can_return')</label>
                                                <div class="radio">
                                                    <label>
                                                        {!! Form::radio('steps[0][can_return]', 1, false, ['class' => 'isReturnCheckbox']) !!} @lang('essentials::lang.yes')
                                                    </label>
                                                    <label>
                                                        {!! Form::radio('steps[0][can_return]', 0, true, ['class' => 'isReturnCheckbox']) !!}@lang('essentials::lang.no')
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="escalations-container">

                                            <div class="escalation-field-template col-md-12">
                                                <div class="form-group col-md-4">
                                                    {!! Form::label('escalates_to', __('essentials::lang.escalates_to') . ':') !!}
                                                    {!! Form::select('escalates_to', $escalates_departments, null, [
                                                        'class' => 'form-control push',
                                                        'name' => 'steps[0][escalates_to][]',
                                                        'placeholder' => __('essentials::lang.escalates_to'),
                                                        'style' => 'height:40px',
                                                    ]) !!}
                                                </div>
                                                <div class="form-group col-md-4">
                                                    {!! Form::label(
                                                        'escalates_after',
                                                        __('essentials::lang.escalates_after') . ' (' . __('essentials::lang.in_hours') . ')' . ':',
                                                    ) !!}

                                                    <div class="input-group">
                                                        {!! Form::number('escalates_after', null, [
                                                            'class' => 'form-control',
                                                            'name' => 'steps[0][escalates_after][]',
                                                            'placeholder' => __('essentials::lang.escalates_after'),
                                                            'style' => 'height:40px',
                                                        ]) !!}

                                                    </div>
                                                </div>

                                                <div class="form-group col-md-4 ">

                                                    <button type="button" class="btn btn-sm btn-success add-escalation">
                                                        @lang('essentials::lang.add_escalation')
                                                    </button>


                                                </div>


                                                <div class="additional-escalations"></div>

                                            </div>
                                        </div>


                                    </div>


                                </div>
                                <br>

                            </div>
                            <div>
                                <button type="button"
                                    class="btn btn-sm btn-warning my-button addStep">@lang('essentials::lang.add_managment')</button>
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
                    url: "{{ route('procedures') }}",

                },
                columns: [

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
            /////////////////////////////start////////////////////////////////////////////////////////////////////////
            let add_modal_steps_count = 0;

            $(document).on("click", ".add_modal_remove_step_btn", function() {
                // Find the closest entire_step div and remove it
                var stepDiv = $(this).closest('.entire_step');

                // Check if there's more than one step before removing
                stepDiv.remove();
                // Optional: Alert the user or handle the case where the last step cannot be removed
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

            $(document).on("click", "#add_modal_add_step", function() {

                var stepZero = $("#step_0");
                stepZero.find('.escalation-field-template').not(':first').remove();
                var newStep = stepZero.clone();
                add_modal_steps_count++;

                newStep.attr('id', 'step_' + add_modal_steps_count);

                newStep.css('display', 'block');

                $("#workflow-step_add_modal").append(newStep);
                newStep.find('.departments').select2({
                    dropdownParent: $(
                        '#addProceduresModal'),
                    width: '100%',
                });
                // Reinitialize components if needed (e.g., select2)


            });
            ///////////////////////////////end/////////////////////////////////////////////////////////////



            $(".addSasdsatep").on("click", function() {
                let newStepIndex = $(" #workflow-step_add_modal").length;
                let newStep = $("#workflow-step_add_modal:last").clone(false);
                newStep.find('.hidden-step-details').removeClass('hidden-step-details');
                newStep.find('input, select').each(function() {

                    if ($(this).is(':text')) {
                        $(this).val('');
                    }

                    if ($(this).is(':radio')) {
                        $(this).prop('checked', false);
                    }
                    if ($(this).is('input[type="number"]')) {
                        $(this).val('');
                    }
                    if ($(this).is('select')) {

                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }

                        $(this).next('.select2-container').remove();

                        $(this).val('');

                        $(this).removeAttr('multiple');
                    }

                    let name = $(this).attr("name");

                    if (name) {
                        let newName = name.replace(/\[\d+\]/, `[${newStepIndex}]`);
                        $(this).attr("name", newName).attr('id', newName);
                    }
                    let newAdditionalEscalationsContainer = newStep.find('.additional-escalations');
                    newAdditionalEscalationsContainer.empty();
                });

                newStep.find('.add-escalation').show();
                newStep.find('.remove-step').remove();
                newStep.append(
                    '<div class="col-md-12"><button type="button" class="btn btn-danger btn-sm remove-step">@lang('essentials::lang.remove_department')</button></div>'
                );

                $(".stepsClass").append(newStep);

                newStep.find('.remove-step').on("click", function() {

                    $(this).closest('.workflow-step').remove();
                });
                newStep.find('.select2').select2({
                    width: '100%'
                });

                if (newStepIndex === 0) {
                    $('.stepsClass .workflow-step:first .select2').select2({
                        multiple: true,
                        width: '100%'
                    });
                }


            });


            $(document).on('click', '.add-escalation', function() {

                let escalationRow = $(this).closest('.escalations-container').find(
                    '.escalation-field-template').first().clone();
                escalationRow.find('input, select').val('');
                escalationRow.append(
                    '<div class="col-md-12"><button type="button" class="btn btn-sm btn-danger remove-escalation">@lang('essentials::lang.remove_escalation')</button></div>'
                );
                $(this).closest('.escalations-container').find('.additional-escalations').append(
                    escalationRow);
                escalationRow.find('.add-escalation').remove();
                $(this).closest('.add-escalation').hide();
            });


            $(document).on('click', '.remove-escalation', function() {
                var $container = $(this).closest('.escalations-container');
                $(this).closest('.escalation-field-template').remove();


                if ($container.find('.additional-escalations').children().length === 0) {

                    $container.find('.add-escalation').show();
                }
            });

            $('.stepsClass').on('click', '.remove-step', function() {
                $(this).closest('.workflow-step').remove();
            });

            $(document).on('click', '.edit-procedure', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var procedure_id = $(this).data('id');


                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var procedures = typeof response.procedures === 'string' ? JSON.parse(
                            response.procedures) : response.procedures;
                        var startingDepartments = procedures.filter(function(procedure) {
                            return procedure.start === 1;
                        }).map(function(procedure) {
                            return procedure.department_id;
                        });
                        if (procedures.length > 0) {

                            $('select[name^="steps["]').val([]).trigger('change');
                            $('input[name^="steps["]').prop('checked', false);


                            var startingDepartments = procedures.filter(function(procedure) {
                                return procedure.start === 1;
                            }).map(function(procedure) {
                                return procedure.department_id;
                            });

                            $('select[name="steps[0][department_id][]"]').attr('multiple',
                                'multiple').val(startingDepartments).trigger('change');

                            procedures.forEach(function(procedure, index) {
                                if (procedure.start !== 1) {
                                    var newStep = addNewStep();
                                    var stepIndex = index;
                                    console.log(procedure);

                                    newStep.find('select[name^="steps["]').val(procedure
                                        .department_id).trigger('change');
                                    console.log(procedure.can_reject);
                                    var canReject = procedure.can_reject;

                                    if (canReject == 1) {
                                        console.log(canReject);
                                        $('input[name="steps[' + index +
                                                '][can_reject]"]').prop('checked', true)
                                            .trigger('change');
                                    } else {
                                        $('input[name="steps[' + index +
                                            '][can_reject]"]').prop('checked',
                                            false).trigger('change');
                                    }

                                    var canReturn = procedure.can_return;
                                    if (canReturn == 1) {
                                        console.log(canReturn);
                                        $('input[name="steps[' + index +
                                                '][can_return]"]').prop('checked', true)
                                            .trigger('change');
                                    } else {
                                        $('input[name="steps[' + index +
                                            '][can_return]"]').prop('checked',
                                            false).trigger('change');
                                    }


                                }
                            });

                            $('#editProceduresModal').modal('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error in AJAX request:", error);
                    }
                });

            });

            function addNewStep() {


                let newStepIndex = $(".stepsClass .workflow-step").length;
                let newStep = $(".workflow-step:last").clone(false);
                newStep.find('.hidden-step-details').removeClass('hidden-step-details');
                newStep.find('input, select').each(function() {

                    if ($(this).is(':text')) {
                        $(this).val('');
                    }

                    if ($(this).is(':radio')) {
                        $(this).prop('checked', false);
                    }
                    if ($(this).is('input[type="number"]')) {
                        $(this).val('');
                    }
                    if ($(this).is('select')) {

                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }

                        $(this).next('.select2-container').remove();

                        $(this).val('');

                        $(this).removeAttr('multiple');
                    }

                    let name = $(this).attr("name");

                    if (name) {
                        let newName = name.replace(/\[\d+\]/, `[${newStepIndex}]`);
                        $(this).attr("name", newName).attr('id', newName);
                    }
                    let newAdditionalEscalationsContainer = newStep.find('.additional-escalations');
                    newAdditionalEscalationsContainer.empty();
                });

                newStep.find('.add-escalation').show();
                newStep.find('.remove-step').remove();
                newStep.append(
                    '<div class="col-md-12"><button type="button" class="btn btn-danger btn-sm remove-step">@lang('essentials::lang.remove_department')</button></div>'
                );

                $(".stepsClass").append(newStep);

                newStep.find('.remove-step').on("click", function() {

                    $(this).closest('.workflow-step').remove();
                });
                newStep.find('.select2').select2({
                    width: '100%'
                });

                if (newStepIndex === 0) {
                    $('.stepsClass .workflow-step:first .select2').select2({
                        multiple: true,
                        width: '100%'
                    });
                }
                return newStep;
            }



        });
    </script>
@endsection
