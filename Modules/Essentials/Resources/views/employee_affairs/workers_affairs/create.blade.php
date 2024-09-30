@extends('layouts.app')

@section('title', __('essentials::lang.add_new_worker'))

@section('content')

    <head>
        <style>
            #video {
                transform: scaleX(-1);
                /* Flip the video horizontally */
            }
        </style>
    </head>
    <section class="content-header">
        <h1>@lang('essentials::lang.add_new_worker')</h1>
    </section>


    <section class="content">
        {!! Form::open(['route' => 'store-worker-affairs', 'enctype' => 'multipart/form-data']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12 box box-primary">
                    <h4>@lang('essentials::lang.basic_info'):</h4>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('emp_number', __('essentials::lang.emp_number') . ':') !!}
                            {!! Form::text('emp_number', null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.emp_number'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                            {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('business.first_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                            {!! Form::text('mid_name', null, [
                                'class' => 'form-control',
                            
                                'placeholder' => __('business.mid_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('last_name', __('business.last_name') . ':') !!}
                            {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                        </div>
                    </div>



                    <div id="workerInput" class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('assigned_to', __('sales::lang.assigned_to') . ':') !!}
                            {!! Form::select('assigned_to', $contacts, null, [
                                'class' => 'form-control select2',
                                'style' => 'height:40px',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('profile_picture', __('user.profile_picture') . ':') !!}
                            {!! Form::file('profile_picture', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="captureButton">@lang('essentials::lang.capture_photo')</button>
                        </div>
                    </div>
                    @include('essentials::employee_affairs.employee_affairs.popup_camera_modal')
                </div>
                @include('essentials::employee_affairs.workers_affairs.personal_info')

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-big"
                            id="submit_user_button">@lang('messages.save')</button>
                    </div>
                </div>




            </div>


        </div>
        {!! Form::close() !!}
    </section>
@stop

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.hijri-date-picker').on('change', function() {
                var hijriDate = $(this).val();


                if (hijriDate) {

                    var gregorianDate = HijriDate.toGregorian(hijriDate, 'YYYY-MM-DD');


                    $('#user_dob').val(gregorianDate);
                } else {

                    $('#user_dob').val('');
                }
            });
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function() {
            $('#userTypeSelect').on('change', function() {
                var userType = $('#userTypeSelect').val();
                if (userType === 'worker') {
                    $('#workerInput').show();
                } else {
                    $('#workerInput').hide();
                }
            });
        });
    </script>
    <script type="text/javascript">
        __page_leave_confirmation('#user_add_form');
        $(document).ready(function() {
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
                            q: params.term,
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
                    password: {
                        required: true,
                        minlength: 5
                    },
                    confirm_password: {
                        equalTo: "#password"
                    },

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


        });
    </script>
@endsection
