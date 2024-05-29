@extends('layouts.app')
@section('title', __('request.allRequests'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('request.allRequests')</span>
        </h1>
    </section>

    <head>
        <style>
            .alert {
                animation: fadeOut 5s forwards;
                max-width: 300px;
                margin: 0 auto;
            }

            @keyframes fadeOut {
                to {
                    opacity: 0;
                    visibility: hidden;
                }
            }

            .workflow-circle {
                min-width: 110px;
                height: 110px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
                color: #fff;
                padding: 10px;

            }

            .workflow-arrow {
                position: relative;
                display: inline-block;
                width: 0;
                height: 0;
                margin: 0 10px;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;


            }

            .workflow-circle span {
                margin-top: 5px;

            }

            .workflow-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                white-space: nowrap;
                overflow-x: auto;
                margin-bottom: 20px;
            }

            .workflow-circle.pending {
                background-color: orange;
            }

            .workflow-circle.approved {
                background-color: green;
            }

            .workflow-circle.rejected {
                background-color: red;
            }

            .workflow-circle.grey {
                background-color: grey;
            }

            .pending-arrow,
            .approved-arrow,
            .rejected-arrow,
            .grey-arrow {
                color: #000;
            }

            .department-name {
                text-align: center;
                margin-top: 5px;
                font-weight: bold;
            }
        </style>
    </head>
    <!-- Main content -->
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @else
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    @endif
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.insurances_add_requests'))
                @slot('tool')
                    <div class="box-tools">

                        <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                            data-target="#addRequestModal">
                            <i class="fa fa-plus"></i> @lang('request.create_order')
                        </button>
                    </div>
                @endslot
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>@lang('request.request_number')</th>
                            <th>@lang('request.request_owner')</th>
                            <th>@lang('request.eqama_number')</th>
                            <th>@lang('request.request_type')</th>
                            <th>@lang('request.request_date')</th>
                            <th>@lang('request.status')</th>
                            <th>@lang('request.note')</th>
                            <th>@lang('request.action')</th>
                            <th></th>

                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        {{-- add request --}}
        <div class="modal fade" id="addRequestModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'insurance_storeRequest', 'enctype' => 'multipart/form-data']) !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('request.create_order')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">



                            <div class="form-group col-md-6">
                                {!! Form::label('type', __('essentials::lang.type') . ':*') !!}
                                {!! Form::select(
                                    'type',
                                    collect($requestTypes)->mapWithKeys(fn($type, $id) => [$id => trans("request.$type")])->toArray(),
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'required',
                                        'style' => 'height: 40px',
                                        'placeholder' => __('essentials::lang.select_type'),
                                        'id' => 'requestType',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('user_id', __('essentials::lang.name') . ':*') !!}
                                {!! Form::select('user_id[]', $users, null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'required',
                                    'id' => 'worker',
                                    'style' => 'height: 60px; width: 250px;',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="leaveType" style="display: none;">
                                {!! Form::label('leaveType', __('request.leaveType') . ':*') !!}
                                {!! Form::select('leaveType', $leaveTypes, null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.select_leaveType'),
                                    'id' => 'leaveType',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6" id="start_date" style="display: none;">
                                {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                                {!! Form::date('start_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('essentials::lang.start_date'),
                                    'id' => 'startDateField',
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6" id="end_date" style="display: none;">
                                {!! Form::label('end_date', __('essentials::lang.end_date') . ':*') !!}
                                {!! Form::date('end_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('essentials::lang.end_date'),
                                    'id' => 'endDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="escape_time" style="display: none;">
                                {!! Form::label('escape_time', __('request.escape_time') . ':*') !!}
                                {!! Form::time('escape_time', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.escape_time'),
                                    'id' => 'escapeTimeField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="exit_date" style="display: none;">
                                {!! Form::label('exit_date', __('request.exit_date') . ':*') !!}
                                {!! Form::date('exit_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.exit_date'),
                                    'id' => 'exit_dateField',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6" id="return_date" style="display: none;">
                                {!! Form::label('return_date', __('request.return_date') . ':*') !!}
                                {!! Form::date('return_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.return_date'),
                                    'id' => 'return_dateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="escape_date" style="display: none;">
                                {!! Form::label('escape_date', __('essentials::lang.escape_date') . ':*') !!}
                                {!! Form::date('escape_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('essentials::lang.escape_date'),
                                    'id' => 'escapeDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="workInjuriesDate" style="display: none;">
                                {!! Form::label('workInjuriesDate', __('request.workInjuriesDate') . ':*') !!}
                                {!! Form::date('workInjuriesDate', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.workInjuriesDate'),
                                    'id' => 'workInjuriesDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="resEditType" style="display: none;">
                                {!! Form::label('resEditType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'resEditType',
                                    [
                                        'name' => __('request.name'),
                                        'religion' => __('request.religion'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('essentials::lang.select_type'),
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="atmType" style="display: none;">
                                {!! Form::label('atmType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'atmType',
                                    [
                                        'release' => __('request.release'),
                                        're_issuing' => __('request.re_issuing'),
                                        'update' => __('request.update_info'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('essentials::lang.select_type'),
                                        'id' => 'atmType',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="baladyType" style="display: none;">
                                {!! Form::label('baladyType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'baladyType',
                                    [
                                        'renew' => __('request.renew'),
                                        'issuance' => __('request.issuance'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('essentials::lang.select_type'),
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="ins_class" style="display: none;">
                                {!! Form::label('ins_class', __('request.insurance_class') . ':*') !!}
                                {!! Form::select('ins_class', $classes, null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.select_class'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="main_reason" style="display: none;">
                                {!! Form::label('main_reason', __('request.main_reason') . ':*') !!}
                                {!! Form::select('main_reason', $main_reasons, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_reason'),
                                    'id' => 'mainReasonSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="sub_reason_container" style="display: none;">
                                {!! Form::label('sub_reason', __('request.sub_reason') . ':*') !!}
                                {!! Form::select('sub_reason', [], null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_sub_reason'),
                                    'id' => 'subReasonSelect',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6" id="amount" style="display: none;">
                                {!! Form::label('amount', __('request.advSalaryAmount') . ':*') !!}
                                {!! Form::number('amount', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.advSalaryAmount'),
                                    'id' => 'advSalaryAmountField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="visa_number" style="display: none;">
                                {!! Form::label('visa_number', __('request.visa_number') . ':*') !!}
                                {!! Form::number('visa_number', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.visa_number'),
                                    'id' => 'visa_numberField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="installmentsNumber" style="display: none;">
                                {!! Form::label('installmentsNumber', __('request.installmentsNumber') . ':*') !!}
                                {!! Form::number('installmentsNumber', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.installmentsNumber'),
                                    'id' => 'installmentsNumberField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="monthlyInstallment" style="display: none;">
                                {!! Form::label('monthlyInstallment', __('request.monthlyInstallment') . ':*') !!}
                                {!! Form::number('monthlyInstallment', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.monthlyInstallment'),
                                    'id' => 'monthlyInstallmentField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="authorized_entity" style="display: none;">
                                {!! Form::label('authorized_entity', __('request.authorized_entity') . ':*') !!}
                                {!! Form::text('authorized_entity', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.authorized_entity'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="commissioner_info" style="display: none;">
                                {!! Form::label('commissioner_info', __('request.commissioner_info') . ':') !!}
                                {!! Form::text('commissioner_info', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.commissioner_info'),
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6" id="trip_type" style="display: none;">
                                {!! Form::label('trip_type', __('request.trip_type') . ':*') !!}
                                {!! Form::select(
                                    'trip_type',
                                    [
                                        'round' => __('request.round_trip'),
                                        'one_way' => __('request.one_way_trip'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.select_type'),
                                        'id' => 'trip_typeField',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="Take_off_location" style="display: none;">
                                {!! Form::label('Take_off_location', __('request.Take_off_location') . ':') !!}
                                {!! Form::text('Take_off_location', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.Take_off_location'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="destination" style="display: none;">
                                {!! Form::label('destination', __('request.destination') . ':*') !!}
                                {!! Form::text('destination', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.destination'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="weight_of_furniture" style="display: none;">
                                {!! Form::label('weight_of_furniture', __('request.weight_of_furniture') . ':') !!}
                                {!! Form::text('weight_of_furniture', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.weight_of_furniture'),
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="time_of_take_off" style="display: none;">
                                {!! Form::label('time_of_take_off', __('request.time_of_take_off') . ':*') !!}
                                {!! Form::time('time_of_take_off', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.time_of_take_off'),
                                    'id' => 'time_of_take_offField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="date_of_take_off" style="display: none;">
                                {!! Form::label('date_of_take_off', __('request.date_of_take_off') . ':*') !!}
                                {!! Form::date('date_of_take_off', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.date_of_take_off'),
                                    'id' => 'date_of_take_offField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="return_date_of_trip" style="display: none;">
                                {!! Form::label('return_date_of_trip', __('request.return_date') . ':*') !!}
                                {!! Form::date('return_date_of_trip', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.return_date_of_trip'),
                                    'id' => 'return_dateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('note', __('request.note') . ':') !!}
                                {!! Form::textarea('note', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.note'),
                                    'rows' => 3,
                                ]) !!}
                            </div>

                            {{-- <div class="form-group col-md-6" id="reason" style="display: block;">
                            {!! Form::label('reason', __('request.reason') . ':') !!}
                            {!! Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('request.reason'), 'rows' => 3]) !!}
                        </div> --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('attachment', __('request.attachment') . ':') !!}
                                {!! Form::file('attachment', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.attachment'),
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        {{-- view request --}}
        <div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('request.view_request')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="workflow-container" id="workflow-container">
                                <!-- Workflow content here -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('request.worker_details')</h4>
                                <ul id="worker-list">
                                    <!-- Worker details content here -->
                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('request.activites')</h4>
                                <ul id="activities-list">
                                    <!-- Activities will be dynamically added here -->
                                </ul>
                            </div>
                            <div class="col-md-6">

                                <h4>@lang('request.attachments')</h4>
                                <ul id="attachments-list">

                                </ul>
                            </div>
                        </div>

                        <!-- Attachment Form -->
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="attachment">
                                    <h4>@lang('request.attachment')</h4>
                                </label>
                                <input type="file" class="form-control" style="width: 250px;" id="attachment"
                                    name="attachment">
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        </form>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- return request --}}
        <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnModalLabel">@lang('request.return_the_request')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="returnModalForm">
                            <div class="form-group">
                                <label for="reasonInput">@lang('request.reason')</label>
                                <input type="text" class="form-control" id="reasonInput" required>
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('request.update')</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('request.close')</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- edit request --}}
        <div class="modal fade" id="editRequestModal" tabindex="-1" role="dialog"
            aria-labelledby="editRequestModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'editRequestForm']) !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('request.edit_order')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <!-- Show but not editable fields -->
                            <div class="form-group col-md-6">
                                {!! Form::label('type', __('request.type')) !!}
                                {!! Form::text('type', null, [
                                    'class' => 'form-control',
                                    'readonly',
                                    'id' => 'editRequestType',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('user_id', __('request.name')) !!}
                                {!! Form::text('user_id', null, [
                                    'class' => 'form-control',
                                    'readonly',
                                    'id' => 'editRequestUser',
                                ]) !!}
                            </div>

                            <!-- Editable fields -->
                            <div class="form-group col-md-6" id="edit_start_date" style="display: none;">
                                {!! Form::label('start_date', __('request.start_date') . ':*') !!}
                                {!! Form::date('start_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'editStartDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_end_date" style="display: none;">
                                {!! Form::label('end_date', __('request.end_date') . ':*') !!}
                                {!! Form::date('end_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'editEndDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_leaveType" style="display: none;">
                                {!! Form::label('leaveType', __('request.leaveType') . ':*') !!}
                                {!! Form::select('leaveType', $leaveTypes, null, [
                                    'class' => 'form-control',
                                    'id' => 'editLeaveTypeField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_escape_time" style="display: none;">
                                {!! Form::label('escape_time', __('request.escape_time') . ':*') !!}
                                {!! Form::time('escape_time', null, [
                                    'class' => 'form-control',
                                    'id' => 'editEscapeTimeField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_escape_date" style="display: none;">
                                {!! Form::label('escape_date', __('request.escape_date') . ':*') !!}
                                {!! Form::date('escape_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'editEscapeDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_exit_date" style="display: none;">
                                {!! Form::label('exit_date', __('request.exit_date') . ':*') !!}
                                {!! Form::date('exit_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'editExitDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_return_date" style="display: none;">
                                {!! Form::label('return_date', __('request.return_date') . ':*') !!}
                                {!! Form::date('return_date', null, [
                                    'class' => 'form-control',
                                    'id' => 'editReturnDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_workInjuriesDate" style="display: none;">
                                {!! Form::label('workInjuriesDate', __('request.workInjuriesDate') . ':*') !!}
                                {!! Form::date('workInjuriesDate', null, [
                                    'class' => 'form-control',
                                    'id' => 'editWorkInjuriesDateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_resEditType" style="display: none;">
                                {!! Form::label('resEditType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'resEditType',
                                    [
                                        'name' => __('request.name'),
                                        'religion' => __('request.religion'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'id' => 'editResEditTypeField',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_atmType" style="display: none;">
                                {!! Form::label('atmType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'atmType',
                                    [
                                        'release' => __('request.release'),
                                        're_issuing' => __('request.re_issuing'),
                                        'update' => __('request.update_info'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'id' => 'editAtmTypeField',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_baladyType" style="display: none;">
                                {!! Form::label('baladyType', __('request.request_type') . ':*') !!}
                                {!! Form::select(
                                    'baladyType',
                                    [
                                        'renew' => __('request.renew'),
                                        'issuance' => __('request.issuance'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'id' => 'editBaladyTypeField',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_ins_class" style="display: none;">
                                {!! Form::label('ins_class', __('request.insurance_class') . ':*') !!}
                                {!! Form::select('ins_class', $classes, null, [
                                    'class' => 'form-control',
                                    'id' => 'editInsClassField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_main_reason" style="display: none;">
                                {!! Form::label('main_reason', __('request.main_reason') . ':*') !!}
                                {!! Form::select('main_reason', $main_reasons, null, [
                                    'class' => 'form-control',
                                    'id' => 'editMainReasonSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_sub_reason_container" style="display: none;">
                                {!! Form::label('sub_reason', __('request.sub_reason') . ':*') !!}
                                {!! Form::select('sub_reason', [], null, [
                                    'class' => 'form-control',
                                    'id' => 'editSubReasonSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_amount" style="display: none;">
                                {!! Form::label('amount', __('request.advSalaryAmount') . ':*') !!}
                                {!! Form::number('amount', null, [
                                    'class' => 'form-control',
                                    'id' => 'editAdvSalaryAmountField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_visa_number" style="display: none;">
                                {!! Form::label('visa_number', __('request.visa_number') . ':*') !!}
                                {!! Form::number('visa_number', null, [
                                    'class' => 'form-control',
                                    'id' => 'editVisaNumberField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_installmentsNumber" style="display: none;">
                                {!! Form::label('installmentsNumber', __('request.installmentsNumber') . ':*') !!}
                                {!! Form::number('installmentsNumber', null, [
                                    'class' => 'form-control',
                                    'id' => 'editInstallmentsNumberField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_monthlyInstallment" style="display: none;">
                                {!! Form::label('monthlyInstallment', __('request.monthlyInstallment') . ':*') !!}
                                {!! Form::number('monthlyInstallment', null, [
                                    'class' => 'form-control',
                                    'id' => 'editMonthlyInstallmentField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_authorized_entity" style="display: none;">
                                {!! Form::label('authorized_entity', __('request.authorized_entity') . ':*') !!}
                                {!! Form::text('authorized_entity', null, [
                                    'class' => 'form-control',
                                    'id' => 'editAuthorizedEntity',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_commissioner_info" style="display: none;">
                                {!! Form::label('commissioner_info', __('request.commissioner_info') . ':') !!}
                                {!! Form::text('commissioner_info', null, [
                                    'class' => 'form-control',
                                    'id' => 'editCommissionerInfo',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_trip_type">
                                {!! Form::label('trip_type', __('request.trip_type') . ':*') !!}
                                {!! Form::select(
                                    'trip_type',
                                    [
                                        'round' => __('request.round_trip'),
                                        'one_way' => __('request.one_way_trip'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'id' => 'editTripTypeField',
                                    ],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_take_off_location" style="display: none;">
                                {!! Form::label('Take_off_location', __('request.Take_off_location') . ':') !!}
                                {!! Form::text('Take_off_location', null, [
                                    'class' => 'form-control',
                                    'id' => 'editTakeOffLocation',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_destination" style="display: none;">
                                {!! Form::label('destination', __('request.destination') . ':*') !!}
                                {!! Form::text('destination', null, [
                                    'class' => 'form-control',
                                    'id' => 'editDestination',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_weight_of_furniture" style="display: none;">
                                {!! Form::label('weight_of_furniture', __('request.weight_of_furniture') . ':') !!}
                                {!! Form::text('weight_of_furniture', null, [
                                    'class' => 'form-control',
                                    'id' => 'editWeightOfFurniture',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_time_of_take_off" style="display: none;">
                                {!! Form::label('time_of_take_off', __('request.time_of_take_off') . ':*') !!}
                                {!! Form::time('time_of_take_off', null, [
                                    'class' => 'form-control',
                                    'id' => 'editTimeOfTakeOffField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_date_of_take_off" style="display: none;">
                                {!! Form::label('date_of_take_off', __('request.date_of_take_off') . ':*') !!}
                                {!! Form::date('date_of_take_off', null, [
                                    'class' => 'form-control',
                                    'id' => 'editDateOfTakeOffField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_return_date_of_trip" style="display: none;">
                                {!! Form::label('return_date_of_trip', __('request.return_date') . ':*') !!}
                                {!! Form::date('return_date_of_trip', null, [
                                    'class' => 'form-control',
                                    'id' => 'editReturnDateOfTripField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('note', __('request.note') . ':') !!}
                                {!! Form::textarea('note', null, [
                                    'class' => 'form-control',
                                    'id' => 'editNote',
                                    'rows' => 3,
                                ]) !!}
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        @include('request.change_request_status')

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#requestType').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });
            });





            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('insurance_requests') }}"
                },

                columns: [

                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },

                    {
                        data: 'request_type_id',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('request.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('request.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('request.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('request.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('request.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('request.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('request.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('request.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('request.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('request.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('request.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('request.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('request.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('request.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('request.chamberRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('request.WarningRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('request.cancleContractRequest')';
                            } else if (data === 'passportRenewal') {
                                return '@lang('request.passportRenewal')';
                            } else if (data === 'AjirAsked') {
                                return '@lang('request.AjirAsked')';
                            } else if (data === 'AlternativeWorker') {
                                return '@lang('request.AlternativeWorker')';
                            } else if (data === 'TransferringGuaranteeFromExternalClient') {
                                return '@lang('request.TransferringGuaranteeFromExternalClient')';
                            } else if (data === 'Permit') {
                                return '@lang('request.Permit')';
                            } else if (data === 'FamilyInsurace') {
                                return '@lang('request.FamilyInsurace')';
                            } else if (data === 'Ajir_link') {
                                return '@lang('request.Ajir_link')';
                            } else if (data === 'ticketReservationRequest') {
                                return '@lang('request.ticketReservationRequest')';
                            } else if (data === 'authorizationRequest') {
                                return '@lang('request.authorizationRequest')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },

                    {
                        data: 'can_return',

                    },
                    {
                        data: 'is_new',
                        render: function(data, type, row) {
                            var buttonsHtml = '';
                            var userId = "{{ auth()->user()->id }}";

                            if (data != '0' && row.created_by == userId) {
                                buttonsHtml +=
                                    `<button class="btn btn-primary edit-request" data-id="${row.id}" data-type="${row.request_type_id}">@lang('request.edit')</button>`;
                                buttonsHtml +=
                                    `<button class="btn btn-danger delete-request" data-id="${row.id}">@lang('request.delete')</button>`;
                            }
                            return buttonsHtml;
                        }
                    }



                ],
            });

            $(document).on('click', 'a.change_status', function(e) {
                e.preventDefault();

                $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value'))
                    .change();
                $('#change_status_modal').find('#request_id').val($(this).data('request-id'));
                $('#change_status_modal').modal('show');


            });


            $(document).on('submit', 'form#change_status_form', function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector('.update-offer-status'));
                ladda.start();
                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        ladda.stop();
                        if (result.success == true) {
                            $('div#change_status_modal').modal('hide');
                            toastr.success(result.msg);
                            requests_table.ajax.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            $('#requests_table').on('click', '.btn-return', function() {

                var requestId = $(this).data('request-id');
                $('#returnModal').modal('show');
                $('#returnModal').data('id', requestId);
            });


            $('#returnModalForm').submit(function(e) {
                e.preventDefault();

                var requestId = $('#returnModal').data('id');
                var reason = $('#reasonInput').val();

                $.ajax({
                    url: "{{ route('returnRequest') }}",
                    method: "POST",
                    data: {
                        requestId: requestId,
                        reason: reason
                    },
                    success: function(result) {

                        if (result.success == true) {
                            $('#returnModal').modal('hide');
                            toastr.success(result.msg);
                            requests_table.ajax.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });


            $(document).on('click', '.btn-view-request', function() {
                var requestId = $(this).data('request-id');
                console.log(requestId);

                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'
                            .replace(
                                ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            console.log(response);

                            var workflowContainer = $('#workflow-container');
                            var activitiesList = $('#activities-list');
                            var workerList = $('#worker-list');
                            var attachmentsList = $('#attachments-list');

                            workflowContainer.html('');
                            workerList.html('');
                            activitiesList.html('');
                            attachmentsList.html('');

                            for (var i = 0; i < response.workflow.length; i++) {

                                var status = response.workflow[i].status ? response.workflow[i]
                                    .status.toLowerCase() : 'grey';
                                var circle = '<div class="workflow-circle ' + status + '">';
                                circle += '<p class="department-name">' + response.workflow[i]
                                    .department + '</p>';
                                circle += '</div>';

                                workflowContainer.append(circle);


                                if (i < response.workflow.length - 1) {
                                    workflowContainer.append(
                                        '<i class="fas fa-arrow-left workflow-arrow ' +
                                        status + '-arrow"></i>');
                                }
                            }

                            //  worker info
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.worker_name') }}' + ': ' + response
                                .user_info.worker_full_name + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.nationality') }}' + ': ' + response
                                .user_info.nationality + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.project_name') }}' + ': ' + response
                                .user_info.assigned_to + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.eqama_number') }}' + ': ' + response
                                .user_info.id_proof_number + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.contract_end_date') }}' + ': ' +
                                response.user_info.contract_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.eqama_end_date') }}' + ': ' +
                                response.user_info.eqama_end_date + '</p>');
                            workerList.append('<p class="worker-info">' +
                                '{{ __('request.passport_number') }}' + ': ' +
                                response.user_info.passport_number + '</p>');



                            //activities

                            // activitiesList.append('<p class="worker-info">' + '{{ __('request.created_by') }}' + ': ' + created_user_info.created_user_full_name + '</p>');    

                            for (var j = 0; j < response.followup_processes.length; j++) {
                                var activity = '<li>';

                                activity += '<p>' +
                                    '{{ __('request.created_department_name') }}' +
                                    ': ' +
                                    response.request_info.started_depatment.name + '</p>';
                                activity += '<p>' +
                                    '{{ __('request.created_user_name') }}' +
                                    ': ' +
                                    response.created_user_info.created_user_full_name + '</p>';
                                activity += '<p>' +
                                    '{{ __('essentials::lang.department_name') }}' + ': ' +
                                    response.followup_processes[j].department.name;

                                activity +=
                                    '<p class="{{ __('essentials::lang.status') }} ' +
                                    response.followup_processes[j].status.toLowerCase() +
                                    '">' +
                                    '<strong>{{ __('essentials::lang.status') }}:</strong> ' +
                                    response.followup_processes[j].status + '</p>';

                                activity += '<p>' + '{{ __('essentials::lang.reason') }}' +
                                    ': ';
                                if (response.followup_processes[j].reason) {
                                    activity += '<strong>' + response.followup_processes[j]
                                        .reason + '</strong>';
                                } else {
                                    activity += '{{ __('essentials::lang.not_exist') }}';
                                }
                                activity += '<p>' + '{{ __('essentials::lang.note') }}' +
                                    ': ';
                                if (response.followup_processes[j].status_note) {
                                    activity += '<strong>' + response.followup_processes[j]
                                        .status_note + '</strong>';
                                } else {
                                    activity += '{{ __('essentials::lang.not_exist') }}';
                                }
                                activity += '</p>';
                                activity += '<p style="color: green;">' +
                                    '{{ __('essentials::lang.updated_by') }}' + ': ' + (
                                        response.followup_processes[j].updated_by ||
                                        '{{ __('essentials::lang.not_exist') }}') + '</p>';


                                activity += '</li>';
                                activitiesList.append(activity);
                            }
                            for (var j = 0; j < response.attachments.length; j++) {
                                var attachment = '<li>';

                                attachment += '<p>';


                                attachment += '<a href="{{ url('uploads') }}/' + response
                                    .attachments[j].file_path +
                                    '" target="_blank" onclick="openAttachment(\'' + response
                                    .attachments[j].file_path + '\', ' + (j + 1) + ')">' +
                                    '{{ trans('request.attach') }} ' + (j + 1) + '</a>';

                                attachment += '</p>';
                                attachment += '</li>';

                                attachmentsList.append(attachment);
                            }
                            $('#attachmentForm').attr('action',
                                '{{ route('saveAttachment', ['requestId' => ':requestId']) }}'
                                .replace(':requestId', response.request_info.id));

                            $('#attachmentForm input[name="requestId"]').val(requestId);


                            $('#requestModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });




        });
    </script>

    <script>
        $(document).ready(function() {
            var users = @json($users);
            var mainReasonSelect = $('#mainReasonSelect');
            var subReasonContainer = $('#sub_reason_container');
            var subReasonSelect = $('#subReasonSelect');

            function fetchUsersWithSaudiNationality() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');



                $.ajax({
                    url: '/get-non-saudi-users',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        users: @json($users)
                    },
                    success: function(data) {
                        console.log(data.users);
                        var userSelect = $('#worker');
                        userSelect.empty();

                        $.each(data.users, function(key, value) {
                            userSelect.append($('<option>', {
                                value: key,
                                text: value
                            }));
                        });


                        userSelect.trigger('change');
                    },
                    error: function(xhr) {

                        console.log('Error:', xhr.responseText);
                    }
                });
            }
            mainReasonSelect.on('change', function() {
                var selectedMainReason = $(this).val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                console.log(selectedMainReason);
                $.ajax({
                    url: '{{ route('getSubReasons') }}',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        main_reason: selectedMainReason
                    },
                    success: function(data) {
                        subReasonSelect.empty();

                        if (data.sub_reasons.length > 0) {
                            subReasonContainer.show();

                            $.each(data.sub_reasons, function(index, subReason) {
                                subReasonSelect.append($('<option>', {
                                    value: subReason.id,
                                    text: subReason.name
                                }));
                            });
                        } else {
                            subReasonContainer.hide();
                        }
                    }
                });

            });

            $('#requestType').change(handleTypeChange);

            function handleTypeChange() {
                var selectedId = $('#requestType').val();

                $.ajax({
                    url: '/get-request-type/' + selectedId,
                    type: 'GET',
                    success: function(response) {
                        var selectedType = response.type;

                        if (selectedType === 'leavesAndDepartures') {
                            $('#start_date').show();

                        } else {
                            $('#start_date').hide();
                        }

                        if (selectedType === 'leavesAndDepartures') {
                            $('#end_date').show();
                        } else {
                            $('#end_date').hide();
                        }
                        if (selectedType === 'returnRequest') {
                            $('#exit_date').show();
                            $('#return_date').show();
                            fetchUsersWithSaudiNationality();

                        } else {
                            $('#exit_date').hide();
                            $('#return_date').hide();

                        }
                        if (selectedType === 'leavesAndDepartures') {
                            $('#leaveType').show();
                        } else {
                            $('#leaveType').hide();
                        }
                        if (selectedType === 'workInjuriesRequest') {
                            $('#workInjuriesDate').show();
                        } else {
                            $('#workInjuriesDate').hide();
                        }


                        if (selectedType === 'escapeRequest') {
                            $('#escape_time').show();
                            $('#escape_date').show();
                            fetchUsersWithSaudiNationality();
                        } else {
                            $('#escape_time').hide();
                            $('#escape_date').hide();
                        }
                        if (selectedType === 'advanceSalary') {
                            $('#installmentsNumber').show();
                            $('#monthlyInstallment').show();
                            $('#amount').show();

                        } else {
                            $('#installmentsNumber').hide();
                            $('#monthlyInstallment').hide();
                            $('#amount').hide();
                        }
                        if (selectedType === 'authorizationRequest') {
                            $('#commissioner_info').show();
                            $('#authorized_entity').show();

                        } else {
                            $('#commissioner_info').hide();
                            $('#authorized_entity').hide();
                        }
                        if (selectedType === 'ticketReservationRequest') {
                            $('#trip_type').show();
                            $('#Take_off_location').show();
                            $('#destination').show();
                            $('#weight_of_furniture').show();
                            $('#date_of_take_off').show();
                            $('#time_of_take_off').show();
                            $('#trip_typeField').change(function() {
                                if ($(this).val() === 'round') {
                                    $('#return_date_of_trip').show();
                                } else {
                                    $('#return_date_of_trip').hide();
                                }
                            });
                        } else {
                            $('#trip_type').hide();
                            $('#Take_off_location').hide();
                            $('#destination').hide();
                            $('#weight_of_furniture').hide();
                            $('#date_of_take_off').hide();
                            $('#time_of_take_off').hide();
                        }
                        if (selectedType === 'residenceEditRequest') {
                            $('#resEditType').show();
                            fetchUsersWithSaudiNationality();


                        } else {
                            $('#resEditType').hide();

                        }
                        if (selectedType === 'baladyCardRequest') {
                            $('#baladyType').show();


                        } else {
                            $('#baladyType').hide();

                        }
                        if (selectedType === 'insuranceUpgradeRequest') {
                            $('#ins_class').show();


                        } else {
                            $('#ins_class').hide();

                        }
                        if (selectedType === 'cancleContractRequest') {
                            $('#main_reason').show();


                        } else {
                            $('#main_reason').hide();

                        }
                        if (selectedType === 'mofaRequest') {
                            $('#visa_number').show();

                        } else {
                            $('#visa_number').hide();

                        }
                        if (selectedType === 'atmCard') {
                            $('#atmType').show();


                        } else {
                            $('#atmType').hide();

                        }
                        if (selectedType === 'exitRequest') {
                            fetchUsersWithSaudiNationality();

                        }

                        if (selectedType === 'passportRenewal') {
                            fetchUsersWithSaudiNationality();

                        }


                    },
                    error: function(xhr) {

                        console.log('Error:', xhr.responseText);
                    }
                });
            }

            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#worker').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });

            });





        });
    </script>

    <script>
        $(document).ready(function() {
            $(document).on('change', '.task-checkbox', function() {
                var taskId = $(this).data('task-id');

                var isChecked = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '/update-task-status',
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        taskId: taskId,
                        isDone: isChecked
                    },
                    success: function(response) {
                        window.location.reload();

                        console.log('Task status updated successfully.');
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to update task status.');
                    }
                });
            });
        });
    </script>
    <script>
        const requestTypeTranslations = {
            'exitRequest': '@lang('request.exitRequest')',
            'returnRequest': '@lang('request.returnRequest')',
            'escapeRequest': '@lang('request.escapeRequest')',
            'advanceSalary': '@lang('request.advanceSalary')',
            'leavesAndDepartures': '@lang('request.leavesAndDepartures')',
            'atmCard': '@lang('request.atmCard')',
            'residenceRenewal': '@lang('request.residenceRenewal')',
            'workerTransfer': '@lang('request.workerTransfer')',
            'residenceCard': '@lang('request.residenceCard')',
            'workInjuriesRequest': '@lang('request.workInjuriesRequest')',
            'residenceEditRequest': '@lang('request.residenceEditRequest')',
            'baladyCardRequest': '@lang('request.baladyCardRequest')',
            'mofaRequest': '@lang('request.mofaRequest')',
            'insuranceUpgradeRequest': '@lang('request.insuranceUpgradeRequest')',
            'chamberRequest': '@lang('request.chamberRequest')',
            'WarningRequest': '@lang('request.WarningRequest')',
            'cancleContractRequest': '@lang('request.cancleContractRequest')',
            'passportRenewal': '@lang('request.passportRenewal')',
            'AjirAsked': '@lang('request.AjirAsked')',
            'AlternativeWorker': '@lang('request.AlternativeWorker')',
            'TransferringGuaranteeFromExternalClient': '@lang('request.TransferringGuaranteeFromExternalClient')',
            'Permit': '@lang('request.Permit')',
            'FamilyInsurace': '@lang('request.FamilyInsurace')',
            'Ajir_link': '@lang('request.Ajir_link')',
            'ticketReservationRequest': '@lang('request.ticketReservationRequest')',
            'authorizationRequest': '@lang('request.authorizationRequest')',
        };
        $('#requests_table').on('click', '.edit-request', function() {
            var requestId = $(this).data('id');
            var requestType = $(this).data('type');


            $('#editRequestForm').attr('action', `/update-request/${requestId}`);

            $.ajax({
                url: `/get-request/${requestId}`,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    console.log(response.requestType.type);

                    populateEditModal(response.request, response.requestType, response.related_to_user);
                    handleEditTypeChange(response.requestType.type, response.request);
                    $('#editRequestModal').modal('show');
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });
        });

        function populateEditModal(request, requestType, related_to_user) {

            $('#editRequestType').val(requestTypeTranslations[requestType.type]);
            $('#editRequestUser').val(related_to_user.first_name + ' ' + related_to_user
                .last_name);
            $('#editStartDateField').val(request.start_date) ?? null;
            $('#editEndDateField').val(request.end_date) ?? null;
            $('#editExitDateField').val(request.start_date) ?? null;
            $('#editReturnDateField').val(request.end_date) ?? null;
            $('#editEscapeTimeField').val(request.escape_time) ?? null;
            $('#editEscapeDateField').val(request.start_date) ?? null;
            $('#editWorkInjuriesDateField').val(request.workInjuriesDate) ?? null;
            $('#editResEditTypeField').val(request.resCardEditType) ?? null;
            $('#editAtmTypeField').val(request.atmCardType) ?? null;
            $('#editBaladyTypeField').val(request.baladyCardType) ?? null;
            $('#editInsClassField').val(request.insurance_classes_id) ?? null;
            $('#editMainReasonSelect').val(request.contract_main_reason_id) ?? null;
            $('#editSubReasonSelect').val(request.contract_sub_reason_id) ?? null;
            $('#editAdvSalaryAmountField').val(request.advSalaryAmount) ?? null;
            $('#editVisaNumberField').val(request.visa_number) ?? null;
            $('#editInstallmentsNumberField').val(request.installmentsNumber) ?? null;
            $('#editMonthlyInstallmentField').val(request.monthlyInstallment) ?? null;
            $('#editAuthorizedEntity').val(request.authorized_entity) ?? null;
            $('#editCommissionerInfo').val(request.commissioner_info) ?? null;
            $('#editTripTypeField').val(request.trip_type) ?? null;
            $('#editTakeOffLocation').val(request.Take_off_location) ?? null;
            $('#editDestination').val(request.destination) ?? null;
            $('#editWeightOfFurniture').val(request.weight_of_furniture) ?? null;
            $('#editTimeOfTakeOffField').val(request.time_of_take_off) ?? null;
            $('#editDateOfTakeOffField').val(request.date_of_take_off) ?? null;
            $('#editReturnDateOfTripField').val(request.return_date) ?? null;
            $('#editNote').val(request.note) ?? null;

            if (request.trip_type === 'round') {
                $('#edit_return_date_of_trip').show();
                $('#editReturnDateOfTripField').val(request.return_date);
            } else {
                $('#edit_return_date_of_trip').hide();
                $('#editReturnDateOfTripField').val(''); // Clear the field if hidden
            }

            // Attach change event handler to #editTripTypeField to handle dynamic changes
            $('#editTripTypeField').off('change').on('change', function() {
                if ($(this).val() === 'round') {
                    $('#edit_return_date_of_trip').show();
                } else {
                    $('#edit_return_date_of_trip').hide();
                    $('#editReturnDateOfTripField').val(''); // Clear the field if hidden
                }
            });
        }


        function handleEditTypeChange(requestType, request) {

            $('#edit_start_date, #edit_end_date, #edit_leaveType, #edit_workInjuriesDate, #edit_escape_time, #edit_escape_date, #edit_exit_date, #edit_return_date, #edit_installmentsNumber, #edit_monthlyInstallment, #edit_amount, #edit_commissioner_info, #edit_authorized_entity, #edit_trip_type, #edit_take_off_location, #edit_destination, #edit_weight_of_furniture, #edit_date_of_take_off, #edit_time_of_take_off, #edit_return_date_of_trip, #edit_resEditType, #edit_baladyType, #edit_ins_class, #edit_main_reason, #edit_visa_number, #edit_atmType')
                .hide();

            switch (requestType) {
                case 'leavesAndDepartures':
                    $('#edit_start_date, #edit_end_date, #edit_leaveType').show();
                    break;
                case 'returnRequest':
                    $('#edit_exit_date, #edit_return_date').show();
                    break;
                case 'workInjuriesRequest':
                    $('#edit_workInjuriesDate').show();
                    break;
                case 'escapeRequest':
                    $('#edit_escape_time, #edit_escape_date').show();
                    break;
                case 'advanceSalary':
                    $('#edit_installmentsNumber, #edit_monthlyInstallment, #edit_amount').show();
                    break;
                case 'authorizationRequest':
                    $('#edit_commissioner_info, #edit_authorized_entity').show();
                    break;
                case 'ticketReservationRequest':
                    $('#edit_trip_type, #edit_take_off_location, #edit_destination, #edit_weight_of_furniture, #edit_date_of_take_off, #edit_time_of_take_off')
                        .show();
                    $('#editTripTypeField').off('change').on('change', function() {
                        if ($(this).val() === 'round') {
                            $('#edit_return_date_of_trip').show();
                            $('#editReturnDateOfTripField').val(request.return_date);
                        } else {
                            $('#edit_return_date_of_trip').hide();
                            $('#editReturnDateOfTripField').val('');
                        }
                    }).trigger('change');
                    break;
                case 'residenceEditRequest':
                    $('#edit_resEditType').show();
                    break;
                case 'baladyCardRequest':
                    $('#edit_baladyType').show();
                    break;
                case 'insuranceUpgradeRequest':
                    $('#edit_ins_class').show();
                    break;
                case 'cancleContractRequest':
                    $('#edit_main_reason').show();
                    break;
                case 'mofaRequest':
                    $('#edit_visa_number').show();
                    break;
                case 'atmCard':
                    $('#edit_atmType').show();
                    break;
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#editRequestForm').on('submit', function(event) {
                // Check if fields are hidden and set their value to null
                if ($('#edit_resEditType').is(':hidden')) {
                    $('#editResEditTypeField').val(null);
                }
                if ($('#edit_atmType').is(':hidden')) {
                    $('#editAtmTypeField').val(null);
                }
                if ($('#edit_baladyType').is(':hidden')) {
                    $('#editBaladyTypeField').val(null);
                }
                if ($('#edit_ins_class').is(':hidden')) {
                    $('#editInsClassField').val(null);
                }
                if ($('#edit_leaveType').is(':hidden')) {
                    $('#editLeaveTypeField').val(null);
                }
            });
            $('#requests_table').on('click', '.delete-request', function() {
                var requestId = $(this).data('id');

                // Perform AJAX request to delete the request
                $.ajax({
                    url: `/delete-request/${requestId}`,
                    type: 'DELETE',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            // Reload the datatable
                            $('#requests_table').DataTable().ajax.reload();
                            // Reload the page
                            //  window.location.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while deleting the request.');
                    }
                });

            });
        });
    </script>
@endsection
