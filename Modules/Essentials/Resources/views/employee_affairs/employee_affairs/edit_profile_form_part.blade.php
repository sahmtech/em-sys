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
            {!! Form::label('user_hijri_dob', __('lang_v1.hijri_dob') . ':') !!}
            {!! Form::text('hijrii_date', !empty($user->hijrii_date) ? $user->hijrii_date : null, [
                'class' => 'form-control hijri-date-picker',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.hijri_dob'),
                'readonly',
                'id' => 'user_hijri_dob',
            ]) !!}
        </div>

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
            {!! Form::label('blood_group', __('lang_v1.blood_group') . ':') !!}

            {!! Form::select('blood_group', $blood_types, !empty($user->blood_group) ? $user->blood_group : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('essentials::lang.blood_group'),
            ]) !!}
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


        <div class="form-group col-md-6">
            {!! Form::label('permanent_address', __('lang_v1.address') . ':') !!}
            {!! Form::text('permanent_address', !empty($user->permanent_address) ? $user->permanent_address : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'placeholder' => __('lang_v1.address'),
                'rows' => 3,
            ]) !!}
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('id_proof_name', __('lang_v1.id_proof_name') . ':*') !!}
            <select id="id_proof_name" style="height:40px" required name="id_proof_name" class="form-control"
                onchange="updateNationalityOptions(this)">
                <option value="">@lang('user.select_proof_name')</option>
                <option value="national_id"
                    {{ !empty($user->id_proof_name) && $user->id_proof_name == 'national_id' ? 'selected' : '' }}>
                    @lang('user.national_id')</option>
                <option value="eqama"
                    {{ !empty($user->id_proof_name) && $user->id_proof_name == 'eqama' ? 'selected' : '' }}>
                    @lang('user.eqama')</option>
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

        <div class="form-group col-md-3" id="proof_no_container" style="<?php echo !empty($user->id_proof_name) ? 'display:block' : 'display:none'; ?>">
            {!! Form::label('id_proof_number', __('lang_v1.id_proof_number') . ':*') !!}
            {!! Form::text('id_proof_number', !empty($user->id_proof_number) ? $user->id_proof_number : null, [
                'class' => 'form-control',
                'style' => 'height:40px',
                'required',
                'placeholder' => __('lang_v1.id_proof_number'),
                'oninput' => 'validateIdProofNumber(this)',
            ]) !!}
            <span id="idProofNumberError" class="text-danger"></span>
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
        <br>
        <div class="form-group col-md-3">
            <button type="button" class="btn btn-success align-self-center id_attachements_btn">
                {{ __('essentials::lang.id_attachements') }}
            </button>
        </div>




        @if (empty($user))
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <table id="documentsTable" class="table">
                            <thead>
                                <tr>
                                    <th>{!! Form::label('doc_type', __('essentials::lang.doc_type') . ':') !!}</th>
                                    <th>{!! Form::label('document_file', __('essentials::lang.file') . ':') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <!-- Document Type Select -->
                                        {!! Form::select(
                                            'document_type[]',
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
                                                'class' => 'form-control',
                                                'style' => 'height:40px',
                                                'placeholder' => __('essentials::lang.select_type'),
                                            ],
                                        ) !!}
                                    </td>
                                    <td>
                                        <!-- File Input -->
                                        {!! Form::file('document_file[]', [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {{-- <button type="button" id="remove-row"
                                        class="btn btn-danger remove-row">{{ __('messages.delete') }}
                                    </button> --}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-center">

                    <button type="button" class="btn btn-success align-self-center" onclick="addRow()">
                        {{ __('essentials::lang.add_file') }}
                    </button>
                </div>
            </div>
        @endif
        <input type="hidden" id="DocumentTypes" name="DocumentTypes" value="">
    </div>



    <div class="col-md-12 box box-primary">

        <h4>@lang('lang_v1.add_qualification'):</h4>




        <div class=" col-md-4">
            <div class="form-group">

                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':') !!}
                {!! Form::select(
                    'qualification_type',
                    [
                        'bachelors' => __('essentials::lang.bachelors'),
                        'master' => __('essentials::lang.master'),
                        'PhD' => __('essentials::lang.PhD'),
                        'diploma' => __('essentials::lang.diploma'),
                    ],
                    !empty($qualification->qualification_type) ? $qualification->qualification_type : null,
                    ['class' => 'form-control', 'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')],
                ) !!}
            </div>
        </div>
        <div class=" col-md-4">
            <div class="form-group ">
                {!! Form::label('general_specialization', __('essentials::lang.general_specialization') . ':') !!}
                {!! Form::select(
                    'general_specialization',
                    $professions,
                    !empty($qualification->specialization) ? $qualification->specialization : null,
                    [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'id' => 'professionSelect',
                        'placeholder' => __('essentials::lang.select_specialization'),
                    ],
                ) !!}
            </div>

        </div>
        <div class=" col-md-4">
            <div class="form-group ">
                {!! Form::label('sub_specialization', __('essentials::lang.sub_specialization') . ':') !!}
                {!! Form::select(
                    'sub_specialization',
                    $spacializations,
                    !empty($qualification->sub_specialization) ? $qualification->sub_specialization : null,
                    [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'id' => 'specializationSelect',
                    ],
                ) !!}
            </div>

        </div>
        <div class=" col-md-4">
            <div class="form-group">
                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                {!! Form::date(
                    'graduation_year',
                    !empty($qualification->graduation_year) ? $qualification->graduation_year : null,
                    ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_year')],
                ) !!}
            </div>
        </div>


        <div class=" col-md-4">
            <div class="form-group">
                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                {!! Form::text(
                    'graduation_institution',
                    !empty($qualification->graduation_institution) ? $qualification->graduation_institution : null,
                    ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_institution')],
                ) !!}
            </div>
        </div>
        <div class=" col-md-4">
            <div class="form-group">
                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                {!! Form::select(
                    'graduation_country',
                    $countries,
                    !empty($qualification->graduation_country) ? $qualification->graduation_country : null,
                    [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'placeholder' => __('essentials::lang.select_country'),
                    ],
                ) !!}
            </div>
        </div>
        <div class=" col-md-2">
            <div class="form-group">
                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                {!! Form::number('degree', !empty($qualification->degree) ? $qualification->degree : null, [
                    'class' => 'form-control',
                    'placeholder' => __('essentials::lang.degree'),
                    'step' => 'any',
                    'onkeyup' => 'getGPA()',
                ]) !!}
            </div>
        </div>

        <div class=" col-md-2">
            <div class="form-group">
                {!! Form::label('great_degree', __('essentials::lang.great_degree') . ':') !!}
                {!! Form::number('great_degree', !empty($qualification->great_degree) ? $qualification->great_degree : null, [
                    'class' => 'form-control',
                    'placeholder' => __('essentials::lang.great_degree'),
                    'step' => 'any',
                    'onkeyup' => 'getGPA()',
                ]) !!}

            </div>
        </div>

        <div class=" col-md-4">
            <div class="form-group">
                {!! Form::label('marksName', __('essentials::lang.marksName') . ':') !!}
                {!! Form::text('marksName', !empty($qualification->marksName) ? $qualification->marksName : null, [
                    'class' => 'form-control',
                    'placeholder' => __('essentials::lang.marksName'),
                    'step' => 'any',
                    'readonly',
                ]) !!}
            </div>
        </div>

        <div class="clearfix"></div>
        <br>
        <div class="form-group col-md-3">
            <button type="button" class="btn btn-success align-self-center qualification_attachements_btn"
                id="qualificationFileLink">
                {{ __('essentials::lang.qualification_attachements') }}
            </button>

        </div>
        @if (empty($qualification))
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('essentials::lang.qualification_file', __('essentials::lang.qualification_file') . ':') !!}
                    {!! Form::file('qualification_file', [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                    ]) !!}
                </div>

            </div>
        @endif
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

        <div class="clearfix"></div>
        <br>
        <div class="form-group col-md-3">
            <button type="button" class="btn btn-success align-self-center iban_attachements_btn" id="ibanFileLink">
                {{ __('essentials::lang.Iban_file') }}
            </button>

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
        $(document).ready(function() {

            toggleBorderNoVisibility();


            $('#id_proof_name').on('change', function() {
                toggleBorderNoVisibility();
            });


            function toggleBorderNoVisibility() {
                var idProofName = $('#id_proof_name').val();
                if (idProofName === 'eqama') {
                    $('#border_no_container').show();
                } else {
                    $('#border_no_container').hide();
                }
            }
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
        function addRow() {
            var table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
            var newRow = table.rows[0].cloneNode(true);
            var len = table.rows.length;


            newRow.cells[0].getElementsByTagName('select')[0].name = 'document_type[' + len + ']';
            newRow.cells[1].getElementsByTagName('input')[0].name = 'document_file[' + len + ']';


            var removeButton =
                '<button type="button" class="btn btn-danger remove-row">{{ __('messages.delete') }}</button>';
            if (newRow.cells[2]) {
                newRow.cells[2].innerHTML = removeButton;
            } else {
                var newCell = newRow.insertCell(2);
                newCell.innerHTML = removeButton;
            }


            table.appendChild(newRow);
        }

        $(document).on('click', '.remove-row', function() {

            if ($('#documentsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });

        $(document).on('change', 'select[name^="document_type"]', function() {
            updateDocumentTypes();
        });

        $(document).on('change', 'input[name^="document_file"]', function() {
            updateDocumentTypes();
        });

        function updateDocumentTypes() {
            var DocumentTypes = [];

            $('select[name^="document_type"]').each(function(index) {
                var document_type = $(this).val();
                var document_file = $('input[name^="document_file"]').eq(index)
                    .val(); // Get the file input based on the index
                if (document_type && document_file) { // Check if both values are not empty
                    DocumentTypes.push({
                        document_type: document_type,
                        document_file: document_file // This will be the file name
                    });
                }
            });

            console.log(DocumentTypes);
            var inputElement = document.getElementById('DocumentTypes');
            if (inputElement) {
                inputElement.value = JSON.stringify(DocumentTypes);
            } else {
                // If the element does not exist, create it and append to the form
                inputElement = document.createElement('input');
                inputElement.type = 'hidden';
                inputElement.id = 'DocumentTypes';
                inputElement.name = 'DocumentTypes';
                $('form').append(inputElement);
                inputElement.value = JSON.stringify(DocumentTypes);
            }
        }
    </script>



    <script>
        function validateBorderNumber() {
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


        if (document.getElementById('border_no')) {
            document.getElementById('border_no').addEventListener('input', validateBorderNumber);
        }
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

                    $('#border_no_container').hide();
                    $('#proof_no_container').show();


                    for (const [id, name] of Object.entries(nationalities)) {

                        if (id !== '5') {
                            const option = document.createElement('option');
                            option.value = id;
                            option.text = name;
                            nationalitySelect.appendChild(option);
                        }
                    }
                } else if (selectedOption === 'national_id') {
                    // validationLength = 13;
                    console.log(selectedOption);



                    validationLength = 10;
                    idProofNumberInput.value = '1';

                    $('#border_no_container').show();
                    $('#proof_no_container').show();

                    const option = document.createElement('option');
                    option.value = '5';
                    option.text = nationalities['5'];
                    nationalitySelect.appendChild(option);

                    // for (const [id, name] of Object.entries(nationalities)) {
                    //     const option = document.createElement('option');
                    //     option.value = id;
                    //     option.text = name;
                    //     nationalitySelect.appendChild(option);
                    // }
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


    <script type="text/javascript">
        $(document).ready(function() {


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


        function updateberPProofNumrefix(select) {

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
            if (idProofName === 'eqama') {
                idProofNumberInput.setAttribute('type', 'text');
            } else if (idProofName === 'national_id') {
                idProofNumberInput.setAttribute('type', 'number');
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
