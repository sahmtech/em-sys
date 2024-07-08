<div class="col-md-12 box box-primary" id="section4">

    <h4>@lang('essentials::lang.hrm_details_create_edit'):</h4>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('company_id', __('essentials::lang.company') . ':*') !!}
            {!! Form::select('company_id', $company, null, [
                'class' => 'form-control select2',
                'style' => 'height:40px',
                'required',
                'placeholder' => __('messages.please_select'),
            ]) !!}
        </div>
    </div>



    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
            {!! Form::select('profession', $job_titles, !empty($user->profession_id) ? $user->profession_id : null, [
                'class' => 'form-control select2',
                'required',
                'style' => 'height:40px',
                'placeholder' => __('sales::lang.profession'),
            ]) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('shift', __('essentials::lang.shift_time') . ':*') !!}
            {!! Form::select('shift[]', $shifts, !empty($user->shift_id) ? $user->shift_id : null, [
                'class' => 'form-control select2',
                'required',
                'multiple',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.shift_time'),
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


    <div class="clearfix"></div>
    <br>
    <div class="form-group col-md-3">
        <button type="button" class="btn btn-success align-self-center contract_attachements_btn"
            id="ContractFileLink">
            {{ __('essentials::lang.contract_file') }}
        </button>

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
                            <button type="button" id="remove-row" style="display: none;"
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
        <button type="button" id="add-row" class="btn btn-primary">{{ __('essentials::lang.add_extry') }}</button>
    </div>
    <div class="clearfix">
    </div>
    <br>
    <div class="col-md-3">
        <div class="form-group">

            {!! Form::label('total_salary', __('essentials::lang.total_salary') . ':') !!}

            {!! Form::number('total_salary', !empty($user->total_salary) ? $user->total_salary : null, [
                'class' => 'form-control ',
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
            <select id="can_add_category" name="can_add_category" class="form-control" style="height:40px">
                @isset($user)
                    <option value="#">@lang('essentials::lang.select_for_travel')</option>
                    <option value="1" @selected(is_null($user_travel))>@lang('essentials::lang.includes')</option>
                    <option value="0" @selected(!is_null($user_travel))>@lang('essentials::lang.does_not_include')</option>
                @else
                    <option value="#" selected>@lang('essentials::lang.select_for_travel')</option>
                    <option value="1">@lang('essentials::lang.includes')</option>
                    <option value="0">@lang('essentials::lang.does_not_include')</option>
                @endisset
            </select>
        </div>

        <div class="form-group col-md-3" id="category_input" style="display: none;">
            {!! Form::label('travel_ticket_categorie', __('essentials::lang.travel_ticket_categorie') . ':') !!}
            {!! Form::select('travel_ticket_categorie', $travel_ticket_categorie, null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.travel_ticket_categorie'),
            ]) !!}
        </div>
    </div>

    <div class="form-group col-md-3">
        {!! Form::label('health_insurance', __('essentials::lang.health_insurance') . ':') !!}
        {!! Form::select(
            'health_insurance',
            ['1' => __('essentials::lang.have_an_insurance'), '0' => __('essentials::lang.not_have_an_insurance')],
            $user->has_insurance ?? null,
            ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('essentials::lang.health_insurance')],
        ) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::label('max_anuual_leave_days', __('essentials::lang.max_anuual_leave_days') . ':') !!}
        {!! Form::select(
            'max_anuual_leave_days',
            ['31' => __('essentials::lang.31_days'), '21' => __('essentials::lang.21_days')],
            $user->max_anuual_leave_days ?? null,
            [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.max_anuual_leave_days'),
            ],
        ) !!}
    </div>
</div>
<input type="hidden" id="selectedData" name="selectedData" value="">




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            // Subtract 1 day
            endDateObj.setDate(endDateObj.getDate() - 1);

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

        $('#salary-table-body').on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#add-row').click(function() {
            var newRow = $('#salary-table-body tr:first').clone();
            newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
            newRow.find('input[name="amount[]"]').attr('name', 'amount[]');
            newRow.find('.remove-row').show();
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
<script>
    $(document).ready(function() {

        toggleCategoryInput();


        $('#can_add_category').change(function() {
            toggleCategoryInput();
        });

        function toggleCategoryInput() {
            console.log('11111111111111');
            var selectedOption = $('#can_add_category').val();
            console.log(selectedOption);
            if (selectedOption === '1') {
                $('#category_input').show();
                @isset($user_travel)
                    $('#travel_ticket_categorie').val('{{ $user_travel->categorie_id }}');
                @endisset
            } else {
                $('#category_input').hide();
            }
        }
    });
</script>
