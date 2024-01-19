<div class="col-md-12 box box-primary">
                    <h4>@lang('essentials::lang.personal_info'):</h4>
                   

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

                
</div>

