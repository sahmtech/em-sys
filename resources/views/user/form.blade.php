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

    <div  class="col-md-12" id="section2">

  

            <div class="form-group col-md-3">
                {!! Form::label('user_dob', __('lang_v1.dob') . ':') !!}
                {!! Form::text('dob', !empty($user->dob) ? @format_date($user->dob) : null, [
                    'class' => 'form-control',
                    'style' => 'height:36px',
                    'placeholder' => __('lang_v1.dob'),
                    'readonly',
                    'id' => 'user_dob',
                ]) !!}
            </div>

            <div class="form-group col-md-3">
            {!! Form::label('user_hijri_dob', __('lang_v1.hijri_dob') . ':') !!}
            {!! Form::text('hijrii_date', !empty($user->hijrii_date) ? $user->hijrii_date : null, [
                'class' => 'form-control hijri-date-picker',
                'style' => 'height:36px',
                'placeholder' => __('lang_v1.hijri_dob'),
                'readonly',
                'id' => 'user_hijri_dob',
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
                        'style' => 'height:36px',
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
                    ['class' => 'form-control', 'style' => 'height:36px', 'placeholder' => __('lang_v1.marital_status')],
                ) !!}
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('blood_group', __('lang_v1.blood_group') . ':') !!}
                <!-- {!! Form::text('blood_group', !empty($user->blood_group) ? $user->blood_group : null, [
                    'class' => 'form-control',
                    'placeholder' => __('lang_v1.blood_group'),
                ]) !!} -->
                {!! Form::select('blood_group', $blood_types, null, [
                    'class' => 'form-control',
                    'placeholder' => __('essentials::lang.blood_group'),
                ]) !!}
            </div>

            <div class="clearfix"></div>
            <div class="form-group col-md-3">
                {!! Form::label('contact_number', __('lang_v1.mobile_number') . ':*') !!}
                {!! Form::text('contact_number', !empty($user->contact_number) ? $user->contact_number : '05', [
                    'class' => 'form-control',
                    'require',
                    'style' => 'height:36px',
                    'placeholder' => __('lang_v1.mobile_number'),
                    'oninput' => 'validateContactNumber(this)',
                    'maxlength' => '10',
                ]) !!}
                <span id="contactNumberError" class="text-danger"></span>
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('alt_number', __('business.alternate_number') . ':') !!}
                {!! Form::text('alt_number', !empty($user->alt_number) ? $user->alt_number : null, [
                    'class' => 'form-control',
                    'placeholder' => __('business.alternate_number'),
                ]) !!}
            </div>
            <div class="form-group col-md-3">
                {!! Form::label('family_number', __('lang_v1.family_contact_number') . ':') !!}
                {!! Form::text('family_number', !empty($user->family_number) ? $user->family_number : null, [
                    'class' => 'form-control',
                    'style' => 'height:36px',
                    'placeholder' => __('lang_v1.family_contact_number'),
                ]) !!}
            </div>


            <div class="clearfix"></div>
            <div class="form-group col-md-6">
                {!! Form::label('permanent_address', __('lang_v1.permanent_address') . ':') !!}
                {!! Form::text('permanent_address', !empty($user->permanent_address) ? $user->permanent_address : null, [
                    'class' => 'form-control',
                    'style' => 'height:36px',
                    'placeholder' => __('lang_v1.permanent_address'),
                    'rows' => 3,
                ]) !!}
            </div>
            <div class="form-group col-md-6">
                {!! Form::label('current_address', __('lang_v1.current_address') . ':') !!}
                {!! Form::text('current_address', !empty($user->current_address) ? $user->current_address : null, [
                    'class' => 'form-control',
                    'style' => 'height:36px',
                    'placeholder' => __('lang_v1.current_address'),
                    'rows' => 3,
                ]) !!}
            </div>



            <div class="form-group col-md-3">
                {!! Form::label('id_proof_name', __('lang_v1.id_proof_name') . ':*') !!}
                <select id="id_proof_name" style="height:40px" name="id_proof_name" class="form-control"
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

            <div class="form-group col-md-3">
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


    <div class="form-group col-md-3">
                                    {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                                    {!! Form::select(
                                        'doc_type',
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
            </div>

            <div class="form-group col-md-6">
                                    {!! Form::label('doc_file', __('essentials::lang.file') . ':*') !!}
                                    {!! Form::file('doc_file', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.file'),
                                      
                                        'style' => 'height:40px',
                                    ]) !!}
            </div>
 
          
            
            {{--
                <div class="clearfix"></div>
            <div class="row">
                        <div class="col-md-12 text-center">
                        <button onclick="submitSection('section2')" class="btn btn-primary btn-big">@lang('messages.save')</button>
                        </div>
            </div> --}}
          
        </div>


<div class="col-md-12" id="section3">
        <hr>
        <h4>@lang('lang_v1.add_qualification'):</h4>

    <div class="form-group col-md-6">
                                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                                {!! Form::select('qualification_type', [
                                    'bachelors'=>__('essentials::lang.bachelors'),
                                     'master' =>__('essentials::lang.master'),
                                     'PhD' =>__('essentials::lang.PhD'),
                                     'diploma' =>__('essentials::lang.diploma'),
                             
                                 ], null, ['class' => 'form-control',
                                  'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')]); !!}
                             </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('major', __('essentials::lang.major') . ':*') !!}
                                {!! Form::select('major',$spacializations, null, ['class' => 'form-control','style'=>'height:40px',
                                     'placeholder' =>  __('essentials::lang.major'), 'required']) !!}
                            </div> 
       
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                                {!! Form::date('graduation_year', null,
                                     ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_year'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                                {!! Form::text('graduation_institution', null,
                                     ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_institution'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                                {!! Form::select('graduation_country',$countries, null, ['class' => 'form-control','style'=>'height:40px',
                                     'placeholder' =>  __('essentials::lang.select_country'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                {!! Form::number('degree', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.degree'), 'required', 'step' => 'any']) !!}
                            </div>
                            
</div>
<div class="col-md-12" id="section3">
        <hr>
        <h4>@lang('lang_v1.bank_details'):</h4>

        <div class="form-group col-md-3">
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
    <div class="form-group col-md-3">
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
    <div class="form-group col-md-3">
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
    <div class="form-group col-md-3">
        {!! Form::label('bank_code', __('lang_v1.bank_code') . ':*') !!} @show_tooltip(__('lang_v1.bank_code_help'))
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
    <div class="form-group col-md-3">
        {!! Form::label('branch', __('lang_v1.branch') . ':') !!}
        {!! Form::text('bank_details[branch]', !empty($bank_details['branch']) ? $bank_details['branch'] : null, [
            'class' => 'form-control',
            'style' => 'height:40px',
            'id' => 'branch',
            'placeholder' => __('lang_v1.branch'),
        ]) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('Iban_file', __('lang_v1.Iban_file') . ':') !!}
        {!! Form::file('bank_details[Iban_file]', null, [
            'class' => 'form-control',
            'id' => 'Iban_file',
            'placeholder' => __('lang_v1.Iban_file'),
            'required',
            'style' => 'height:40px',
        ]) !!}
    </div>
   {{--
    
    <div class="clearfix"></div>
            <div class="row">
                        <div class="col-md-12 text-center">
                        <button onclick="submitSection('section3')" class="btn btn-primary btn-big">@lang('messages.save')</button>
                         </div>
            </div>--}}

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
        $(document).ready(function() {

            $('#userTypeSelect').change(function() {

                var userType = $(this).val();


                var idProofDropdown = $('#id_proof_name');


                idProofDropdown.val('');


                if (userType === 'worker') {

                    idProofDropdown.find('option[value="eqama"]').show();
                    idProofDropdown.find('option[value="national_id"]').hide();
                } else {

                    idProofDropdown.find('option').show();
                }
            });
        });
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



        document.getElementById('border_no').addEventListener('input', validateBorderNumber);
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





function updateNationalityOptions(selectElement) {
    const idProofNumberInput = $('#id_proof_number');
    const selectedOption = selectElement.value;
    console.log(selectedOption);
    const nationalitySelect = $('#nationalities_select');

    const options = nationalitySelect.find('option');
    console.log(options);

    if (selectedOption === 'eqama' || selectedOption === 'national_id') {
     
        idProofNumberInput.val(selectedOption === 'eqama' ? '2' : '1');

      
        options.each(function() {
            $(this).toggle(selectedOption === 'national_id' ? $(this).val() === '5' : $(this).val() !== '5');
        });
    } else {
       
        idProofNumberInput.val('');
        options.show();
    }
}




    </script>




    <script>
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
