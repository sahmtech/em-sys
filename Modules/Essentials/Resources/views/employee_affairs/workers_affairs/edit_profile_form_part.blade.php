<!DOCTYPE html>
<html lang="ar">

<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $user_custom_field1 = !empty($custom_labels['user']['custom_field_1']) ? $custom_labels['user']['custom_field_1'] : __('lang_v1.user_custom_field1');
        $user_custom_field2 = !empty($custom_labels['user']['custom_field_2']) ? $custom_labels['user']['custom_field_2'] : __('lang_v1.user_custom_field2');
        $user_custom_field3 = !empty($custom_labels['user']['custom_field_3']) ? $custom_labels['user']['custom_field_3'] : __('lang_v1.user_custom_field3');
        $user_custom_field4 = !empty($custom_labels['user']['custom_field_4']) ? $custom_labels['user']['custom_field_4'] : __('lang_v1.user_custom_field4');
    @endphp


    <div class="col-md-12 box box-primary">
        <h4>@lang('essentials::lang.personal_info'):</h4>


        <div class="form-group col-md-3">
            {!! Form::label('user_dob', __('lang_v1.dob') . ':') !!}
            {!! Form::text('dob', !empty($user->dob) ? @format_date($user->dob) : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.dob'),
                'readonly',
                'id' => 'user_dob',
            ]) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('gender', __('lang_v1.gender') . ':') !!}
            {!! Form::select(
                'gender',
                ['male' => __('lang_v1.male'), 'female' => __('lang_v1.female'), 'others' => __('lang_v1.others')],
                !empty($user->gender) ? $user->gender : null,
                [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'gender',
                    'placeholder' => __('messages.please_select'),
                ],
            ) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('marital_status', __('lang_v1.marital_status') . ':') !!}
            {!! Form::select(
                'marital_status',
                ['married' => __('lang_v1.married'), 'unmarried' => __('lang_v1.unmarried'), 'divorced' => __('lang_v1.divorced')],
                !empty($user->marital_status) ? $user->marital_status : null,
                ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('lang_v1.marital_status')],
            ) !!}
        </div>



        <div class="form-group col-md-3">
            {!! Form::label('contact_number', __('lang_v1.mobile_number') . ':') !!}
            {!! Form::text('contact_number', !empty($user->contact_number) ? $user->contact_number : '05', [
                'class' => 'form-control',
                'require',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.mobile_number'),
                'oninput' => 'validateContactNumber(this)',
                'maxlength' => '10',
            ]) !!}
            <span id="contactNumberError" class="text-danger"></span>
        </div>


        <div class="form-group col-md-3">
            {!! Form::label('id_proof_name', __('lang_v1.id_proof_name') . ':*') !!}
            <select id="id_proof_name" style="height:40px" required name="id_proof_name" class="form-control"
                onchange="updateNationalityOptions(this)">
                <option value="">@lang('user.select_proof_name')</option>

                <option value="eqama"
                    {{ !empty($user->id_proof_name) && $user->id_proof_name == 'eqama' ? 'selected' : '' }}>
                    @lang('user.eqama')</option>
                <option value="border"
                    {{ !empty($user->id_proof_name) && $user->id_proof_name == 'border' ? 'selected' : '' }}>
                    @lang('essentials::lang.border_number')</option>
            </select>
        </div>

        <div id="eqamaEndDateInput" class="form-group col-md-3"
            style="{{ !is_null($resident_doc) && !is_null($resident_doc->expiration_date) ? '' : 'display: none;' }}">
            {!! Form::label('expiration_date', __('lang_v1.eqama_end_date') . ':') !!}
            {!! Form::date('expiration_date', optional($resident_doc)->expiration_date ?? '', [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.eqama_end_date'),
                'id' => 'eqama_end_date',
            ]) !!}
        </div>

        <div class="form-group col-md-3" id="proof_no_container" style="display:none">
            {!! Form::label('id_proof_number', __('lang_v1.id_proof_number') . ':') !!}
            {!! Form::text('id_proof_number', !empty($user->id_proof_number) ? $user->id_proof_number : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.id_proof_number'),
                'oninput' => 'validateIdProofNumber(this)',
            ]) !!}
            <span id="idProofNumberError" class="text-danger"></span>
        </div>
        <div class="form-group col-md-6" id="border_no_container"
            style="{{ !is_null($user) && optional($user)->border_no ? '' : 'display:none' }}">
            {!! Form::label('border_no', __('essentials::lang.border_number') . ':') !!}
            {!! Form::text('border_no', optional($user)->border_no ?? '3', [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.border_number'),
                'id' => 'border_no',
                'maxlength' => '10',
                'oninput' => 'validateBorderNumber()',
            ]) !!}
            <div id="border_no_error" class="text-danger"></div>
        </div>


        <div class="form-group col-md-3">
            {!! Form::label('nationality', __('sales::lang.nationality') . ':*') !!}
            {!! Form::select('nationality', $nationalities, !empty($user->nationality_id) ? $user->nationality_id : null, [
                'class' => 'form-control select2',
                'id' => 'nationalities_select',
                'style' => 'height:40px',
                'required',
                'placeholder' => __('sales::lang.nationality'),
            ]) !!}
        </div>

        <div class="clearfix"></div>

        {{-- <div class="col-md-3">
            <div class="form-group ">
                {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':') !!}
                {!! Form::select(
                    'document_type',
                    [
                        'national_id' => __('essentials::lang.national_id'),
                        'passport' => __('essentials::lang.passport'),
                        'residence_permit' => __('essentials::lang.residence_permit'),
                        'drivers_license' => __('essentials::lang.drivers_license'),
                        'car_registration' => __('essentials::lang.car_registration'),
                        'international_certificate' => __('essentials::lang.international_certificate'),
                    ],
                    null,
                    [
                        'class' => 'form-control ',
                        'style' => 'height:40px',
                        'placeholder' => __('essentials::lang.select_type'),
                    ],
                ) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('document_file', __('essentials::lang.file') . ':') !!}
                {!! Form::file('document_file', [
                    'class' => 'form-control',
                    'placeholder' => __('essentials::lang.file'),
                    'style' => 'height:40px',
                ]) !!}
            </div>
        </div>
            --}}
    </div>




    <div class="col-md-12  box box-primary" id="section3">

        <h4>@lang('lang_v1.bank_details'):</h4>

        <div class="form-group col-md-4">
            {!! Form::label('account_holder_name', __('lang_v1.account_holder_name') . ':') !!}
            {!! Form::text(
                'bank_details[account_holder_name]',
                !empty($bank_details['account_holder_name']) ? $bank_details['account_holder_name'] : null,
                [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'account_holder_name',
                    'placeholder' => __('lang_v1.account_holder_name'),
                ],
            ) !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('account_number', __('lang_v1.account_number') . ':') !!}
            {!! Form::text(
                'bank_details[account_number]',
                !empty($bank_details['account_number']) ? $bank_details['account_number'] : null,
                [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'account_number',
                    'placeholder' => __('lang_v1.account_number'),
                ],
            ) !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('bank_name', __('lang_v1.bank_name') . ':') !!}

            {!! Form::select(
                'bank_details[bank_name]',
                $banks,
                !empty($bank_details['bank_name']) ? $bank_details['bank_name'] : null,
                [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'bank_name',
                    'placeholder' => __('lang_v1.bank_name'),
                ],
            ) !!}

        </div>
        <div class="form-group col-md-4">
            {!! Form::label('bank_code', __('lang_v1.bank_code') . ':') !!} @show_tooltip(__('lang_v1.bank_code_help'))
            {!! Form::text(
                'bank_details[bank_code]',
                !empty($bank_details['bank_code']) ? $bank_details['bank_code'] : 'SA',
                [
                    'class' => 'form-control',
                    'style' => 'height:40px',
                    'id' => 'bank_code',
                    'placeholder' => __('lang_v1.bank_code'),
                    'oninput' => 'validateBankCode(this)',
                    'maxlength' => '24',
                ],
            ) !!}
            <span id="bankCodeError" class="text-danger"></span>
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('branch', __('lang_v1.branch') . ':') !!}
            {!! Form::text('bank_details[branch]', !empty($bank_details['branch']) ? $bank_details['branch'] : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'id' => 'branch',
                'placeholder' => __('lang_v1.branch'),
            ]) !!}
        </div>

        {{-- <div class="form-group col-md-4">
            {!! Form::label('Iban_file', __('essentials::lang.Iban_file') . ':') !!}
            {!! Form::file('Iban_file', [
                'class' => 'form-control',
                'placeholder' => __('essentials::lang.Iban_file'),
            
                'style' => 'height:40px',
            ]) !!}
        </div> --}}


        {{--
                <div class="clearfix"></div>
            <div class="row">
                        <div class="col-md-12 text-center">
                        <button onclick="submitSection('section2')" class="btn btn-primary btn-big">@lang('messages.save')</button>
                        </div>
            </div> --}}



    </div>
    <script>
        function validateBorderNumber() {
            console.log("border");
            var borderNoInput = document.getElementById('border_no');
            var borderNo = borderNoInput.value.trim();

            if (borderNo.length === 0) {
                document.getElementById('border_no_error').textContent = '';
                return;
            }

            if (/^3\d{9}$/.test(borderNo) || /^4\d{9}$/.test(borderNo)) {
                document.getElementById('border_no_error').textContent = '';
            } else {
                document.getElementById('border_no_error').textContent =
                    'رقم الحدود يجب أن يحتوي على 10 أرقام ويبدأ ب 3 أو 4';
            }
        }



        document.getElementById('border_no').addEventListener('input', validateBorderNumber);
    </script>
    <script>
        $(document).ready(function() {
            // Function to handle the showing and hiding of elements
            function handleIdProofSelection() {
                var selectedProof = $('#id_proof_name').val();

                if (selectedProof === 'eqama') {
                    $('#border_no_container').hide();
                    $('#proof_no_container').show();
                } else if (selectedProof === 'border') {
                    $('#border_no_container').show();
                    $('#proof_no_container').hide();
                } else {
                    $('#border_no_container').hide();
                    $('#proof_no_container').hide();
                }
            }

            // Bind the change event of the dropdown to the handler function
            $('#id_proof_name').change(handleIdProofSelection);

            // Call the handler function on page load to set the initial state
            handleIdProofSelection();
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#id_proof_name').change(function() {
                var selectedProof = $(this).val();
                var eqamaEndDateInput = $('#eqamaEndDateInput');

                if (selectedProof === 'eqama') {

                    eqamaEndDateInput.show();

                } else {

                    eqamaEndDateInput.hide();
                    $('#expiration_date').val('');
                }
            });

        });
    </script>





    <script>
        function validateContactNumber(input) {
            let contactNumber = input.value.trim();


            contactNumber = contactNumber.replace(/\D/g, '');

            if (contactNumber.length === 10 && contactNumber.startsWith('05')) {

                document.getElementById('contactNumberError').innerText = '';
            } else {

                if (contactNumber.length !== 10) {
                    document.getElementById('contactNumberError').innerText = 'رقم الموبايل يجب أن يحتوي على 10 أرقام';
                } else if (!contactNumber.startsWith('05')) {
                    document.getElementById('contactNumberError').innerText = 'رقم الموبايل يجب أن يبدأ بـ 05';
                }


                input.value = contactNumber.substr(0, 10);
            }
        }
    </script>


    <script>
        let validationLength = 10;

        function validateBankCode(input) {
            const bankCode = input.value;

            if (bankCode.length === 24 && bankCode.startsWith('SA')) {
                document.getElementById('bankCodeError').innerText = '';
            } else {
                if (bankCode.length !== 24) {
                    document.getElementById('bankCodeError').innerText = 'رقم البنك يجب أن يحتوي على 24 رقم';
                } else if (!bankCode.startsWith('SA')) {
                    document.getElementById('bankCodeError').innerText = 'رقم البنك يجب أن يبدأ بـ SA';
                }


                if (bankCode.length > 24) {
                    input.value = bankCode.substr(0, 24);
                }
            }
        }


        $(document).ready(function() {


            var nationalities = @json($nationalities);
            var selectedNationalityId = {{ $user->nationality_id ?? 'null' }};


            var nationalitySelect = $('#nationalities_select');

            $.each(nationalities, function(id, name) {


                nationalitySelect.append(new Option(name, id));

            });



            nationalitySelect.val(selectedNationalityId);


            nationalitySelect.trigger('change');



            $('#id_proof_name').change(function() {
                var selectedOption = $(this).val();
                console.log(selectedOption);
                const idProofNumberInput = document.getElementById('id_proof_number');
                const border_no_containerInput = document.getElementById('border_no');
                idProofNumberInput.minLength = validationLength;

                const nationalitySelect = document.querySelector('#nationalities_select');
                const input = document.getElementById('id_proof_number');
                const prefix = selectedOption === 'eqama' ? '2' : '1';
                input.setAttribute('data-prefix', prefix);
                input.value = prefix;


                nationalitySelect.innerHTML = '';


                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.text = '{{ __('sales::lang.nationality') }}';
                nationalitySelect.appendChild(defaultOption);

                if (selectedOption === 'eqama') {
                    validationLength = 10;
                    idProofNumberInput.value = '2';
                    border_no_containerInput.value = '';
                    $('#border_no_container').hide();
                    $('#proof_no_container').show();



                    for (const [id, name] of Object.entries(nationalities)) {
                        if (id != '5') {
                            const option = document.createElement('option');
                            option.value = id;
                            option.text = name;
                            nationalitySelect.appendChild(option);
                        }

                    }
                } else if (selectedOption === 'border') {
                    // validationLength = 13;

                    border_no_containerInput.value = '3';
                    console.log(border_no_containerInput.value);
                    validationLength = 10;
                    idProofNumberInput.value = '';

                    $('#border_no_container').show();
                    $('#proof_no_container').hide();

                    for (const [id, name] of Object.entries(nationalities)) {
                        if (id != '5') {
                            const option = document.createElement('option');
                            option.value = id;
                            option.text = name;
                            nationalitySelect.appendChild(option);
                        }

                    }
                } else {
                    validationLength = 10;
                    idProofNumberInput.value = '';


                    for (const [id, name] of Object.entries(nationalities)) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.text = name;
                        nationalitySelect.appendChild(option);
                    }
                    $('#border_no_container').hide();
                    $('#proof_no_container').hide();
                }
            });



        });
    </script>


    <script>
        function getGPA() {
            const GPA = [{
                    PercentageTo: 100,
                    PercentageFrom: 85,
                    marksName: '{{ __('essentials::lang.veryExcellent') }}',
                    Grade: "A+",
                },
                {
                    PercentageTo: 84,
                    PercentageFrom: 80,
                    marksName: '{{ __('essentials::lang.excellent') }}',
                    Grade: "A",
                },
                {
                    PercentageTo: 79,
                    PercentageFrom: 75,
                    marksName: '{{ __('essentials::lang.veryGood') }}',
                    Grade: "B+",
                },
                {
                    PercentageTo: 74,
                    PercentageFrom: 70,
                    marksName: '{{ __('essentials::lang.veryGood') }}',
                    Grade: "B",
                },
                {
                    PercentageTo: 69,
                    PercentageFrom: 65,
                    marksName: '{{ __('essentials::lang.good') }}',
                    Grade: "B-",
                },
                {
                    PercentageTo: 64,
                    PercentageFrom: 60,
                    marksName: '{{ __('essentials::lang.good') }}',
                    Grade: "C+",
                },
                {
                    PercentageTo: 59,
                    PercentageFrom: 55,
                    marksName: '{{ __('essentials::lang.weak') }}',
                    Grade: "C",
                },
                {
                    PercentageTo: 54,
                    PercentageFrom: 50,
                    marksName: '{{ __('essentials::lang.weak') }}',
                    Grade: "C-",
                },
                {
                    PercentageTo: 49,
                    PercentageFrom: 45,
                    marksName: '{{ __('essentials::lang.bad') }}',
                    Grade: "D",
                },
                {
                    PercentageTo: 44,
                    PercentageFrom: 40,
                    marksName: '{{ __('essentials::lang.bad') }}',
                    Grade: "D-",
                },
                {
                    PercentageTo: 39,
                    PercentageFrom: 0,
                    marksName: '{{ __('essentials::lang.fail') }}',
                    Grade: "F",
                },
            ];
            var great_degree = document.getElementById('great_degree').value;
            var degree = document.getElementById('degree').value;

            if (degree > great_degree) {
                document.getElementById("marksName").style.color = "red";
                document.getElementById('marksName').value = 'يجب ان تكون الدرجة العطمة اعلى من الدرجة';
            }
            var greatDegree = 100 / great_degree;
            GPA.forEach(gpaMark => {
                if (degree >= gpaMark.PercentageFrom / greatDegree && degree <= gpaMark.PercentageTo /
                    greatDegree) {

                    document.getElementById('marksName').value = gpaMark.marksName +
                        '  ( ' + gpaMark.Grade + ' )'
                }

            });


        }



        function validateIdProofNumber(input) {
            const idProofName = document.getElementById('id_proof_name').value;
            const idProofNumberInput = input;
            const idProofNumber = idProofNumberInput.value;

            const idProofNumberError = document.getElementById('idProofNumberError');
            const prefix = input.getAttribute('data-prefix');

            idProofNumberError.innerText = '';

            if (prefix === null) {
                input.value = idProofNumber;
                return;
            }
            if (idProofNumber.startsWith(prefix)) {
                if (idProofName === 'eqama' && idProofNumber.length !== 10) {
                    idProofNumberError.innerText = 'يجب أن تكون مكونة من 10 أرقام';
                    input.value = idProofNumber.slice(0, 10);
                } else if (idProofName === 'national_id' && idProofNumber.length !== 10) {
                    idProofNumberError.innerText = 'يجب أن تكون مكونة من 10 أرقام';
                    input.value = idProofNumber.slice(0, 10);
                }
            } else {
                input.value = prefix + idProofNumber;
            }
        }
    </script>


</body>

</html>
