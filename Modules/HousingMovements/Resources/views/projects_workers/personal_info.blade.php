<div class="col-md-12 box box-primary">
                    <h4>@lang('housingmovements::lang.personal_info'):</h4>
                   

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
                        {!! Form::label('contact_number', __('lang_v1.mobile_number') . ':') !!}
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
            {!! Form::label('id_proof_name', __('lang_v1.id_proof_name') . ':*') !!}
            <select id="id_proof_name" style="height:36px" name="id_proof_name" class="form-control"
                >
                <option value="">@lang('user.select_proof_name')</option>
              
                <option value="eqama">
                    @lang('user.eqama')</option>

                    
                <option value="border">
                    @lang('housingmovements::lang.border_number')</option>
            </select>
        </div>

        <div id="eqamaEndDateInput" class="form-group col-md-3"
            style="{{ !is_null($resident_doc) && !is_null($resident_doc->expiration_date) ? '' : 'display: none;' }}">
            {!! Form::label('expiration_date', __('lang_v1.eqama_end_date') . ':') !!}
            {!! Form::date('expiration_date', optional($resident_doc)->expiration_date ?? '', [
                'class' => 'form-control',
                'style' => 'height:36px',
                'placeholder' => __('lang_v1.eqama_end_date'),
                'id' => 'eqama_end_date',
            ]) !!}
        </div>

        <div class="form-group col-md-3" id="proof_no_container">
            {!! Form::label('id_proof_number', __('lang_v1.id_proof_number') . ':') !!}
            {!! Form::text('id_proof_number', !empty($user->id_proof_number) ? $user->id_proof_number : null, [
                'class' => 'form-control',
                'style' => 'height:36px',
                'placeholder' => __('lang_v1.id_proof_number'),
                'oninput' => 'validateIdProofNumber(this)',
            ]) !!}
            <span id="idProofNumberError" class="text-danger"></span>
        </div>

      
            <div class="form-group col-md-6" id="border_no_container"
            style="{{ !is_null($user) && optional($user)->border_no ? '' : 'display:none' }}">
            {!! Form::label('border_no', __('housingmovements::lang.border_number') . ':') !!}
            {!! Form::text('border_no', optional($user)->border_no ?? '3', [
                'class' => 'form-control',
                'style' => 'height:36px',
                'placeholder' => __('housingmovements::lang.border_number'),
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
                'style' => 'height:36px',
                'required',
                'placeholder' => __('sales::lang.nationality'),
            ]) !!}
        </div>

        <div class="col-md-3">
            <div class="form-group ">
                {!! Form::label('doc_type', __('housingmovements::lang.doc_type') . ':') !!}
                {!! Form::select(
                    'document_type',
                    [
                        'national_id' => __('housingmovements::lang.national_id'),
                        'passport' => __('housingmovements::lang.passport'),
                        'residence_permit' => __('housingmovements::lang.residence_permit'),
                        'drivers_license' => __('housingmovements::lang.drivers_license'),
                        'car_registration' => __('housingmovements::lang.car_registration'),
                        'international_certificate' => __('housingmovements::lang.international_certificate'),
                    ],
                    null,
                    [
                        'class' => 'form-control ',
                        'style' => 'height:36px',
                        'placeholder' => __('housingmovements::lang.select_type'),
                    ],
                ) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('document_file', __('housingmovements::lang.file') . ':') !!}
                {!! Form::file('document_file', [
                    'class' => 'form-control',
                    'placeholder' => __('housingmovements::lang.file'),
                    'style' => 'height:36px',
                ]) !!}
            </div>
        </div>

                
</div>

@include('housingmovements::projects_workers.bank_details')
@include('housingmovements::projects_workers.rest_worker_info')





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


                    for (const [id, name] of Object.entries(nationalities)) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.text = name;
                        nationalitySelect.appendChild(option);
                    }
                } 
                else if (selectedOption === 'border') {
                   // validationLength = 13;

                    border_no_containerInput.value = '3';
                    console.log( border_no_containerInput.value);
                    validationLength = 13;
                    idProofNumberInput.value = '';

                    $('#border_no_container').show();
                    $('#proof_no_container').hide();
                  
                    for (const [id, name] of Object.entries(nationalities)) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.text = name;
                        nationalitySelect.appendChild(option);
                    }
                } 
                else {
                    validationLength = 10;
                    idProofNumberInput.value = '';


                    for (const [id, name] of Object.entries(nationalities)) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.text = name;
                        nationalitySelect.appendChild(option);
                    }
                }
            });


        });
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
