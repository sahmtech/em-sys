@extends('layouts.app')

@section('title', __('essentials::lang.edit_employee'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.edit_employee')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action(
                [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'update'],
                [$user->id],
            ),
            'method' => 'PUT',
            'id' => 'user_edit_form',
        ]) !!}

        <div class="col-md-12 box box-primary">
            <h4>@lang('essentials::lang.basic_info'):</h4>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('surname', __('business.prefix') . ':') !!}
                    {!! Form::text('surname', $user->surname, [
                        'class' => 'form-control',
                        'placeholder' => __('business.prefix_placeholder'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                    {!! Form::text('first_name', $user->first_name, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('business.first_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('last_name', __('business.last_name') . ':') !!}
                    {!! Form::text('last_name', $user->last_name, [
                        'class' => 'form-control',
                        'placeholder' => __('business.last_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_type', __('user.user_type') . ':*') !!}
                    {!! Form::select(
                        'user_type',
                        [
                            'manager' => __('user.manager'),
                            'employee' => __('user.employee'),
                            'worker' => __('user.worker'),
                        ],
                        $user->user_type,
                        [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'required',
                            'id' => 'userTypeSelect',
                            'placeholder' => __('user.user_type'),
                        ],
                    ) !!}
                </div>
            </div>
            @if ($user->user_type == 'worker' && !empty($user->assigned_to))
                <div class="form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('project', __('followup::lang.project') . ':*') !!}
                            {!! Form::select('project', $projects, !empty($user->assigned_to) ? $user->assigned_to : null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.project'),
                                'id' => 'project',
                            ]) !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @include('user.edit_profile_form_part', [
            'bank_details' => !empty($user->bank_details) ? json_decode($user->bank_details, true) : null,
        ])

        @if (!empty($form_partials))
            @foreach ($form_partials as $partial)
                {!! $partial !!}
            @endforeach
        @endif

        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-big" id="submit_user_button">@lang('messages.update')</button>
            </div>
        </div>









        {!! Form::close() !!}
    @stop
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {
                __page_leave_confirmation('#user_edit_form');

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
                                q: params.term, // search term
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
            });

            $('form#user_edit_form').validate({
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
                                },
                                user_id: {{ $user->id }}
                            }
                        }
                    },
                    password: {
                        minlength: 5
                    },
                    confirm_password: {
                        equalTo: "#password",
                    },
                    username: {
                        minlength: 5,
                        remote: {
                            url: "/business/register/check-username",
                            type: "post",
                            data: {
                                username: function() {
                                    return $("#username").val();
                                },
                                @if (!empty($username_ext))
                                    username_ext: "{{ $username_ext }}"
                                @endif
                            }
                        }
                    }
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
        </script>
    @endsection
