@extends('layouts.app')

@section('title', __('essentials::lang.edit_worker'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.edit_worker')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action(
                [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'update'],
                [$user->id],
            ),
            'method' => 'PUT',
            'id' => 'user_edit_form',
        ]) !!}

        <div class="col-md-12 box box-primary">
            <h4>@lang('essentials::lang.basic_info'):</h4>

            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                    {!! Form::text('first_name', $user->first_name, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('business.first_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                    {!! Form::text('mid_name', $user->mid_name, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('business.mid_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('last_name', __('business.last_name') . ':') !!}
                    {!! Form::text('last_name', $user->last_name, [
                        'class' => 'form-control',
                        'placeholder' => __('business.last_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('email', __('business.email') . ':') !!}
                    {!! Form::email('email', $user->email, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                </div>
            </div>


        </div>

        @include('essentials::employee_affairs.workers_affairs.edit_profile_form_part', [
            'bank_details' => !empty($user->bank_details) ? json_decode($user->bank_details, true) : null,
        ])

        <div class="col-md-12 box box-primary" id="section4">

            <h4>@lang('essentials::lang.hrm_details_create_edit'):</h4>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('essentials::lang.company') . ':*') !!}
                    {!! Form::select('location_id', $companies, !empty($user->company_id) ? $user->company_id : null, [
                        'class' => 'form-control select2',
                        'style' => 'height:40px',
                        'required',
                        'placeholder' => __('messages.please_select'),
                    ]) !!}
                </div>
            </div>


            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('profession', __('essentials::lang.job_title') . ':*') !!}
                    {!! Form::select('profession', $job_titles, !empty($user->profession_id) ? $user->profession_id : null, [
                        'class' => 'form-control select2',
                        'required',
                        'style' => 'height:40px',
                        'placeholder' => __('essentials::lang.job_title'),
                        'id' => 'professionSelect',
                    ]) !!}
                </div>
            </div>



        </div>






        <div class="col-md-12 box box-primary" id="section5">
            <h4>@lang('essentials::lang.contract_details_create_edit'):</h4>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('contract_type', __('essentials::lang.contract_type') . ':') !!}
                    {!! Form::select(
                        'contract_type',
                        $contract_types,
                        !empty($contract->contract_type_id) ? $contract->contract_type_id : null,
                        ['class' => 'form-control select', 'style' => 'height:40px', 'placeholder' => __('messages.please_select')],
                    ) !!}
                </div>
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('contract_start_date', __('essentials::lang.contract_start_date') . ':') !!}
                {!! Form::date(
                    'contract_start_date',
                    !empty($contract->contract_start_date) ? $contract->contract_start_date : null,
                    [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'id' => 'contract_start_date',
                        'placeholder' => __('essentials::lang.contract_start_date'),
                    ],
                ) !!}
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('contract_duration', __('essentials::lang.contract_duration') . ':') !!}
                <div class="form-group">
                    <div class="multi-input">
                        <div class="input-group">
                            {!! Form::number(
                                'contract_duration',
                                !empty($contract->contract_duration) ? $contract->contract_duration : null,
                                [
                                    'class' => 'form-control width-40 pull-left',
                                    'style' => 'height:40px',
                                    'id' => 'contract_duration',
                                    // 'placeholder' => __('essentials::lang.contract_duration'),
                                ],
                            ) !!}
                            {!! Form::select(
                                'contract_duration_unit',
                                ['years' => __('essentials::lang.years'), 'months' => __('essentials::lang.months')],
                                !empty($contract->contract_per_period) ? $contract->contract_per_period : null,
                                ['class' => 'form-control width-60 pull-left', 'style' => 'height:40px', 'id' => 'contract_duration_unit'],
                            ) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('contract_end_date', __('essentials::lang.contract_end_date') . ':') !!}
                {!! Form::date('contract_end_date', !empty($contract->contract_end_date) ? $contract->contract_end_date : null, [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'contract_end_date',
                    'placeholder' => __('essentials::lang.contract_end_date'),
                ]) !!}
            </div>
            <div class="clearfix">
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('probation_period', __('essentials::lang.probation_period') . ':') !!}
                {!! Form::text('probation_period', !empty($contract->probation_period) ? $contract->probation_period : null, [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'placeholder' => __('essentials::lang.probation_period_in_days'),
                ]) !!}
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':') !!}
                {!! Form::select(
                    'is_renewable',
                    ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')],
                    !empty($contract->probation_period) ? $contract->probation_period : null,
                    ['class' => 'form-control', 'style' => 'height:40px'],
                ) !!}
            </div>


            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('essentials::lang.contract_file', __('essentials::lang.contract_file') . ':') !!}
                    {!! Form::file('contract_file', [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                    ]) !!}
                </div>

            </div>


        </div>




        <div class="col-md-12 box box-primary" id="section6">

            <h4>@lang('essentials::lang.payroll_create_edit'):</h4>

            <div class="col-md-5">
                <div class="form-group">
                    <table class="table">
                        <thead>
                            <tr>
                                <th> {!! Form::label('essentials_salary', __('essentials::lang.salary') . ':') !!}</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="col-md-8">
                                        {!! Form::number('essentials_salary', !empty($user->essentials_salary) ? $user->essentials_salary : null, [
                                            'class' => 'form-control pull-left',
                                            'style' => 'height:40px',
                                            'placeholder' => __('essentials::lang.salary_per_month'),
                                        ]) !!}
                                    </div>


                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-1">


            </div>


            <div class="col-md-5">
                <div class="form-group">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{!! Form::label('extra_salary_type', __('essentials::lang.extra_salary_type') . ':') !!}</th>
                                <th>{!! Form::label('amount', __('essentials::lang.amount') . ':') !!}</th>

                            </tr>
                        </thead>
                        <tbody id="salary-table-body">
                            <tr>
                                <td>

                                    {!! Form::select('salary_type[]', $allowance_types, null, [
                                        'class' => 'form-control  pull-left',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.extra_salary_type'),
                                    ]) !!}

                                </td>
                                <td>

                                    {!! Form::text('amount[]', null, [
                                        'class' => 'form-control  pull-left',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.amount'),
                                    ]) !!}

                                </td>
                                <td>
                                    <button type="button" id="remove-row"
                                        class="btn btn-danger remove-row">{{ __('messages.delete') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="clearfix">
            </div>
            <div class="col-md-4">
                <button type="button" id="add-row"
                    class="btn btn-primary">{{ __('essentials::lang.add_extry') }}</button>
            </div>
            <div class="clearfix">
            </div>
            <br>
            <div class="col-md-3">
                <div class="form-group">

                    {!! Form::label('total_salary', __('essentials::lang.total_salary') . ':') !!}

                    {!! Form::number('total_salary', !empty($user->total_salary) ? $user->total_salary : null, [
                        'class' => 'form-control pull-left',
                        'style' => 'height:40px',
                        'id' => 'total_salary',
                        'placeholder' => __('essentials::lang.salary'),
                    ]) !!}


                </div>
            </div>
            <div class="clearfix">
            </div>
        </div>



        <div class="col-md-12 box box-primary" id="section7">

            <h4>@lang('essentials::lang.features'):</h4>

            <div>
                <div class="form-group col-md-3">
                    {!! Form::label('can_add_category', __('essentials::lang.travel_categorie') . ':') !!}
                    {!! Form::select(
                        'can_add_category',
                        [
                            '#' => __('essentials::lang.select_for_travel'),
                            '1' => __('essentials::lang.includes'),
                            '0' => __('essentials::lang.does_not_include'),
                        ],
                        !empty($user->employee_travle_categorie) ? ($user->employee_travle_categorie ? '1' : '0') : '0',
                        ['class' => 'form-control', 'style' => 'height:40px'],
                    ) !!}
                </div>
                {{-- <div class="form-group col-md-3" id="category_input" style="display: none;">
                    {!! Form::label('travel_ticket_categorie', __('essentials::lang.travel_ticket_categorie') . ':') !!}
                    {!! Form::select('travel_ticket_categorie', $travel_ticket_categorie, null, [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'placeholder' => __('essentials::lang.travel_ticket_categorie'),
                    ]) !!}
                </div> --}}
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('health_insurance', __('essentials::lang.health_insurance') . ':') !!}
                {!! Form::select(
                    'health_insurance',
                    ['1' => __('essentials::lang.have_an_insurance'), '0' => __('essentials::lang.not_have_an_insurance')],
                    !empty($user->has_insurance) ? $user->has_insurance : null,
                    ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('essentials::lang.health_insurance')],
                ) !!}
            </div>
        </div>
        <input type="hidden" id="selectedData" name="selectedData" value="">







        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-big" id="submit_user_button">@lang('messages.update')</button>
            </div>
        </div>









        {!! Form::close() !!}

    @stop
    @section('javascript')

        <script>
            $(document).ready(function() {
                $('#contract_start_date, #contract_duration, #contract_duration_unit').change(function() {
                    updateContractEndDate();
                });

                function updateContractEndDate() {
                    var startDate = $('#contract_start_date').val();
                    var duration = $('#contract_duration').val();
                    var unit = $('#contract_duration_unit').val();

                    if (startDate && duration && unit) {
                        var newEndDate = calculateEndDate(startDate, duration, unit);
                        $('#contract_end_date').val(newEndDate);
                    }
                }

                function calculateEndDate(startDate, duration, unit) {
                    var startDateObj = new Date(startDate);
                    var endDateObj = new Date(startDateObj);

                    if (unit === 'years') {
                        endDateObj.setFullYear(startDateObj.getFullYear() + parseInt(duration));
                    } else if (unit === 'months') {
                        endDateObj.setMonth(startDateObj.getMonth() + parseInt(duration));
                    }

                    return endDateObj.toISOString().split('T')[0];
                }
            });
        </script>


        <script>
            $(document).ready(function() {
                $('#contract_start_date, #contract_duration, #contract_duration_unit').change(function() {
                    updateContractEndDate();
                });

                $('#contract_end_date').change(function() {
                    validateAndUpdateDuration();
                });

                function updateContractEndDate() {
                    var startDate = $('#contract_start_date').val();
                    var duration = $('#contract_duration').val();
                    var unit = $('#contract_duration_unit').val();

                    if (startDate && duration && unit) {
                        var newEndDate = calculateEndDate(startDate, duration, unit);
                        $('#contract_end_date').val(newEndDate);
                    }
                }

                function validateAndUpdateDuration() {
                    var startDate = $('#contract_start_date').val();
                    var endDate = $('#contract_end_date').val();
                    var unit = $('#contract_duration_unit').val();

                    if (startDate && endDate && unit) {
                        var calculatedDuration = calculateDuration(startDate, endDate, unit);
                        $('#contract_duration').val(calculatedDuration);
                    }
                }

                function calculateEndDate(startDate, duration, unit) {
                    var startDateObj = new Date(startDate);
                    var endDateObj = new Date(startDateObj);

                    if (unit === 'years') {
                        endDateObj.setFullYear(startDateObj.getFullYear() + parseInt(duration));
                    } else if (unit === 'months') {
                        endDateObj.setMonth(startDateObj.getMonth() + parseInt(duration));
                    }

                    return endDateObj.toISOString().split('T')[0];
                }

                function calculateDuration(startDate, endDate, unit) {
                    var startDateObj = new Date(startDate);
                    var endDateObj = new Date(endDate);
                    var diff;

                    if (unit === 'years') {
                        diff = endDateObj.getFullYear() - startDateObj.getFullYear();
                    } else if (unit === 'months') {
                        diff = (endDateObj.getFullYear() - startDateObj.getFullYear()) * 12 + endDateObj.getMonth() -
                            startDateObj.getMonth();
                    }

                    return diff;
                }
            });
        </script>


        <script>
            $(document).ready(function() {
                $('#contract_start_date, #contract_end_date').change(function() {
                    var startDate = $('#contract_start_date').val();
                    var endDate = $('#contract_end_date').val();

                    if (startDate && endDate) {
                        var start = new Date(startDate);
                        var end = new Date(endDate);

                        var monthsDiff = (end.getFullYear() - start.getFullYear()) * 12 + end.getMonth() - start
                            .getMonth();

                        if (monthsDiff < 12) {
                            $('#contract_duration').val(monthsDiff);
                            $('#contract_duration_unit').val('months');
                        } else {
                            var yearsDiff = Math.floor(monthsDiff / 12);
                            $('#contract_duration').val(yearsDiff);
                            $('#contract_duration_unit').val('years');
                        }
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {

                function calculateTotalSalary() {
                    var essentialsSalary = parseFloat($('#essentials_salary').val()) || 0;
                    var totalAllowance = 0;


                    $('input[name="amount[]"]').each(function() {
                        totalAllowance += parseFloat($(this).val()) || 0;
                    });


                    var totalSalary = essentialsSalary + totalAllowance;


                    $('#total_salary').val(totalSalary);
                }

                calculateTotalSalary();

                $('#essentials_salary').on('input', calculateTotalSalary);

                $(document).on('input', 'input[name="amount[]"]', calculateTotalSalary);


                var selectedData = [];
                var professionSelect = $('#professionSelect');
                var specializationSelect = $('#specializationSelect');
                professionSelect.on('change', function() {
                    var selectedProfession = $(this).val();
                    console.log(selectedProfession);
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '{{ route('specializations') }}',
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            profession_id: selectedProfession
                        },
                        success: function(data) {
                            specializationSelect.empty();
                            $.each(data, function(id, name) {
                                specializationSelect.append($('<option>', {
                                    value: id,
                                    text: name
                                }));
                            });
                        }
                    });
                });


                $('#can_add_category').change(function() {
                    if (this.value === '1') {
                        $('#category_input').show();
                    } else {
                        $('#category_input').hide();
                    }
                });
                var allowanceDeductionIds = @json($allowance_deduction_ids);

                if (Array.isArray(allowanceDeductionIds) && allowanceDeductionIds.length > 0) {
                    allowanceDeductionIds.forEach(function(item) {
                        var newRow = $('#salary-table-body tr:first').clone();
                        newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
                        newRow.find('input[name="amount[]"]').attr('name', 'amount[]');

                        newRow.find('select[name="salary_type[]"]').val(item.essentials_allowance_and_deduction
                            .id);
                        newRow.find('input[name="amount[]"]').val(item.amount);
                        $('#salary-table-body').append(newRow);
                    });

                    $('#salary-table-body tr:first').remove();
                }
                $('#salary-table-body tr:first').find('.remove-row').remove();
                $('#salary-table-body').on('click', '.remove-row', function() {
                    $(this).closest('tr').remove();
                });

                $('#add-row').click(function() {
                    var newRow = $('#salary-table-body tr:first').clone();
                    newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
                    newRow.find('input[name="amount[]"]').attr('name', 'amount[]');
                    $('#salary-table-body').append(newRow);
                });

                $(document).on('change', 'select[name="salary_type[]"]', function() {
                    updateSelectedData();
                });

                $(document).on('input', 'input[name="amount[]"]', function() {
                    updateSelectedData();
                });

                function updateSelectedData() {
                    selectedData = [];

                    $('select[name="salary_type[]"]').each(function(index) {
                        var salaryType = $(this).val();
                        var amount = parseFloat($('input[name="amount[]"]').eq(index).val());
                        selectedData.push({
                            salaryType: salaryType,
                            amount: amount
                        });
                    });

                    console.log(selectedData);
                    var inputElement = document.getElementById('selectedData');
                    inputElement.value = JSON.stringify(selectedData);
                    calculateTotalSalary();
                }


                function updateAmount(element) {
                    var salaryType = $(element).val();
                    console.log(salaryType);

                    $.ajax({
                        url: '/hrm/get-amount/' + salaryType,
                        type: 'GET',
                        success: function(response) {

                            var amountInput = $(element).closest('tr').find('input[name="amount[]"]');
                            amountInput.val(response ? response.amount : 0);
                            updateSelectedData();
                            calculateTotalSalary();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }



                $(document).on('change', 'select[name="salary_type[]"]', function() {
                    updateAmount(this);
                });
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                __page_leave_confirmation('#user_edit_form');

                $('#selected_contacts').on('ifChecked', function(event) {
                    $('div.selected_contacts_div').removeClass('hide');
                });
                $('#selected_contacts').on('ifUnchecked', function(event) {
                    $('div.selected_contacts_div').addClass('hide');
                });
                $('#allow_login').on('ifChecked', function(event) {
                    $('div.user_auth_fields').removeClass('hide');
                });
                $('#allow_login').on('ifUnchecked', function(event) {
                    $('div.user_auth_fields').addClass('hide');
                });

                $('#user_allowed_contacts').select2({
                    ajax: {
                        url: '/contacts/customers',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term, // search term
                                page: params.page,
                                all_contact: true
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data,
                            };
                        },
                    },
                    templateResult: function(data) {
                        var template = '';
                        if (data.supplier_business_name) {
                            template += data.supplier_business_name + "<br>";
                        }
                        template += data.text + "<br>" + LANG.mobile + ": " + data.mobile;

                        return template;
                    },
                    minimumInputLength: 1,
                    escapeMarkup: function(markup) {
                        return markup;
                    },
                });
            });

            $('form#user_edit_form').validate({
                rules: {
                    first_name: {
                        required: true,
                    },
                    email: {
                        email: true,
                        remote: {
                            url: "/business/register/check-email",
                            type: "post",
                            data: {
                                email: function() {
                                    return $("#email").val();
                                },
                                user_id: {{ $user->id }}
                            }
                        }
                    },
                    password: {
                        minlength: 5
                    },
                    confirm_password: {
                        equalTo: "#password",
                    },
                    username: {
                        minlength: 5,
                        remote: {
                            url: "/business/register/check-username",
                            type: "post",
                            data: {
                                username: function() {
                                    return $("#username").val();
                                },
                            }
                        }
                    }
                },
                messages: {
                    password: {
                        minlength: 'Password should be minimum 5 characters',
                    },
                    confirm_password: {
                        equalTo: 'Should be same as password'
                    },
                    username: {
                        remote: 'Invalid username or User already exist'
                    },
                    email: {
                        remote: '{{ __('validation.unique', ['attribute' => __('business.email')]) }}'
                    }
                }
            });
        </script>
    @endsection
