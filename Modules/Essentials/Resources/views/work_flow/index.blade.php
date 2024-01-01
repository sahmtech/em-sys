@extends('layouts.app')
@section('title', __('essentials::lang.procedures'))
<style>
    .custom-modal-dialog.style {
        width: 72% !important;
      
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
                            <div id="procedureSteps" class="row">

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
                                            'required', 'style' => 'height:40px',
                                        ],
                                    ) !!}
                                </div>
                                <div class="clearfix"></div>
                                <div class="workflow-step" class="row">
                                    
                                    <div class="form-group col-md-2" style="width: 200px;">
                                        {!! Form::label('department_id', __('essentials::lang.managment') . ':*') !!}
                                        {!! Form::select('department_id', $departments, null, [
                                            'class' => 'form-control',
                                            'name' => 'steps[0][department_id]',
                                            'placeholder' => __('essentials::lang.managment'),
                                            'required',
                                            'style' => 'height:40px',
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>@lang('essentials::lang.can_reject') </label>
                                        <div class="radio">
                                            <label>
                                                {!! Form::radio('steps[0][can_reject]', 1, false, ['class' => 'isRejectCheckbox']) !!} Yes
                                            </label>
                                            <label>
                                                {!! Form::radio('steps[0][can_reject]', 0, true, ['class' => 'isRejectCheckbox']) !!} No
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>@lang('essentials::lang.can_return')</label>
                                        <div class="radio">
                                            <label>
                                                {!! Form::radio('steps[0][can_return]', 1, false, ['class' => 'isReturnCheckbox']) !!} Yes
                                            </label>
                                            <label>
                                                {!! Form::radio('steps[0][can_return]', 0, true, ['class' => 'isReturnCheckbox']) !!} No
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-2" style="width: 200px;">
                                        {!! Form::label('escalates_to', __('essentials::lang.escalates_to') . ':') !!}
                                        {!! Form::select('escalates_to', $departments, null, [
                                            'class' => 'form-control',
                                            'name' => 'steps[0][escalates_to]',
                                            'placeholder' => __('essentials::lang.escalates_to'),
                                            'style' => 'height:40px',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {!! Form::label('escalates_after', __('essentials::lang.escalates_after') . ' (' . __('essentials::lang.in_hours') . ')' . ':') !!}
                                        
                                        <div class="input-group">
                                            {!! Form::number('escalates_after', null, [
                                                'class' => 'form-control',
                                                'name' => 'steps[0][escalates_after]',
                                                'placeholder' => __('essentials::lang.escalates_after'),
                                                'style' => 'height:36px',
                                            ]) !!}
                                           
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                   

                            </div>

                            <button type="button" id="addStep">@lang('essentials::lang.add_managment')</button>
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
            $("#addStep").on("click", function() {

                let newStep = $(".workflow-step:last").clone();
                newStep.find("input, select").each(function() {
                    let name = $(this).attr("name");
                    name = name.replace(/\[(\d+)\]/, function(match, p1) {
                        return "[" + (parseInt(p1) + 1) + "]";
                    });
                    $(this).attr("name", name);


                });

                $("#procedureSteps").append(newStep);
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
                    {
                        data: 'action'
                    },


                ]
            });
            $(document).on('click', 'button.delete_procedure_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_city,
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



        });
    </script>
@endsection
