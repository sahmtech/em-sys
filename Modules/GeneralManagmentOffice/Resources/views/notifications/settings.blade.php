@extends('layouts.app')
@section('title', __('generalmanagement::lang.notification_settings'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('generalmanagement::lang.notification_settings')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget')
            {!! Form::open([
                'url' => route('office.notifications.settings.update'),
                'method' => 'post',
            ]) !!}


            @php
                $notificatinoSettingsArr = [];
                if (!empty($notificatinoSettings)) {
                    $notificatinoSettingsArr = $notificatinoSettings;
                }
            @endphp
            @foreach ($notificatinoSettingsArr as $notificatinoSetting)
                <div class="col-md-12">
                    <div class="box box-primary">

                        <div class="row check_group">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h4>{{ __('lang_v1.' . $notificatinoSetting['notification_type'] . '') }}</h4>
                                </div>
                                <div class="col-md-12">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="check_all input-icheck"> {{ __('role.select_all') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label class="custom_permission_lable">

                                                {!! Form::checkbox(
                                                    'settings[' . $notificatinoSetting['notification_type'] . '][dashboard_enabled]',
                                                    1,
                                                    $notificatinoSetting['dashboard_enabled'],
                                                    ['class' => 'input-icheck'],
                                                ) !!} {{ __('lang_v1.dashboard_enabled') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label class="custom_permission_lable">
                                                {!! Form::checkbox(
                                                    'settings[' . $notificatinoSetting['notification_type'] . '][email_enabled]',
                                                    1,
                                                    $notificatinoSetting['email_enabled'],
                                                    ['class' => 'input-icheck'],
                                                ) !!} {{ __('lang_v1.email_enabled') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-big">@lang('messages.save')</button>
                </div>
            </div>

            {!! Form::close() !!}
        @endcomponent
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).on('change', '#checkbox_all_employees, #checkbox_all_managers',
            function() {
                if ($('#checkbox_all_employees').is(':checked') || $('#checkbox_all_managers').is(
                        ':checked')) {
                    $('#checkbox_specific_users').prop('checked', false);
                    $('#usersSelect').closest('.form-group').hide();
                }
            });
        $(document).on('change', '#checkbox_specific_users',
            function() {
                if ($('#checkbox_specific_users').is(':checked')) {
                    $('#checkbox_all_employees, #checkbox_all_managers').prop('checked', false);
                    $('#usersSelect').closest('.form-group').show();
                } else {
                    $('#usersSelect').closest('.form-group').hide();
                }
            });
        $(document).ready(function() {
            if (!$('#checkbox_specific_users').is(':checked')) {
                $('#usersSelect').closest('.form-group').hide();
            }
        });
    </script>
@endsection
