<div class="modal-dialog modal-lg" role="document" id='add_client_modal'>
    <div class="modal-content">
        {!! Form::open(['method' => 'post', 'id' => 'quick_add_client_form']) !!}
        <input type="hidden">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">@lang('sales::lang.add_element')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
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
                {{-- 'specializations','professions','nationalities' --}}
                {{-- <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                        {!! Form::select('specialization', $specializations, null, [
                            'class' => 'form-control select2',
                            'id' => 'specializationSearch',
                            'required',
                            'style' => 'height:40px',
                            'placeholder' => __('sales::lang.specialization'),
                        ]) !!}
                    </div>
                </div> --}}
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
                                            'GOSI' => __('sales::lang.GOSI'),
                                            'vacation_salary' => __('sales::lang.vacation_salary'),
                                            'end_of_service' => __('sales::lang.end_of_service'),
                                            'administrative_fees' => __('sales::lang.administrative_fees'),
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
                                        ['cash' => __('sales::lang.cash'), 'insured_by_the_other' => __('sales::lang.insured_by_the_other')],
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
                                    ]) !!}
                                </td>

                            </tr>
                        </tbody>
                    </table>

                    <button type="button" id="add-row"
                        class="btn btn-primary">{{ __('essentials::lang.add') }}</button>
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
                                'id' => 'number',
                            ]) !!}
                        </div>
                    </td>
                    <td>
                        <div class="col-sm-6">
                            {!! Form::text('monthly_cost', 0, [
                                'class' => 'form-control input-sm input_number',
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

{{-- <script src="{{ asset('js/client.js') }}"></script> --}}
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
                    console.log(" ******* success of ajax *********");
                    console.log(data);
                    console.log(" ****************");
                })
                .catch(error => {
                    console.error(error);
                });
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

            var inputElement = document.getElementById('selectedData');
            inputElement.value = JSON.stringify(selectedData);

        }


        function addRow() {
            var newRow = $('#salary-table-body tr:first').clone();
            newRow.find('select[name="salary_type[]"]').attr('name', 'salary_type[]');
            newRow.find('input[name="amount[]"]').attr('name', 'amount[]');

            $('#salary-table-body').append(newRow);

        }

        $('#add-row').click(function() {
            addRow();
            //  updateSelectedData();
        });


        $(document).on('change', 'select[name="type[]"]', function() {
            var selectedOption = $(this).val();
            var amountInput = $(this).closest('tr').find('input[name="amount[]"]');

            if (selectedOption === 'insured_by_the_other') {
                amountInput.prop('disabled', true).val('0');
                updateMonthlyCost();
                updateTotal();
                // updateSelectedData();
            } else {
                amountInput.prop('disabled', false);
                //  updateSelectedData();
            }

        });



        // Function to calculate the sum of essentials_salary and amount fields
        function updateMonthlyCost() {
            var essentialsSalary = parseFloat($('#essentials_salary').val()) || 0;
            var totalAmount = 0;

            $('input[name="amount[]"]').each(function() {
                var amount = parseFloat($(this).val()) || 0;
                totalAmount += amount;
            });

            var monthlyCost = essentialsSalary + totalAmount;
            $('#monthly_cost').val(monthlyCost);
        }

        // Update the monthly_cost field initially
        updateMonthlyCost();



        // Update monthly cost on essentials_salary change
        $('#essentials_salary').on('input', function() {
            updateMonthlyCost();
            updateTotal();
        });

        // Update monthly cost when any amount field changes
        $(document).on('input', 'input[id="amountInput"]', function() {
            updateMonthlyCost();
            updateTotal();
        });


        const numberInput = document.getElementById('number');
        const monthlyCostInput = document.getElementById('monthly_cost');
        const totalField = document.getElementById('total');


        numberInput.addEventListener('input', updateTotal);
        monthlyCostInput.addEventListener('input', updateTotal);


        function updateTotal() {

            totalField.value = (parseFloat(numberInput.value) || 0) * (parseFloat(monthlyCostInput.value) || 0);
        }



    });
</script>
