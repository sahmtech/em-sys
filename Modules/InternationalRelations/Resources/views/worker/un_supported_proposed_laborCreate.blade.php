@extends('layouts.app')

@section('title', __('internationalrelations::lang.add_new_proposed_labor'))

@section('content')


    <section class="content-header">
        <h1>@lang('internationalrelations::lang.add_new_proposed_labor')</h1>
    </section>

    <section class="content">
        {!! Form::open(['route' => 'storeProposed_labor', 'enctype' => 'multipart/form-data', 'id' => 'user_add_form']) !!}
        <div class="row">
            <div class="col-md-12">
                @component('components.widget')
                    <input type="hidden" name="delegation_id" value="{{ $delegation_id }}">
                    <input type="hidden" name="agency_id" value="{{ $agency_id }}">
                    <input type="hidden" name="unSupportedworker_order_id" value="{{ $unSupportedworker_order_id }}">
                    <div class="form-group col-md-3">
                        {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                        {!! Form::text('first_name', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('business.first_name'),
                        ]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                        {!! Form::text('mid_name', null, [
                            'class' => 'form-control',
                        
                            'placeholder' => __('business.mid_name'),
                        ]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('last_name', __('business.last_name') . ':') !!}
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('email', __('business.email') . ':') !!}
                        {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                    </div>
                    <div class="form-group col-md-3">

                        {!! Form::label('profile_picture', __('user.profile_picture') . ':') !!}
                        {!! Form::file('profile_picture', ['class' => 'form-control', 'accept' => 'image/*']) !!}


                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('dob', __('lang_v1.dob') . ':') !!}
                        {!! Form::date('dob', null, ['class' => 'form-control', 'style' => 'height:40px']) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('gender', __('lang_v1.gender') . ':*') !!}
                        {!! Form::select(
                            'gender',
                            ['male' => __('lang_v1.male'), 'female' => __('lang_v1.female'), 'others' => __('lang_v1.others')],
                            !empty($user->gender) ? $user->gender : null,
                            [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'required',
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
                    <div class="clearfix"></div>
                    <div class="form-group col-md-3">
                        {!! Form::label('blood_group', __('lang_v1.blood_group') . ':') !!}
                        {!! Form::select('blood_group', $blood_types, null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.blood_group'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('age', __('lang_v1.age') . ':*') !!}
                        {!! Form::number('age', null, ['class' => 'form-control', 'required', 'placeholder' => __('lang_v1.age')]) !!}
                    </div>

                    <div class="clearfix"></div>
                    <div class="form-group col-md-3">
                        {!! Form::label('contact_number', __('lang_v1.mobile_number') . ':*') !!}
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
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.family_contact_number'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('passport_number', __('business.passport_number') . ':*') !!}
                        {!! Form::text('passport_number', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('business.passport_number'),
                        ]) !!}
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group col-md-3">
                        {!! Form::label('permanent_address', __('lang_v1.permanent_address') . ':') !!}
                        {!! Form::text('permanent_address', !empty($user->permanent_address) ? $user->permanent_address : null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.permanent_address'),
                            'rows' => 3,
                        ]) !!}
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::label('current_address', __('lang_v1.current_address') . ':') !!}
                        {!! Form::text('current_address', !empty($user->current_address) ? $user->current_address : null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.current_address'),
                            'rows' => 3,
                        ]) !!}
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-big"
                                id="submit_user_button">@lang('messages.save')</button>
                        </div>
                    </div>
                @endcomponent
            </div>

        </div>
        {!! Form::close() !!}
    @endsection

    @section('javascript')

        <script type="text/javascript">
            $('form#user_add_form').validate({
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
                                }
                            }
                        }
                    },


                },
                messages: {

                    email: {
                        remote: '{{ __('validation.unique', ['attribute' => __('business.email')]) }}'
                    }
                }
            });
        </script>
    @endsection
