<div class="modal-dialog modal-lg" role="document" id='add_client_modal'>
    <div class="modal-content">
        {!! Form::open(['method' => 'post', 'id' => 'quick_add_client_form']) !!}
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="client_id" value="">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">@lang('sales::lang.add_element')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                    <div class="form-group">
                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                        {!! Form::select('profession', $professions, null, [
                            'class' => 'form-control select2',
                            'id' => 'professionSearch',
                            'style' => 'height:40px',
                            'required',
                            'placeholder' => __('sales::lang.profession'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('nationality', __('sales::lang.nationality') . ':*') !!}
                        {!! Form::select('nationality', $nationalities, null, [
                            'class' => 'form-control select2',
                            'id' => 'nationalitySearch',
                            'required',
                            'style' => 'height:40px',
                            'placeholder' => __('sales::lang.nationality'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('gender', __('sales::lang.gender') . ':*') !!}
                        {!! Form::select('gender', ['male' => __('sales::lang.male'), 'female' => __('sales::lang.female')], null, [
                            'class' => 'form-control',
                            'required',
                            'style' => 'height:40px',
                            'placeholder' => __('sales::lang.gender'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('essentials_salary', __('essentials::lang.salary') . ':') !!}
                        {!! Form::number('essentials_salary', !empty($user->essentials_salary) ? $user->essentials_salary : null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'placeholder' => __('essentials::lang.salary'),
                            'id' => 'essentials_salary',
                        ]) !!}
                    </div>
                </div>

                <input type="hidden" id="selectedData" name="selectedData" value="">
                <br>
                <div class="col-md-12">
                    <h4> @lang('sales::lang.allowances') </h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('sales::lang.allowance_name') }}</th>
                                <th>{{ __('essentials::lang.type') }}</th>
                                <th>{{ __('essentials::lang.amount') }}</th>


                            </tr>
                        </thead>
                        <tbody id="salary-table-body">
                            <tr>
                                <td>
                                    {!! Form::select(
                                        'salary_type[]',
                                        [
                                            'housing_allowance' => __('sales::lang.housing_allowance'),
                                            'food_allowance' => __('sales::lang.food_allowance'),
                                            'transportation_allowance' => __('sales::lang.transportation_allowance'),
                                            'overtime_hours' => __('sales::lang.overtime_hours'),
                                            'other_allowances' => __('sales::lang.other_allowances'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control width-60 pull-left',
                                            'style' => 'height:40px',
                                            'placeholder' => __('essentials::lang.extra_salary_type'),
                                        ],
                                    ) !!}
                                </td>
                                <td>
                                    {!! Form::select(
                                        'type[]',
                                        [
                                            'insured_by_emdadat' => __('sales::lang.insured_by_emdadat'),
                                            'insured_by_the_customer' => __('sales::lang.insured_by_the_customer'),
                                        ],
                                        null,
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => __('essentials::lang.type'),
                                            'id' => 'typeDropdown',
                                            'style' => 'height:40px',
                                        ],
                                    ) !!}
                                </td>
                                <td>
                                    {!! Form::text('amount[]', null, [
                                        'class' => 'form-control width-60 pull-left',
                                        'placeholder' => __('essentials::lang.amount'),
                                        'id' => 'amountInput',
                                        'style' => 'height:40px;',
                                    ]) !!}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" id="add-row"
                        class="btn btn-primary">{{ __('essentials::lang.add') }}</button>
                </div>

                <div class="col-md-12">
                    <h4> @lang('sales::lang.additional_costs') </h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>@lang('sales::lang.description')</th>
                                <th>@lang('sales::lang.amount')</th>
                                <th>@lang('sales::lang.duration_by_month')</th>
                                <th>@lang('sales::lang.monthly_amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>@lang('sales::lang.GOSI')</td>
                                <td id="gosiAmount"></td>
                                <td>24</td>
                                <td id="gosiMonthlyAmount"></td>
                            </tr>
                            <tr>
                                <td>@lang('sales::lang.vacation_salary')</td>
                                <td id="vacationAmount"></td>
                                <td>24</td>
                                <td id="vacationMonthlyAmount"></td>
                            </tr>
                            <tr>
                                <td>@lang('sales::lang.end_of_service')</td>
                                <td id="endServiceAmount"></td>
                                <td>24</td>
                                <td id="endServiceMonthlyAmount"></td>
                            </tr>
                            <tr>
                                <td>@lang('sales::lang.administrative_fees')</td>
                                <td id="administrativeAmount"></td>
                                <td>1</td>
                                <td id="administrativeMonthlyAmount"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="table-responsive col-md-12">
            <table class="table table-bordered add-product-price-table table">
                <tr>
                    <th>@lang('sales::lang.number_of_clients')</th>
                    <th>@lang('sales::lang.monthly_cost')</th>
                    <th>@lang('sales::lang.total')</th>
                </tr>
                <tr>
                    <td>
                        <div class="col-sm-6">
                            {!! Form::text('number', 0, [
                                'class' => 'form-control input-sm input_number',
                                'placeholder' => __('sales::lang.number_of_clients'),
                                'required',
                                'id' => 'input_number',
                            ]) !!}
                        </div>
                    </td>
                    <td>
                        <div class="col-sm-6">
                            {!! Form::text('monthly_cost', 0, [
                                'class' => 'form-control input-sm',
                                'placeholder' => __('sales::lang.monthly_cost'),
                                'required',
                                'id' => 'monthly_cost',
                            ]) !!}
                        </div>
                    </td>
                    <td>
                        <div class="col-sm-6">
                            {!! Form::text('total', 0, ['class' => 'form-control input-sm', 'disabled', 'id' => 'total']) !!}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="submit_quick_client">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script src="{{ asset('js/client.js') }}?v={{ filemtime(public_path('js/client.js')) }}"></script>

<script>
    $(document).ready(function() {
        var selectedData = [];
        const form = document.getElementById('quick_add_client_form');

        $('#submit_quick_client').on('click', function(event) {
            event.preventDefault();
            updateSelectedData();
            const formData = new FormData(form);
            formData.append('selectedData', JSON.stringify(selectedData));
            var essentialsSalary = parseFloat($('#essentials_salary').val()) || 0;
            var gosiAmount = essentialsSalary * 0.02 * 24;
            var vacationAmount = (essentialsSalary / 30) * 21 * 2;
            var endServiceAmount = essentialsSalary / 2 * 2;
            var administrativeAmount = 375;
            var gosiMonthlyAmount = gosiAmount / 24;
            var vacationMonthlyAmount = vacationAmount / 24;
            var endServiceMonthlyAmount = endServiceAmount / 24;
            var administrativeMonthlyAmount = administrativeAmount / 1;

            formData.append('gosiAmount', gosiMonthlyAmount.toFixed(2));
            formData.append('vacationAmount', vacationMonthlyAmount.toFixed(2));
            formData.append('endServiceAmount', endServiceMonthlyAmount.toFixed(2));
            formData.append('administrativeAmount', administrativeMonthlyAmount.toFixed(2));

            fetch('/sale/saveQuickClient', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    submittedDataFunc(data);
                    $('.quick_add_client_modal').modal('hide');
                    $('#quick_add_client_form')[0].reset();
                    $('#selectedData').val('');
                    $('#action').val('add');
                })
                .catch(error => {
                    console.error(error);
                });
        });

        function updateSelectedData() {
            selectedData = [];

            $('select[name="salary_type[]"]').each(function(index) {
                var salaryType = $(this).val();
                var type = $('input[name="type[]"]').val();
                var amount = parseFloat($('input[name="amount[]"]').eq(index).val());

                selectedData.push({
                    salaryType: salaryType,
                    amount: amount,
                    type: type
                });
            });

            var inputElement = document.getElementById('selectedData');
            inputElement.value = JSON.stringify(selectedData);
        }
        // function addRow() {
        //     var newRow = $('#salary-table-body tr:first').clone();
        //     newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
        //     newRow.find('input[name="amount[]"]').attr('name', 'amount[]');
        //     $('#salary-table-body').append(newRow);
        // }
        function addRow() {
            var newRow = $('#salary-table-body tr:first').clone();
            newRow.find('input[name="amount[]"]').val(''); // Clear the amount input
            newRow.find('.include-salary-checkbox').remove(); // Remove any existing checkbox

            if ($('#contract_form').val() === 'operating_fees') {
                newRow.find('td:last').after(
                    '<td><input type="checkbox" name="include_salary[]" class="include-salary-checkbox" style="margin:auto; display:block;"></td>'
                );
            } else {
                newRow.find('td:last').after('<td></td>'); // Add an empty cell if not operating_fees
            }

            $('#salary-table-body').append(newRow);
        }

        $('#add-row').click(function() {
            addRow();
        });
    });
    // $(document).on('change', 'select[name="type[]"]', function() {
    //         var selectedOption = $(this).val();
    //         var amountInput = $(this).closest('tr').find('input[name="amount[]"]');
    //         if (selectedOption === 'insured_by_the_customer' || selectedOption ===
    //             'insured_by_emdadat') {
    //             amountInput.prop('disabled', true).val('0');
    //             updateMonthlyCost();
    //             updateTotal();
    //         } else {
    //             amountInput.prop('disabled', false);
    //         }
    //     });

    $(document).ready(function() {
        function handleContractTypeChange() {
            if ($('#contract_form').val() === 'operating_fees') {
                $('tr:contains("{{ __('sales::lang.GOSI') }}")').remove();
                $('tr:contains("{{ __('sales::lang.vacation_salary') }}")').remove();
                $('tr:contains("{{ __('sales::lang.end_of_service') }}")').remove();
                $('#salary-table-body tr').each(function() {
                    if (!$(this).find('.include-salary-checkbox').length) {
                        $(this).find('td:last').after(
                            '<td><input type="checkbox" name="include_salary[]" class="include-salary-checkbox" style="margin:auto; display:block;"></td>'
                        );
                    }
                });
            } else {
                $('#salary-table-body tr').each(function() {
                    $(this).find('.include-salary-checkbox').closest('td').remove();
                });
            }
            updateMonthlyCost();
        }

        handleContractTypeChange();



        $('#contract_form').on('change', function() {
            handleContractTypeChange();
        });

        $(document).on('change', '.include-salary-checkbox', function() {
            updateMonthlyCostAndTotal();
        });

        function updateMonthlyCost() {
            var essentialsSalary = parseFloat($('#essentials_salary').val()) || 0;
            var totalAllowances = 0;

            if ($('#contract_form').val() == 'operating_fees') {
                $('#salary-table-body tr').each(function() {
                    var includeSalary = $(this).find('.include-salary-checkbox').is(':checked');
                    if (includeSalary) {
                        var amount = parseFloat($(this).find('input[name="amount[]"]').val()) || 0;
                        totalAllowances += amount;
                    }
                });
            } else {
                $('input[name="amount[]"]').each(function() {
                    var amount = parseFloat($(this).val()) || 0;
                    totalAllowances += amount;
                });
            }

            var gosiMonthlyAmount = 0;
            var vacationMonthlyAmount = 0;
            var endServiceMonthlyAmount = 0;
            var administrativeMonthlyAmount = 375;

            if ($('#contract_form').val() !== 'operating_fees') {
                var gosiAmount = essentialsSalary * 0.02 * 24;
                var vacationAmount = (essentialsSalary / 30) * 21 * 2;
                var endServiceAmount = essentialsSalary / 2 * 2;
                gosiMonthlyAmount = gosiAmount / 24;
                vacationMonthlyAmount = vacationAmount / 24;
                endServiceMonthlyAmount = endServiceAmount / 24;
                administrativeMonthlyAmount = 375;
            }

            if ($('#contract_form').val() !== 'operating_fees') {
                $('#gosiAmount').text(gosiAmount.toFixed(2));
                $('#vacationAmount').text(vacationAmount.toFixed(2));
                $('#endServiceAmount').text(endServiceAmount.toFixed(2));
            }
            $('#administrativeAmount').text(administrativeAmount.toFixed(2));

            if ($('#contract_form').val() !== 'operating_fees') {
                $('#gosiMonthlyAmount').text(gosiMonthlyAmount.toFixed(2));
                $('#vacationMonthlyAmount').text(vacationMonthlyAmount.toFixed(2));
                $('#endServiceMonthlyAmount').text(endServiceMonthlyAmount.toFixed(2));
            }
            $('#administrativeMonthlyAmount').text(administrativeMonthlyAmount.toFixed(2));

            var additionalMonthlyCost = gosiMonthlyAmount + vacationMonthlyAmount + endServiceMonthlyAmount +
                administrativeMonthlyAmount;
            var monthlyCost;

            if ($('#contract_form').val() == 'operating_fees') {
                monthlyCost = totalAllowances + administrativeMonthlyAmount;
            } else {
                monthlyCost = essentialsSalary + totalAllowances + additionalMonthlyCost;
            }

            $('#monthly_cost').val(monthlyCost.toFixed(2));
        }

        $('#essentials_salary').on('change', function() {
            updateMonthlyCost();
            updateTotal();
        });

        $('#essentials_salary').on('input', function() {
            updateMonthlyCost();
            updateTotal();
        });

        $(document).on('input', 'input[id="amountInput"]', function() {
            updateMonthlyCost();
            updateTotal();
        });

        const numberInput = document.getElementById('input_number');
        const monthlyCostInput = document.getElementById('monthly_cost');
        const totalField = document.getElementById('total');

        numberInput.addEventListener('input', updateTotal);
        monthlyCostInput.addEventListener('input', updateTotal);

        function updateTotal() {
            totalField.value = (parseFloat(numberInput.value) || 0) * (parseFloat(monthlyCostInput.value) || 0);
        }
    });
</script>
