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

        @component('components.widget', ['class' => 'box-primary'])
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
