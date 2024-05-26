@extends('layouts.app')
@section('title', __('generalmanagement::lang.notifications'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('generalmanagement::lang.send_notifications')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('employee_name_filter', __('sales::lang.contact_name') . ':') !!}
                            {!! Form::select('employee_name_filter', $contacts2, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'employee_name_filter',
                            ]) !!}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open(['route' => 'office.notifications.send']) !!}
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        {!! Form::label('notification_to', __('generalmanagement::lang.notification_to') . ':') !!}
                        <div class="radio-group">
                            <div class="col-md-4">
                                <div class="checkbox">

                                    {!! Form::checkbox('checkbox_all_employees', 1, false, [
                                        'class' => ' checkbox_all_employees',
                                        'id' => 'checkbox_all_employees',
                                    ]) !!} {{ __('generalmanagement::lang.all_employees') }}

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="checkbox">

                                    {!! Form::checkbox('checkbox_all_managers', 1, false, [
                                        'class' => ' checkbox_all_managers',
                                        'id' => 'checkbox_all_managers',
                                    ]) !!} {{ __('generalmanagement::lang.all_managers') }}

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="checkbox">

                                    {!! Form::checkbox('checkbox_specific_users', 1, false, [
                                        'class' => ' checkbox_specific_users',
                                        'id' => 'checkbox_specific_users',
                                    ]) !!} {{ __('generalmanagement::lang.specific_users') }}

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <br>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('users', __('generalmanagement::lang.recieved_users') . ':') !!}
                            {!! Form::select('users[]', $users, null, [
                                'class' => 'form-control select2',
                                'style' => 'height:40px',
                                'multiple',
                                'id' => 'usersSelect',
                            ]) !!}
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('notification_title', __('generalmanagement::lang.notification_title') . ':') !!}
                            {!! Form::text('notification_title', null, [
                                'class' => 'form-control',
                                'placeholder' => __('generalmanagement::lang.enter_notification_title'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('notification_text', __('generalmanagement::lang.notification_text') . ':') !!}
                            {!! Form::textarea('message', null, [
                                'class' => 'form-control',
                                'id' => 'messageTextarea',
                                'rows' => 10,
                                'placeholder' => __('generalmanagement::lang.enter_notification_text'),
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary pull-right">@lang('generalmanagement::lang.send')</button>

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
