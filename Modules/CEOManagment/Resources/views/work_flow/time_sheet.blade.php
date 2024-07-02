@extends('layouts.app')
@section('title', __('ceomanagment::lang.timesheet'))

<style>
    .pyramid {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 20px;
        position: relative;
    }

    .pyramid-step {
        display: flex;
        justify-content: center;
        margin: 20px 0;
        position: relative;
        width: 80%;
    }

    .pyramid-step div {
        margin: 0 10px;
        padding: 15px 20px;
        border: 2px solid #000;
        border-radius: 10px;
        position: relative;
        background-color: #f5f5f5;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        font-weight: bold;
        text-align: center;
        min-width: 150px;
        transition: transform 0.3s;
    }

    .pyramid-step div:hover {
        transform: scale(1.1);
    }

    .pyramid-step div:before {
        content: "";
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-bottom: 10px solid #000;
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
    }

    .pyramid-step:first-child div:before {
        display: none;
    }

    .pyramid-step:nth-child(1) div {
        background-color: #FFDDC1;
    }

    .pyramid-step:nth-child(1) div:before {
        border-bottom-color: #FFDDC1;
    }

    .pyramid-step:nth-child(2) div {
        background-color: #C1FFC1;
    }

    .pyramid-step:nth-child(2) div:before {
        border-bottom-color: #C1FFC1;
    }

    .pyramid-step:nth-child(3) div {
        background-color: #73da73;
    }

    .pyramid-step:nth-child(3) div:before {
        border-bottom-color: #73da73;
    }

    .pyramid-step:nth-child(4) div {
        background-color: #f1d65a;
    }

    .pyramid-step:nth-child(4) div:before {
        border-bottom-color: #f1d65a;
    }

    .arrow {
        width: 2px;
        height: 20px;
        background-color: #000;
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
    }

    .hints {
        position: absolute;
        bottom: 20px;
        left: 20px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .hint-item {
        display: flex;
        align-items: center;
        margin-top: 5px;
        margin-bottom: 5px;
        font-size: 0.9em;
        color: #555;
    }

    .hint-color {
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        border-radius: 5px;
    }

    .hint-color.step1 {
        background-color: #FFDDC1;
    }

    .hint-color.step2 {
        background-color: #C1FFC1;
    }

    .hint-color.step3 {
        background-color: #73da73;
    }

    .hint-color.step4 {
        background-color: #f1d65a;
    }

    .business-name {
        font-size: 1.5em;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
        text-transform: uppercase;
    }

    .edit-button,
    .save-button {
        margin-left: 10px;
        cursor: pointer;
        font-size: 1.2em;
        color: #007bff;
    }

    .remove-button {
        cursor: pointer;
        color: red;
        font-size: 1.2em;
        margin-left: 5px;
    }

    .add-department-button {
        cursor: pointer;
        color: green;
        font-size: 1.2em;
        margin-left: 5px;
    }

    .hidden {
        display: none;
    }
</style>

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('ceomanagment::lang.timesheet_wk')</span>
        </h1>
    </section>
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <button type="button" class="btn btn-block btn-primary" style="width:20%;" data-toggle="modal"
                    data-target="#addWorkflowModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            @endslot
            @foreach ($workflows->groupBy('business_id') as $businessId => $businessWorkflows)
                <div class="business-name">
                    {{ $all_business[$businessId] ?? 'unknown' }}
                    <span class="edit-button" data-business-id="{{ $businessId }}">&#9998;</span>
                    <span class="save-button hidden" data-business-id="{{ $businessId }}">&#10003;</span>
                </div>
                <hr style="width:50px; position:center;">
                <div class="pyramid" data-business-id="{{ $businessId }}">
                    @foreach ($businessWorkflows->groupBy('step_number') as $stepNumber => $workflowGroup)
                        <div class="pyramid-step">
                            @foreach ($workflowGroup as $workflow)
                                <div>
                                    {{ $workflow->department ? $workflow->department->name : ($workflow->clients_allowed ? __('ceomanagment::lang.client') : '') }}
                                    <span class="remove-button hidden" data-id="{{ $workflow->id }}">&#10006;</span>
                                </div>
                            @endforeach
                            <span class="add-department-button hidden" data-step="{{ $stepNumber }}">&#10010;</span>
                        </div>
                    @endforeach
                    <hr>
                </div>
            @endforeach

            <div class="hints">
                @foreach ($workflows->groupBy('step_number') as $stepNumber => $workflowGroup)
                    @if ($workflowGroup->isNotEmpty())
                        <div class="hint-item">
                            <div class="hint-color step{{ $stepNumber }}"></div>
                            @if ($stepNumber == 1)
                                <span style="margin-right: 10px;">Department create the timesheet</span>
                            @elseif ($stepNumber == 2)
                                <span style="margin-right: 10px;">Second departments in the workflow</span>
                            @elseif ($stepNumber == 3)
                                <span style="margin-right: 10px;">Third departments in the workflow</span>
                            @elseif ($stepNumber == 4)
                                <span style="margin-right: 10px;">Fourth departments in the workflow</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endcomponent

        <!-- Add Workflow Modal -->
        <div class="modal fade" id="addWorkflowModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog custom-modal-dialog style">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeTimeSheetProcedure']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_procedure')</h4>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="form-group col-md-12">
                                {!! Form::label('business', __('essentials::lang.company') . ':*') !!}
                                <div class="clearfix"></div>
                                {!! Form::select('business', $business, null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.business'),
                                    'required',
                                    'style' => 'height:40px',
                                    'id' => 'business_id',
                                ]) !!}
                            </div>
                        </div>
                        <div id="departmentsContainer">
                            <div class="form-group col-md-12 department-group">
                                <label for="departments">@lang('essentials::lang.departments')</label>

                                <div class="clearfix"></div>
                                {!! Form::select('steps[1][departments][]', $departments, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'placeholder' => __('ceomanagment::lang.select_departments_can_create_the_time_sheet'),
                                    'multiple' => 'multiple',
                                    'style' => 'height:40px; width:100%;',
                                    'id' => 'departments_1',
                                ]) !!}
                                <div class="clearfix"></div>
                                <label for="clients" style="margin-top: 2px;">@lang('ceomanagment::lang.can_the_customer_add_it?')</label>
                                <input type="checkbox" name="steps[1][clients_allowed]" value="1">
                            </div>
                        </div>


                        <div class="form-group col-md-12">
                            <button type="button" class="btn btn-sm btn-warning my-button addStep" id="add_modal_add_step">
                                @lang('essentials::lang.add_managment')
                            </button>
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
        $(document).ready(function() {
            let stepCount = 1;
            const selectDepartmentsTranslation = @json(__('ceomanagment::lang.select_departments_for_the_step'));


            function updateDepartments(businessId) {
                console.log(businessId);
                console.log('Updating departments for business ID:', businessId);
                if (businessId) {
                    $.ajax({
                        url: '/ceomanagment/getDepartmentsForWk/' + businessId,
                        type: 'GET',
                        success: function(data) {
                            console.log('Departments data:', data);
                            $('[id^=departments_]').each(function() {
                                var departmentsSelect = $(this);
                                departmentsSelect.empty();
                                $.each(data, function(key, value) {
                                    departmentsSelect.append('<option value="' + key +
                                        '">' + value + '</option>');
                                });
                                $('.select2').select2();
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching departments:', error);
                        }
                    });
                } else {
                    console.log('No business ID found.');
                }
            }

            $('#business_id').change(function() {
                var businessId = $(this).val();
                console.log('Business ID changed:', businessId);
                updateDepartments(businessId);
            });

            $(document).on("click", "#add_modal_add_step", function() {
                stepCount++;
                var container = $('#departmentsContainer');
                var departmentGroup = `
                    <div class="form-group col-md-12 department-group">
                        <label for="departments">@lang('ceomanagment::lang.select_departments_for_this_step')</label>
                        {!! Form::select('steps[${stepCount}][departments][]', [], null, [
                            'class' => 'form-control select2',
                            'required',
                            'multiple' => 'multiple',
                            'style' => 'height:40px',
                            'id' => 'departments_${stepCount}',
                        ]) !!}
                        <div class="clearfix"></div>
                        <label for="clients">@lang('ceomanagment::lang.check_to_add_cutomer_to_this_step')</label>
                        <input type="checkbox" name="steps[${stepCount}][clients_allowed]" value="1">
                    </div>
                `;
                container.append(departmentGroup);

                $('.select2').select2();

                var businessId = $('#business_id').val();
                console.log('Adding new step, current business ID:', businessId);
                if (!businessId) {
                    console.error('Business ID not found when adding a new step.');
                }
                updateDepartments(businessId); // Load departments for new step
            });

            $(document).on('click', '.edit-button', function() {
                var businessId = $(this).data('business-id');
                console.log('Edit button clicked, business ID:', businessId);
                var container = $(`.pyramid[data-business-id="${businessId}"]`);
                console.log(container);
                container.find('.remove-button, .add-department-button').removeClass('hidden');
                $(this).addClass('hidden');
                $(`.save-button[data-business-id="${businessId}"]`).removeClass('hidden');
            });

            $(document).on('click', '.save-button', function() {
                var businessId = $(this).data('business-id');
                console.log('Save button clicked, business ID:', businessId);
                var container = $(`.pyramid[data-business-id="${businessId}"]`);
                container.find('.remove-button, .add-department-button').addClass('hidden');
                $(this).addClass('hidden');
                $(`.edit-button[data-business-id="${businessId}"]`).removeClass('hidden');

                var updatedWorkflows = [];
                container.find('.pyramid-step').each(function(stepIndex, stepElement) {
                    $(stepElement).find('select').each(function(departmentIndex, departmentSelect) {
                        var departments = $(departmentSelect).val();
                        if (departments) {
                            departments.forEach(departmentId => {
                                updatedWorkflows.push({
                                    business_id: businessId,
                                    step_number: stepIndex + 1,
                                    department_id: departmentId,
                                    clients_allowed: $(stepElement).find(
                                        'input[type="checkbox"]').is(
                                        ':checked') ? 1 : 0
                                });
                            });
                        }
                    });
                });

                $.ajax({
                    url: '/ceomanagment/updateTimeSheetProcedure',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        workflows: updatedWorkflows
                    },
                    success: function(response) {
                        alert('Workflow updated successfully.');
                    },
                    error: function(xhr) {
                        alert('Failed to update the workflow.');
                    }
                });
            });

            $(document).on('click', '.add-department-button', function() {
                var stepNumber = $(this).data('step');
                var departmentGroup = `
                    <div class="form-group col-md-12 department-group">
                        <label for="departments">@lang('ceomanagment::lang.select_departments_for_this_step')</label>
                        {!! Form::select('steps[${stepNumber}][departments][]', [], null, [
                            'class' => 'form-control select2',
                            'required',
                            'multiple' => 'multiple',
                            'style' => 'height:40px',
                            'id' => 'departments_${stepNumber}_${Math.random()}',
                        ]) !!}
                        <div class="clearfix"></div>
                        <label for="clients">@lang('ceomanagment::lang.check_to_add_cutomer_to_this_step')</label>
                        <input type="checkbox" name="steps[${stepNumber}][clients_allowed]" value="1">
                    </div>
                `;
                $(this).before(departmentGroup);
                $('.select2').select2();

                var businessId = $('#business_id').val();
                console.log('Adding department, current business ID:', businessId);
                if (!businessId) {
                    console.error('Business ID not found when adding a new department.');
                }
                updateDepartments(businessId);
            });

            $(document).on('click', '.remove-button', function() {
                $(this).closest('div').remove();
            });
            $('#business_id').trigger('change');
            $('.select2').select2();
        });
    </script>
@endsection
