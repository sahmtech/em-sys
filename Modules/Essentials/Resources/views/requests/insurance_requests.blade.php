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

            .modal-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }

            .modal-title {
                font-weight: bold;
                color: #495057;
            }

            .modal-body {
                background-color: #ffffff;
                color: #495057;
            }

            .request-details,
            .activity {
                border: 1px solid #dee2e6;
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 4px;
            }

            .request-details strong,
            .activity strong {
                color: #007bff;
            }

            .modal-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }

            .modal-title {
                font-weight: bold;
                color: #495057;
            }

            .modal-body {
                background-color: #ffffff;
                color: #495057;
            }

            .card {
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
                border-radius: 4px;
                width: 90%;

            }

            .card-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                padding: 10px;
                font-weight: bold;
                color: #495057;
            }

            .card-body {
                padding: 10px;
            }

            .card-body p {
                margin: 5px 0;
            }

            .arrow-down {
                width: 0;
                height: 0;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;
                border-top: 10px solid #dee2e6;
                margin: 0 auto;
            }

            .modal-header .close {
                color: #007bff;
                opacity: 1;
            }

            .modal-header .close:hover,
            .modal-header .close:focus {
                color: #0056b3;
                text-decoration: none;
                opacity: 1;
            }

            .modal-footer .btn-secondary {
                background-color: #007bff;
                border-color: #007bff;
                color: #fff;
            }

            .modal-footer .btn-secondary:hover,
            .modal-footer .btn-secondary:focus {
                background-color: #0056b3;
                border-color: #0056b3;
                color: #fff;
            }

            .modal-header .close {
                color: #007bff;
                opacity: 1;
            }

            .modal-header .close:hover,
            .modal-header .close:focus {
                color: #0056b3;
                text-decoration: none;
                opacity: 1;
            }

            .modal-footer .btn-secondary {
                background-color: #007bff;
                border-color: #007bff;
                color: #fff;
            }

            .modal-footer .btn-secondary:hover,
            .modal-footer .btn-secondary:focus {
                background-color: #0056b3;
                border-color: #0056b3;
                color: #fff;
            }

            .card {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .card-header {
                background-color: #f7f7f7;
                border-bottom: 1px solid #dee2e6;
                font-weight: bold;
            }

            .card-body {
                padding: 1rem;
            }

            .card-footer {
                background-color: #f7f7f7;
                border-top: 1px solid #dee2e6;
                text-align: right;
            }

            .workflow-rectangle {
                min-width: 150px;
                height: 100px;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
                color: #fff;
                padding: 10px;
                text-align: center;
                margin-bottom: 10px;
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

            .workflow-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                white-space: nowrap;
                overflow-x: auto;
            }

            .workflow-rectangle.pending {
                background-color: orange;
            }

            .workflow-rectangle.approved {
                background-color: green;
            }

            .workflow-rectangle.rejected {
                background-color: red;
            }

            .workflow-rectangle.grey {
                background-color: grey;
            }

            .pending-arrow,
            .approved-arrow,
            .rejected-arrow,
            .grey-arrow {
                color: #000;
            }

            .department-name {
                margin-top: 5px;
                font-weight: bold;
            }

            .updated-by {
                font-size: 12px;
                margin-top: 5px;
            }

            .workflow-rectangle.green {
                background-color: #4CAF50;
            }

            .attachment-item {
                margin-bottom: 10px;
            }

            .attachment-link {
                color: #007bff;
                text-decoration: none;
            }

            .attachment-link:hover {
                text-decoration: underline;
            }

            #attachmentForm .attachment-group {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            #attachmentForm .form-control {
                width: 100%;
                max-width: 150px;
                margin-right: 10px;
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
        @component('components.filters', ['title' => __('request.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('request.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        collect($all_status)->mapWithKeys(fn($status) => [$status => trans("request.$status")]),
                        null,
                        [
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'status_filter',
                        ],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="type_filter">@lang('request.request_type'):</label>
                    {!! Form::select(
                        'type_filter',
                        collect($allRequestTypes)->mapWithKeys(fn($type) => [$type => trans("request.$type")]),
                        null,
                        [
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'type_filter',
                        ],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="company_filter">@lang('request.company'):</label>
                    {!! Form::select('company_filter', $companies, null, [
                        'class' => 'form-control select2',
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'company_filter',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="project_filter">@lang('request.project'):</label>
                    {!! Form::select('project_filter', $saleProjects, null, [
                        'class' => 'form-control select2',
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'project_filter',
                    ]) !!}
                </div>
            </div>
        @endcomponent

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
                <div style="margin-bottom: 10px;">
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.change_insurance_request_status'))
                        <button type="button" class="btn btn-warning change_status2">
                            @lang('request.change_status')
                        </button>
                    @endif
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.return_insurance_request'))
                        <button class="btn btn-danger btn-sm btn-return2">
                            {{ trans('request.return_the_request') }}
                        </button>
                    @endif



                </div>
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>@lang('request.company')</th>
                            <th>@lang('request.request_number')</th>
                            <th>@lang('request.request_owner')</th>
                            <th>@lang('request.eqama_number')</th>
                            <th>@lang('request.request_type')</th>
                            <th>@lang('request.request_date')</th>
                            <th>@lang('request.created_by')</th>
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
                                    collect($requestTypes)->mapWithKeys(function ($requestType) {
                                            return [
                                                $requestType['id'] =>
                                                    trans('request.' . $requestType['type']) . ' - ' . trans('request.' . $requestType['for']),
                                            ];
                                        })->toArray(),
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
                            <div class="form-group col-md-6" id="residenceRenewalDuration" style="display: none;">
                                {!! Form::label('residenceRenewalDuration', __('request.required_duration') . ':*') !!}
                                {!! Form::select(
                                    'residenceRenewalDuration',
                                    [
                                        '3 months' => __('request.3 months'),
                                        '6 months' => __('request.6 months'),
                                        '9 months' => __('request.9 months'),
                                        '12 months' => __('request.12 months'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.select_duration'),
                                        'id' => 'residenceRenewalDuration',
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
                            <div class="form-group col-md-6" id="project_name" style="display: none;">
                                {!! Form::label('project_name', __('request.project_name') . ':*') !!}
                                {!! Form::select('project_name', $saleProjects, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_project'),
                                    'id' => 'projectSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="interview_date" style="display: none;">
                                {!! Form::label('interview_date', __('request.interview_date') . ':*') !!}
                                {!! Form::date('interview_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.interview_date'),
                                    'id' => 'interview_dateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="interview_time" style="display: none;">
                                {!! Form::label('interview_time', __('request.interview_time') . ':*') !!}
                                {!! Form::time('interview_time', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.interview_time'),
                                    'id' => 'interview_timeField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="interview_place" style="display: none;">
                                {!! Form::label('interview_place', __('request.interview_place') . ':*') !!}
                                {!! Form::select(
                                    'interview_place',
                                    [
                                        'online' => __('request.online'),
                                        'housing' => __('request.housing_place'),
                                        'company' => __('request.company_place'),
                                        'customer' => __('request.customer_place'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.select_type'),
                                        'id' => 'interview_placeField',
                                    ],
                                ) !!}
                            </div>



                            <div class="form-group col-md-6" id="profession" style="display: none;">
                                {!! Form::label('profession', __('request.profession') . ':*') !!}
                                {!! Form::select('profession', $specializations, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_profession'),
                                    'id' => 'professionSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="job_title" style="display: none;">
                                {!! Form::label('job_title', __('request.job_title') . ':*') !!}
                                {!! Form::select('job_title', $job_titles, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_job_title'),
                                    'id' => 'job_titleSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="nationlity" style="display: none;">
                                {!! Form::label('nationlity', __('request.nationlity') . ':*') !!}
                                {!! Form::select('nationlity', $nationalities, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_nationlity'),
                                    'id' => 'nationlitySelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="number_of_salary_inquiry" style="display: none;">
                                {!! Form::label('number_of_salary_inquiry', __('request.number_of_salary_inquiry') . ':') !!}
                                {!! Form::text('number_of_salary_inquiry', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.number_of_salary_inquiry'),
                                    'id' => 'number_of_salary_inquiryField',
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
                            <input type="hidden" name="request_id" id="request_id">

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
                            <div class="form-group col-md-6" id="edit_residenceRenewalDuration" style="display: none;">
                                {!! Form::label('residenceRenewalDuration', __('request.required_duration') . ':*') !!}
                                {!! Form::select(
                                    'residenceRenewalDuration',
                                    [
                                        '3 months' => __('request.3 months'),
                                        '6 months' => __('request.6 months'),
                                        '9 months' => __('request.9 months'),
                                        '12 months' => __('request.12 months'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.select_duration'),
                                        'id' => 'editResidenceRenewalDurationField',
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
                            <div class="form-group col-md-6" id="edit_project_name" style="display: none;">
                                {!! Form::label('project_name', __('request.project_name') . ':*') !!}
                                {!! Form::select('project_name', $saleProjects, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_project'),
                                    'id' => 'editProjectSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_interview_date" style="display: none;">
                                {!! Form::label('interview_date', __('request.interview_date') . ':*') !!}
                                {!! Form::date('interview_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.interview_date'),
                                    'id' => 'edit_interview_dateField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_interview_time" style="display: none;">
                                {!! Form::label('interview_time', __('request.interview_time') . ':*') !!}
                                {!! Form::time('interview_time', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('request.interview_time'),
                                    'id' => 'edit_interview_timeField',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_interview_place" style="display: none;">
                                {!! Form::label('interview_place', __('request.interview_place') . ':*') !!}
                                {!! Form::select(
                                    'interview_place',
                                    [
                                        'online' => __('request.online'),
                                        'housing' => __('request.housing_place'),
                                        'company' => __('request.company_place'),
                                        'customer' => __('request.customer_place'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.select_type'),
                                        'id' => 'edit_interview_placeField',
                                    ],
                                ) !!}
                            </div>



                            <div class="form-group col-md-6" id="edit_profession" style="display: none;">
                                {!! Form::label('profession', __('request.profession') . ':*') !!}
                                {!! Form::select('profession', $specializations, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_profession'),
                                    'id' => 'editProfessionSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_job_title" style="display: none;">
                                {!! Form::label('job_title', __('request.job_title') . ':*') !!}
                                {!! Form::select('job_title', $job_titles, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_job_title'),
                                    'id' => 'edit_job_titleSelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_nationlity" style="display: none;">
                                {!! Form::label('nationlity', __('request.nationlity') . ':*') !!}
                                {!! Form::select('nationlity', $nationalities, null, [
                                    'class' => 'form-control',
                                    'style' => 'height: 40px',
                                    'placeholder' => __('request.select_nationlity'),
                                    'id' => 'editNationlitySelect',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6" id="edit_number_of_salary_inquiry" style="display: none;">
                                {!! Form::label('number_of_salary_inquiry', __('request.number_of_salary_inquiry') . ':') !!}
                                {!! Form::text('number_of_salary_inquiry', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('request.number_of_salary_inquiry'),
                                    'id' => 'edit_number_of_salary_inquiryField',
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

        {{-- view request details --}}

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
                            <div class="workflow-container" id="workflow-container"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>@lang('request.request_owner')</h4>
                                <ul id="worker-list"></ul>
                                <h4>@lang('request.attachments')</h4>
                                <ul id="attachments-list"></ul>
                            </div>
                            <div class="col-md-6">
                                <h4>@lang('request.request_info')</h4>
                                <ul id="request-info"></ul>
                            </div>
                        </div>
                        <form id="attachmentForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div id="attachmentContainer"></div>
                            <button type="button" class="btn btn-primary" id="addAttachment">@lang('request.add_attachment')</button>
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- view request activities --}}
        <div class="modal fade" id="activitiesModal" tabindex="-1" role="dialog"
            aria-labelledby="activitiesModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="activitiesModalLabel">@lang('request.activities')</h4>

                    </div>
                    <div class="modal-body">
                        <!-- Activities will be injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
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


            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('insurance_requests') }}",
                    data: function(d) {
                        d.status = $('#status_filter').val();
                        d.type = $('#type_filter').val();
                        d.company = $('#company_filter').val();
                        d.project = $('#project_filter').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {


                            if ((row.status_now === 'pending' && row.action_type !==
                                    'task') || (row.status_now === 'pending' && row
                                    .is_superior ===
                                    1)) {
                                return '<input type="checkbox" class="select-row" data-id="' + row
                                    .id + '" data-requestId="' + row.id + '">';

                            } else {
                                return '';
                            }
                        },
                        orderable: false,
                        searchable: false,
                    }, {
                        data: 'company_id'
                    }, {
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
                            // Custom render logic based on request type
                            const requestTypeMap = {
                                'exitRequest': '@lang('request.exitRequest')',
                                'returnRequest': '@lang('request.returnRequest')',
                                'escapeRequest': '@lang('request.escapeRequest')',
                                'advanceSalary': '@lang('request.advanceSalary')',
                                'leavesAndDepartures': '@lang('request.leavesAndDepartures')',
                                'atmCard': '@lang('request.atmCard')',
                                'residenceRenewal': '@lang('request.residenceRenewal')',
                                'residenceIssue': '@lang('request.residenceIssue')',
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
                                'salaryInquiryRequest': '@lang('request.salaryInquiryRequest')',
                                'interviewsRequest': '@lang('request.interviewsRequest')',
                                'moqimPrint': '@lang('request.moqimPrint')',
                                'salaryIntroLetter': '@lang('request.salaryIntroLetter')',
                                'QiwaContract': '@lang('request.QiwaContract')',
                                'ExitWithoutReturnReport': '@lang('request.ExitWithoutReturnReport')',

                            };

                            return requestTypeMap[data] || data;
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'created_user'
                    },
                    {
                        data: 'status'
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

                ]
            });
            $('#status_filter, #type_filter ,#company_filter,#project_filter').change(function() {
                requests_table.ajax.reload();
            });
            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#requests_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === requests_table.rows()
                    .count());
            });


            $(document).on('click', '.btn-view-activities', function() {
                var requestId = $(this).data('request-id');
                viewRequestActivities(requestId);
            });

            function viewRequestActivities(requestId) {
                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'.replace(
                            ':requestId', requestId),
                        method: 'GET',
                        success: function(response) {

                            $('#activitiesModal .modal-body').html(renderRequestActivities(response));
                            $('#activitiesModal').modal('show');
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            }

            function renderRequestActivities(data) {
                let activitiesHtml = `
                      <div class="activity-header card mb-3">
                      
                          <div class="card-body">
                              <p><strong>@lang('request.started_department'):</strong> ${data.request_info.started_depatment.name || '@lang('request.not_exist')'}</p>
                              <p><strong>@lang('request.created_by'):</strong> ${data.created_user_info.created_user_full_name || '@lang('request.not_exist')'}</p>
                          </div>
                      </div>
                  `;

                activitiesHtml += data.followup_processes.reverse().map((process, index) => `
                      <div class="activity card mb-3">
                          <div class="card-body">
                              <p><strong>@lang('request.department'):</strong> ${process.department.name || '@lang('request.not_exist')'}</p>
                              <p><strong>@lang('request.status'):</strong> ${process.status || '@lang('request.not_exist')'}</p>
                             <p><strong>@lang('request.updated_by'):</strong> ${process.updated_by || '@lang('request.not_exist')'}</p>
                                 <p><strong>@lang('request.updated_at'):</strong> ${process.status_changed_at || '@lang('request.not_exist')'}</p>
                          
                              <p><strong>@lang('request.status_note'):</strong> ${process.status_note || '@lang('request.not_exist')'}</p>
                          </div>
                          ${index < data.followup_processes.length - 1 ? '<div class="arrow-down"></div>' : ''}
                      </div>
                  `).join('');

                return activitiesHtml;
            }
        });

        $(document).ready(function() {


            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#requestType').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });
            });

            $(document).on('click', '.change_status2', function(e) {
                e.preventDefault();

                var selectedRows = [];
                $('.select-row:checked').each(function() {
                    selectedRows.push($(this).data('id'));
                });

                if (selectedRows.length === 0) {
                    toastr.error('Please select at least one request.');
                    return;
                }

                // Set the selected rows in a hidden input in the modal
                $('#change_status_modal').find('#request_ids').val(selectedRows.join(','));

                // Show the modal
                $('#change_status_modal').modal('show');
            });

            $(document).on('click', 'a.change_status', function(e) {
                e.preventDefault();

                $('#change_status_modal').find('select#status_dropdown').val($(this)
                        .data('orig-value'))
                    .change();
                $('#change_status_modal').find('#request_id').val($(this).data(
                    'request-id'));
                $('#change_status_modal').modal('show');


            });


            $(document).on('submit', 'form#change_status_form', function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                var ladda = Ladda.create(document.querySelector(
                    '.update-offer-status'));
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
                            window.location.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
            $(document).on('click', '.btn-return2', function(e) {
                e.preventDefault();

                var selectedRows = [];
                $('.select-row:checked').each(function() {
                    selectedRows.push($(this).data('id'));
                });

                if (selectedRows.length === 0) {
                    toastr.error('Please select at least one request.');
                    return;
                }

                // Set the selected request IDs in a hidden input field
                $('#returnModal').find('#request_id').val(selectedRows.join(','));

                // Show the modal
                $('#returnModal').modal('show');
            });
            $('#requests_table').on('click', '.btn-return', function() {
                var requestId = $(this).data('request-id');
                $('#returnModal').modal('show');
                $('#returnModal').data('id', requestId);
            });


            $('#returnModalForm').submit(function(e) {
                e.preventDefault();

                var requestId = $('#returnModal').data('id');
                var requestIds = $('#returnModal').find('#request_id').val();
                var reason = $('#reasonInput').val();

                $.ajax({
                    url: "{{ route('returnRequest') }}",
                    method: "POST",
                    data: {
                        requestId: requestId,
                        requestIds: requestIds,
                        reason: reason
                    },
                    success: function(result) {

                        if (result.success == true) {
                            $('#returnModal').modal('hide');
                            toastr.success(result.msg);
                            window.location.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            $(document).on('click', '.btn-view-request-details', function() {
                var requestId = $(this).data('request-id');

                if (requestId) {
                    $.ajax({
                        url: '{{ route('viewUserRequest', ['requestId' => ':requestId']) }}'
                            .replace(':requestId', requestId),
                        method: 'GET',
                        success: function(response) {
                            console.log(response);
                            var workflowContainer = $('#workflow-container');
                            var workerList = $('#worker-list');
                            var attachmentsList = $('#attachments-list');
                            var requestInfoList = $('#request-info');
                            workflowContainer.html('');
                            workerList.html('');
                            attachmentsList.html('');
                            requestInfoList.html('');

                            response.workflow.forEach(function(step, i) {
                                var status = step.status ? step.status.toLowerCase() :
                                    'grey';
                                if (step.process_id != null) {
                                    console.log(step.process_id);

                                    var updatedBy = response.followup_processes.find(
                                            process => process.id === step
                                            .process_id)?.updated_by ||
                                        '{{ __('request.not_exist') }}';
                                } else {
                                    var updatedBy = '{{ __('request.not_exist') }}';
                                }
                                var rectangle = `
                                          <div class="workflow-rectangle ${status}">
                                              <p class="department-name">${step.department}</p>
                                          <p class="updated-by">@lang('request.updated_by'): ${updatedBy}</p>
                                          </div>
                                      `;
                                workflowContainer.append(rectangle);

                                if (i < response.workflow.length - 1) {
                                    workflowContainer.append(
                                        `<i class="fas fa-arrow-left workflow-arrow ${status}-arrow"></i>`
                                    );
                                }
                            });

                            workerList.append(
                                `<p class="worker-info">{{ __('request.name') }}: ${response.user_info.worker_full_name}</p>`
                            );
                            workerList.append(
                                `<p class="worker-info">{{ __('request.nationality') }}: ${response.user_info.nationality}</p>`
                            );
                            if (response.user_info.company) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.company') }}: ${response.user_info.company}</p>`
                                );
                            }
                            if (response.user_info.assigned_to) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.project_name') }}: ${response.user_info.assigned_to}</p>`
                                );
                            }
                            if (response.user_info.id_proof_number) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.eqama_number') }}: ${response.user_info.id_proof_number}</p>`
                                );
                            }
                            if (response.user_info.contract_end_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.contract_end_date') }}: ${response.user_info.contract_end_date}</p>`
                                );
                            }
                            if (response.user_info.eqama_end_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.eqama_end_date') }}: ${response.user_info.eqama_end_date}</p>`
                                );
                            }
                            if (response.user_info.passport_number) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.passport_number') }}: ${response.user_info.passport_number}</p>`
                                );
                            }
                            if (response.user_info.admission_date) {
                                workerList.append(
                                    `<p class="worker-info">{{ __('request.admission_date') }}: ${response.user_info.admission_date}</p>`
                                );
                            }
                            response.attachments.forEach(function(attachment, j) {
                                attachmentsList.append(`
                                  <li class="attachment-item">
                                      <a href="{{ url('uploads') }}/${attachment.file_path}" target="_blank" class="attachment-link">
                                          ${attachment.name || '@lang('request.attach') ' + (j + 1)}
                                      </a>
                                  </li>
                              `);
                            });
                            // Populate request info list
                            var requestInfo = response.request_info;
                            var requestInfoData = [{
                                    label: '{{ __('request.type') }}',
                                    value: requestInfo.type
                                },
                                {
                                    label: '{{ __('request.request_no') }}',
                                    value: requestInfo.request_no
                                },
                                {
                                    label: '{{ __('request.exit_date') }}',
                                    value: requestInfo.start_date
                                },
                                {
                                    label: '{{ __('request.end_date') }}',
                                    value: requestInfo.end_date
                                },
                                {
                                    label: '{{ __('request.escape_time') }}',
                                    value: requestInfo.escape_time
                                },
                                {
                                    label: '{{ __('request.advSalaryAmount') }}',
                                    value: requestInfo.advSalaryAmount
                                },
                                {
                                    label: '{{ __('request.monthlyInstallment') }}',
                                    value: requestInfo.monthlyInstallment
                                },
                                {
                                    label: '{{ __('request.installmentsNumber') }}',
                                    value: requestInfo.installmentsNumber
                                },
                                {
                                    label: '{{ __('request.baladyCardType') }}',
                                    value: requestInfo.baladyCardType
                                },
                                {
                                    label: '{{ __('request.workInjuriesDate') }}',
                                    value: requestInfo.workInjuriesDate
                                },
                                {
                                    label: '{{ __('request.resCardEditType') }}',
                                    value: requestInfo.resCardEditType
                                },
                                {
                                    label: '{{ __('request.main_reason') }}',
                                    value: requestInfo.contract_main_reason_id
                                },
                                {
                                    label: '{{ __('request.sub_reason') }}',
                                    value: requestInfo.contract_sub_reason_id
                                },
                                {
                                    label: '{{ __('request.visa_number') }}',
                                    value: requestInfo.visa_number
                                },
                                {
                                    label: '{{ __('request.atmCardType') }}',
                                    value: requestInfo.atmCardType
                                },
                                {
                                    label: '{{ __('request.insurance_class') }}',
                                    value: requestInfo.insurance_classes_id
                                },
                                {
                                    label: '{{ __('request.status') }}',
                                    value: requestInfo.status
                                },

                                {
                                    label: '{{ __('request.started_depatment') }}',
                                    value: requestInfo.started_depatment.name
                                },
                                {
                                    label: '{{ __('request.created_at') }}',
                                    value: requestInfo.created_at
                                },
                                {
                                    label: '{{ __('request.updated_at') }}',
                                    value: requestInfo.updated_at
                                }
                            ];

                            requestInfoData.forEach(function(info) {
                                if (info.value !== null && info.value !==
                                    '') { // Check for null or empty values
                                    requestInfoList.append(
                                        `<li class="request-info-item">${info.label}: ${info.value}</li>`
                                    );
                                }
                            });
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
            $('#addAttachment').click(function() {
                var attachmentIndex = $('.attachment-group').length;
                var attachmentGroup = `
              <div class="attachment-group" style="margin-bottom: 10px;">
                  <input type="file" class="form-control attachment-input" name="attachments[${attachmentIndex}][file]" style="width: 150px; display: inline-block; margin-right: 10px;">
                  <input type="text" class="form-control attachment-name" name="attachments[${attachmentIndex}][name]" placeholder="@lang('request.attachment_name')" style="width: 150px; display: inline-block; margin-right: 10px;">
                  <button type="button" class="btn btn-danger remove-attachment">@lang('request.remove')</button>
              </div>
                   `;
                $('#attachmentContainer').append(attachmentGroup);
            });

            $(document).on('click', '.remove-attachment', function() {
                $(this).closest('.attachment-group').remove();
            });

            $('#attachmentForm').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);


                $('.attachment-group').each(function(index, element) {
                    var fileInput = $(element).find('input[type="file"]')[0];
                    var nameInput = $(element).find('input[type="text"]').val();
                    if (fileInput.files[0]) {
                        formData.append(`attachments[${index}][file]`, fileInput.files[0]);
                        formData.append(`attachments[${index}][name]`, nameInput);
                    }
                });

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            console.log(response);
                            toastr.success(response.msg);
                            $('#attachmentForm')[0].reset();
                            $('#attachmentContainer').html('');
                            $('#requestModal').modal('hide');
                            //  $('#requests_table').DataTable().ajax.reload();
                            window.location.reload();
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(response) {
                        var errorMessage = response.responseJSON ? response.responseJSON
                            .message : 'Error saving attachment.';
                        toastr.error(errorMessage);
                    }
                });
            });



        });
    </script>



    <script>
        $(document).ready(function() {
            var users = @json($users);
            var mainReasonSelect = $('#mainReasonSelect');
            var subReasonContainer = $('#sub_reason_container');
            var subReasonSelect = $('#subReasonSelect');
            $('#requestType').change(function() {
                var requestType = $(this).val();

                $.ajax({
                    url: '{{ route('fetch.users.by.type') }}',
                    type: 'GET',
                    data: {
                        type: requestType
                    },
                    success: function(response) {
                        $('#worker').empty();
                        $.each(response.users, function(key, value) {
                            $('#worker').append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                        $('#worker').trigger('change');
                    }
                });
            });

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

            function fetchWorkersNotAssignedy() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');


                $.ajax({
                    url: '/get-unsigned-workers',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        users: @json($users)
                    },
                    success: function(data) {
                        console.log(data.workers);
                        var userSelect = $('#worker');
                        userSelect.empty();

                        $.each(data.workers, function(key, value) {
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
                        if (selectedType === 'residenceRenewal' || selectedType === 'residenceIssue') {

                            $('#residenceRenewalDuration').show();


                        } else {
                            $('#residenceRenewalDuration').hide();

                        }
                        if (selectedType === 'exitRequest') {
                            fetchUsersWithSaudiNationality();

                        }

                        if (selectedType === 'passportRenewal') {
                            fetchUsersWithSaudiNationality();

                        }
                        if (selectedType === 'interviewsRequest') {
                            fetchWorkersNotAssignedy();
                            $('#project_name').show();
                            $('#interview_date').show();
                            $('#interview_time').show();
                            $('#interview_place').show();

                        } else {
                            $('#interview_date').hide();
                            $('#interview_time').hide();
                            $('#interview_place').hide();

                        }
                        if (selectedType === 'salaryInquiryRequest') {
                            $('#nationlity').show();
                            $('#profession').show();
                            $('#number_of_salary_inquiry').show();
                            $('#job_title').show();

                        } else {
                            $('#nationlity').hide();
                            $('#profession').hide();
                            $('#number_of_salary_inquiry').hide();
                            $('#job_title').hide();


                        }

                    },
                    error: function(xhr) {

                        console.log('Error:', xhr.responseText);
                    }
                });
            }

            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#worker').select2({
                    dropdownParent: $('#addRequestModal'),
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "User not found or you don't have access to them or their project.";
                        }
                    }
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
            'residenceIssue': '@lang('request.residenceIssue')',
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
            'interviewsRequest': '@lang('request.interviewsRequest')',
            'salaryInquiryRequest': '@lang('request.salaryInquiryRequest')',
            'moqimPrint': '@lang('request.moqimPrint')',
            'salaryIntroLetter': '@lang('request.salaryIntroLetter')',
            'QiwaContract': '@lang('request.QiwaContract')',
            'ExitWithoutReturnReport': '@lang('request.ExitWithoutReturnReport')',


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
            $('#editLeaveTypeField').val(request.essentials_leave_type_id) ?? null;
            $('#editEndDateField').val(request.end_date) ?? null;
            $('#editExitDateField').val(request.start_date) ?? null;
            $('#editReturnDateField').val(request.end_date) ?? null;
            $('#editEscapeTimeField').val(request.escape_time) ?? null;
            $('#editEscapeDateField').val(request.start_date) ?? null;

            $('#editWorkInjuriesDateField').val(request.workInjuriesDate) ?? null;
            $('#editResEditTypeField').val(request.resCardEditType) ?? null;
            $('#editAtmTypeField').val(request.atmCardType) ?? null;
            $('#editResidenceRenewalDurationField').val(request.editResidenceRenewalDurationField) ?? null;
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

            $('#editProjectSelect').val(request.sale_project_id) ?? null;
            $('#edit_interview_placeField').val(request.interview_place) ?? null;
            $('#edit_interview_dateField').val(request.interview_date) ?? null;
            $('#edit_interview_timeField').val(request.interview_time) ?? null;

            $('#editResidenceRenewalDurationField').val(request.residenceRenewalDuration) ?? null;

            $('#editProfessionSelect').val(request.specialization_id) ?? null;
            $('#edit_job_titleSelect').val(request.job_title_id) ?? null;
            $('#editNationlitySelect').val(request.nationality_id) ?? null;
            $('#edit_number_of_salary_inquiryField').val(request.number_of_salary_inquiry) ?? null;


            $('#editNote').val(request.note) ?? null;

            if (request.trip_type === 'round') {
                $('#edit_return_date_of_trip').show();
                $('#editReturnDateOfTripField').val(request.return_date);
            } else {
                $('#edit_return_date_of_trip').hide();
                $('#editReturnDateOfTripField').val('');
            }


            $('#editTripTypeField').off('change').on('change', function() {
                if ($(this).val() === 'round') {
                    $('#edit_return_date_of_trip').show();
                } else {
                    $('#edit_return_date_of_trip').hide();
                    $('#editReturnDateOfTripField').val('');
                }
            });
        }


        function handleEditTypeChange(requestType, request) {

            $('#edit_start_date, #edit_end_date, #edit_leaveType, #edit_workInjuriesDate, #edit_escape_time, #edit_escape_date, #edit_exit_date, #edit_return_date, #edit_installmentsNumber, #edit_monthlyInstallment, #edit_amount, #edit_commissioner_info, #edit_authorized_entity, #edit_trip_type, #edit_take_off_location, #edit_destination, #edit_weight_of_furniture, #edit_date_of_take_off, #edit_time_of_take_off, #edit_return_date_of_trip, #edit_resEditType, #edit_baladyType, #edit_ins_class, #edit_main_reason, #edit_visa_number, #edit_atmType,#edit_residenceRenewalDuration')
                .hide();

            switch (requestType) {
                case 'leavesAndDepartures':
                    $('#edit_start_date, #edit_end_date, #edit_leaveType').show();
                    break;
                case 'returnRequest':
                    $('#edit_exit_date, #edit_return_date').show();
                    break;
                case 'interviewsRequest':
                    $('#edit_project_name, #edit_interview_date, #edit_interview_time, #edit_interview_place').show();
                    break;
                case 'salaryInquiryRequest':
                    $('#edit_profession, #edit_job_title, #edit_nationlity, #edit_number_of_salary_inquiry').show();
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
                case 'residenceRenewal':
                    $('#edit_residenceRenewalDuration').show();
                    break;
                case 'residenceIssue':
                    $('#edit_residenceRenewalDuration').show();
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
                if ($('#edit_residenceRenewalDuration').is(':hidden')) {
                    $('#editResidenceRenewalDurationField').val(null);
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


                $.ajax({
                    url: `/delete-request/${requestId}`,
                    type: 'DELETE',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);

                            $('#requests_table').DataTable().ajax.reload();

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
