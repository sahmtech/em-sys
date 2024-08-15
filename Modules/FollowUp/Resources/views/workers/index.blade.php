@extends('layouts.app')
@section('title', __('followup::lang.workers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.workers')</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts_fillter, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('nationality_filter', __('followup::lang.nationality') . ':') !!}
                            {!! Form::select('nationality_filter', $nationalities, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('status_label', __('followup::lang.status') . ':') !!}
                            <select class="form-control" name="status_fillter" id='status_fillter' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($status_filltetr as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('essentials::lang.contract_end_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                    @php
                        $default_fields = [
                            $fields[0],
                            $fields[1],
                            $fields[2],
                            $fields[3],
                            $fields[4],
                            $fields[5],
                            $fields[6],
                            $fields[7],
                            $fields[8],
                            $fields[9],
                            $fields[10],
                            $fields[11],
                            $fields[12],
                            $fields[13],
                            $fields[14],
                            $fields[15],
                            $fields[16],
                            $fields[17],
                            $fields[18],
                            $fields[19],
                            $fields[20],
                            $fields[21],
                        ];

                        $default = array_keys($default_fields);

                    @endphp

                    <div style="row">
                        <div class="col-md-11">
                            <div class="form-group">
                                {!! Form::label('choose_fields', __('followup::lang.choose_fields') . ' ') !!}
                                {!! Form::select('choose_fields_select[]', $fields, $default, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'id' => 'choose_fields_select',
                                ]) !!}
                            </div>

                        </div>

                        <div class="col-md-1 ">
                            <button class="btn btn-primary pull-right btn-flat" onclick="chooseFields();"
                                style="margin-top: 24px;
                        width: 62px;
                        height: 40px;
                        border-radius: 4px;">تطبيق</button>
                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-12" style="margin-bottom: 5px;">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#askForWorkerModal">
                    @lang('essentials::lang.ask_for_worker')
                </button>
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table">
                    <thead>
                        <tr>
                            {{-- <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>

                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('followup::lang.contract_end_date')</th> --}}
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>

                            <td>#</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.profile_image')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.employee_number')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.eqama')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.company_name')</td>


                            <td class="table-td-width-100px">@lang('followup::lang.passport_numer')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.passport_expire_date')</td>


                            <td class="table-td-width-100px">@lang('essentials::lang.border_number')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.dob')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.insurance')</td>

                            <td class="table-td-width-100px">@lang('followup::lang.project_name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.nationality')</td>



                            <td class="table-td-width-100px">@lang('followup::lang.eqama_end_date')</td>

                            <td class="table-td-width-100px">@lang('followup::lang.admissions_date')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.admissions_type')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.admissions_status')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.contract_end_date')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.mobile_number')</td>
                            <td class="table-td-width-100px">@lang('business.email')</td>

                            <td class="table-td-width-100px">@lang('followup::lang.profession')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.status')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.Basic_salary')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.total_salary')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.gender')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.marital_status')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.blood_group')</td>

                            <td class="table-td-width-100px">@lang('followup::lang.bank_code')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.travel_categorie')</td>





                        </tr>
                    </thead>
                </table>
                <div style="margin-bottom: 10px;">

                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.cancle_worker_project'))
                        <button type="button" class="btn btn-warning btn-sm custom-btn" id="cancle-project-selected">
                            @lang('followup::lang.cancle_worker_project')
                        </button>
                    @endif

                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.add_request'))
                        <button type="button" class="btn btn-primary btn-sm custom-btn" id="add-request-selected">
                            @lang('request.create_order')
                        </button>
                    @endif
                </div>

            </div>
            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'cancleProject']),
                            'method' => 'post',
                            'id' => 'cancle_project_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('followup::lang.cancle_worker_project')</h4>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                            <div class="form-group col-md-6">
                                {!! Form::label('canceled_date', __('followup::lang.canceled_date') . ':') !!}
                                {!! Form::date('canceled_date', null, [
                                    'class' => 'form-control',
                                    'style' => ' height: 40px',
                                    'placeholder' => __('followup::lang.canceled_date'),
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('notes', __('followup::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('followup::lang.note'),
                                    'rows' => 2,
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="submitsBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
            <div class="modal fade" id="addRequestModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'storeSelectedRowsRequest', 'enctype' => 'multipart/form-data']) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('request.create_order')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="user_id" id="selectedRowsData2" />

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
                                    {!! Form::label('start_date', __('request.start_date') . ':*') !!}
                                    {!! Form::date('start_date', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.start_date'),
                                        'id' => 'startDateField',
                                    ]) !!}
                                </div>


                                <div class="form-group col-md-6" id="end_date" style="display: none;">
                                    {!! Form::label('end_date', __('request.end_date') . ':*') !!}
                                    {!! Form::date('end_date', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.end_date'),
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
                                    {!! Form::label('escape_date', __('request.escape_date') . ':*') !!}
                                    {!! Form::date('escape_date', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('request.escape_date'),
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
                                            'placeholder' => __('request.select_type'),
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
                                            'placeholder' => __('request.select_type'),
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
                                            'placeholder' => __('request.select_type'),
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
        @endcomponent
        <div class="modal fade" id="askForWorkerModal" tabindex="-1" role="dialog"
            aria-labelledby="askForWorkerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="askForWorkerModalLabel">@lang('essentials::lang.ask_for_worker')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('essentials::lang.close')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="askForWorkerForm">
                            @csrf
                            <div class="form-group">
                                <label for="worker_identifier">@lang('essentials::lang.worker_identifier')</label>
                                <input type="text" class="form-control" id="worker_identifier"
                                    name="worker_identifier" required>
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('essentials::lang.submit')</button>
                        </form>

                        <!-- Worker info section -->
                        <div id="worker-info" style="display:none; margin-top: 20px;">
                            <h5>@lang('essentials::lang.worker_information')</h5>
                            <p><strong>@lang('essentials::lang.full_name'):</strong> <span id="worker_full_name"></span></p>
                            <p><strong>@lang('essentials::lang.emp_number'):</strong> <span id="worker_emp_number"></span></p>
                            <p><strong>@lang('essentials::lang.id_proof_number'):</strong> <span id="worker_id_proof_number"></span></p>
                            <p><strong>@lang('essentials::lang.residence_permit_expiration'):</strong> <span id="worker_residence_permit_expiration"></span>
                            </p>
                            <p><strong>@lang('essentials::lang.passport_number'):</strong> <span id="worker_passport_number"></span></p>
                            <p><strong>@lang('essentials::lang.passport_expire_date'):</strong> <span id="worker_passport_expire_date"></span></p>
                            <p><strong>@lang('essentials::lang.border_number'):</strong> <span id="worker_border_no"></span></p>
                            <p><strong>@lang('essentials::lang.company_name'):</strong> <span id="worker_company_name"></span></p>
                            <p><strong>@lang('essentials::lang.assigned_to'):</strong> <span id="worker_assigned_to"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            $('#status_fillter').select2();

            var workers_table = $('#workers_table').DataTable({

                processing: true,
                serverSide: true,


                ajax: {

                    url: "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index']) }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#nationality_filter').val()) {
                            d.nationality = $('#nationality_filter').val();
                        }
                        if ($('#status_fillter').val()) {
                            d.status_fillter = $('#status_fillter').val();
                        }
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },

                columns: [


                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id +
                                '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
                    {
                        "data": "worker_id"
                    },
                    {
                        "data": "profile_image",
                        "render": function(data, type, row) {
                            if (data) {

                                var imageUrl = '/uploads/' + data;
                                return '<img src="' + imageUrl +
                                    '" alt="Profile Image" class="img-thumbnail" width="50" height="50" style=" border-radius: 50%;">';
                            } else {
                                return '@lang('essentials::lang.no_image')';
                            }
                        }
                    },
                    {
                        "data": "emp_number"
                    },


                    {
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' + '{{ route('showWorker', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },

                    {
                        data: 'id_proof_number'
                    },
                    {
                        "data": "company_name"
                    },
                    {
                        data: 'passport_number'
                    },
                    {
                        data: 'passport_expire_date'
                    },
                    {
                        data: 'border_no'
                    }, {
                        data: 'dob'
                    },
                    {
                        data: 'insurance'
                    },
                    {
                        data: 'contact_name'
                    },
                    {
                        data: 'nationality'
                    },


                    {
                        data: 'residence_permit_expiration'
                    },
                    {
                        data: 'admissions_date'
                    },
                    {
                        data: 'admissions_type',
                        render: function(data, type, row) {

                            if (data === 'first_time') {
                                return '@lang('essentials::lang.first_time')';
                            } else if (data === 'after_vac') {
                                return '@lang('essentials::lang.after_vac')';
                            } else {
                                return '@lang('essentials::lang.no_addmission_yet')';
                            }
                        }
                    },
                    {
                        data: 'admissions_status',
                        render: function(data, type, row) {
                            if (data === 'on_date') {
                                return '@lang('essentials::lang.on_date')';
                            } else if (data === 'delay') {
                                return '@lang('essentials::lang.delay')';
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'contract_end_date'
                    },
                    {
                        data: "contact_number"
                    },
                    {
                        data: "email"
                    },

                    {
                        data: "profession",
                        name: 'profession'
                    },

                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'active') {
                                return '@lang('essentials::lang.active')';
                            } else if (data === 'vecation') {
                                return '@lang('essentials::lang.vecation')';
                            } else if (data === 'inactive') {
                                return '@lang('essentials::lang.inactive')';
                            } else if (data === 'terminated') {
                                return '@lang('essentials::lang.terminated')';
                            } else {
                                return ' ';
                            }
                        }
                    },
                    {

                        data: 'essentials_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }

                    },
                    {
                        data: 'total_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }
                    },

                    {
                        data: 'gender',
                        render: function(data, type, row) {
                            if (data === 'male') {
                                return '@lang('lang_v1.male')';
                            } else if (data === 'female') {
                                return '@lang('lang_v1.female')';

                            } else {
                                return '@lang('lang_v1.others')';
                            }
                        }
                    },
                    {
                        data: 'marital_status'
                    },
                    {
                        data: 'blood_group'
                    },
                    {
                        data: 'bank_code',

                    },
                    {
                        data: 'categorie_id',

                    },


                ]
            });

            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            $('#project_name_filter,#doc_filter_date_range,#nationality_filter,#status_fillter').on('change',
                function() {
                    workers_table.ajax.reload();
                });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#workers_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === workers_table.rows()
                    .count());
            });


            $('#cancle-project-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });

            $('#submitsBtn').click(function() {
                var formData = new FormData($('#cancle_project_form')[0]);

                $.ajax({
                    type: 'POST',
                    url: $('#cancle_project_form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success == true) {
                            toastr.success(result.msg);
                            workers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });

                $('#changeStatusModal').modal('hide');
            });

            $('#add-request-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                    };
                }).get();
                console.log(selectedRows);
                $('#selectedRowsData2').val(JSON.stringify(selectedRows));
                $('#addRequestModal').modal('show');
            });


        });


        chooseFields = function() {
            var selectedOptions = $('#choose_fields_select').val();

            var dt = $('#workers_table').DataTable();

            var fields = fields;

            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
    <script>
        $(document).ready(function() {
            $('#askForWorkerForm').on('submit', function(e) {
                e.preventDefault();

                var worker_identifier = $('#worker_identifier').val();

                $.ajax({
                    url: '{{ route('get-worker-info') }}', // Replace with your actual route
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        worker_identifier: worker_identifier
                    },
                    success: function(response) {
                        if (response.success) {
                            // Display the worker info in the modal
                            $('#worker_full_name').text(response.data.full_name);
                            $('#worker_emp_number').text(response.data.emp_number);
                            $('#worker_id_proof_number').text(response.data.id_proof_number);
                            $('#worker_residence_permit_expiration').text(response.data
                                .residence_permit_expiration);
                            $('#worker_passport_number').text(response.data.passport_number);
                            $('#worker_passport_expire_date').text(response.data
                                .passport_expire_date);
                            $('#worker_border_no').text(response.data.border_no);
                            $('#worker_company_name').text(response.data.company_name);
                            $('#worker_assigned_to').text(response.data.assigned_to);

                            $('#worker-info').show();
                        } else {
                            $('#worker-info').hide();
                            alert('@lang('essentials::lang.worker_not_found')');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#worker-info').hide();
                        alert('@lang('essentials::lang.error_occurred')');
                    }
                });
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {


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

            $('#addRequestModal').on('shown.bs.modal', function(e) {
                $('#requestType').select2({
                    dropdownParent: $(
                        '#addRequestModal'),
                    width: '100%',
                });
            });

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



                    },
                    error: function(xhr) {

                        console.log('Error:', xhr.responseText);
                    }
                });
            }







        });
    </script>
@endsection
