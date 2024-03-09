@extends('layouts.app')
@section('title', __('followup::lang.workers_details'))
@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.workers_details')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.create_worker'))
                @slot('tool')
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="box-tools">
                                <a class="btn btn-block btn-primary" href="{{ route('createWorker', ['id' => $id]) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                            </div>
                        </div>
                    </div>
                @endslot
            @endif

            <div class="row">


                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tr class="bg-green">
                            <th>{{ __('followup::lang.name') }}</th>
                            <th>{{ __('followup::lang.sponsor') }}</th>
                            <th>{{ __('followup::lang.nationality') }}</th>
                            <th>{{ __('followup::lang.eqama') }}</th>
                            <th>{{ __('followup::lang.eqama_end_date') }}</th>
                            <th>{{ __('followup::lang.work_card') }}</th>
                            <th>{{ __('followup::lang.insurance') }}</th>
                            <th>{{ __('followup::lang.contract_end_date') }}</th>
                            <th>{{ __('followup::lang.passport') }}</th>
                            <th>{{ __('followup::lang.passport_end_date') }}</th>
                            <th>{{ __('followup::lang.gender') }}</th>
                            <th>{{ __('followup::lang.salary') }}</th>
                            <th>{{ __('followup::lang.profession') }}</th>
                            <th>{{ __('followup::lang.action') }}</th>

                        </tr>

                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->first_name }} {{ $user->last_name }}</td>

                                <td>{{ optional(optional($user->appointment)->location)->name }}</td>

                                <td>{{ optional($user->country)->nationality ?? ' ' }}</td>
                                <td>{{ $user->id_proof_number }}</td>

                                <td>
                                    @foreach ($user->OfficialDocument->reverse() as $off)
                                        @if ($off->type == 'residence_permit')
                                            {{ $off->expiration_date }}
                                        @break
                                    @endif
                                @endforeach
                            </td>

                            <td>{{ optional($user->workCard)->id ?? ' ' }}</td>
                            <td>
                                @if ($user->has_insurance === null)
                                    {{ ' ' }}
                                @elseif ($user->has_insurance == 0)
                                    {{ __('followup::lang.not_have_insurance') }}
                                @elseif ($user->has_insurance == 1)
                                    {{ __('followup::lang.has_insurance') }}
                                @endif
                            </td>
                            <td>{{ optional($user->contract)->contract_end_date ?? ' ' }}</td>
                            <td>
                                @foreach ($user->OfficialDocument->reverse() as $off)
                                    @if ($off->type == 'passport')
                                        {{ $off->number }}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach ($user->OfficialDocument->reverse() as $off)
                                @if ($off->type == 'passport')
                                    {{ $off->expiration_date }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td>
                        @if ($user->gender == 'male')
                            {{ __('followup::lang.male') }}
                        @elseif ($user->gender == 'female')
                            {{ __('followup::lang.female') }}
                        @else
                        @endif
                    </td>
                    <td>
                        @if ($user->essentials_salary)
                            {{ __('followup::lang.basic_salary') }}: {{ floor($user->essentials_salary) }}
                        @endif
                        <br>
                        @if ($user->allowancesAndDeductions->isNotEmpty())
                            {{ __('followup::lang.allowances') }}:
                            <ul>
                                @foreach ($user->userAllowancesAndDeductions as $allowanceOrDeduction)
                                    <li>{{ $allowanceOrDeduction->allowancedescription->description ?? '' }}:
                                        {{ floor($allowanceOrDeduction->amount) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>{{ optional(optional($user->appointment)->profession)->name }}</td>
                    <td>
                        @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.view_worker_details'))
                            <a href="{{ route('showWorker', ['id' => $user->id]) }}"
                                class="btn btn-primary">@lang('followup::lang.view_worker_details')</a>
                        @endif


                        @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('followup.add_request'))
                            <button type="button" class="btn btn-block btn-warning  btn-modal" data-toggle="modal"
                                data-target="#addRequestModal">
                                @lang('request.create_order')
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

    </div>
    <div class="modal fade" id="addRequestModal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeRequest', 'enctype' => 'multipart/form-data']) !!}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('request.create_order')</h4>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <input type="hidden" name="user_id[]" value="{{ $user->id }}">
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
</div>
@endcomponent



@endsection
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {


        var mainReasonSelect = $('#mainReasonSelect');
        var subReasonContainer = $('#sub_reason_container');
        var subReasonSelect = $('#subReasonSelect');


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
                    if (selectedType === 'chamberRequest' || selectedType === 'mofaRequest') {
                        $('#visa_number').show();


                    } else {
                        $('#visa_number').hide();

                    }
                    if (selectedType === 'atmCard') {
                        $('#atmType').show();


                    } else {
                        $('#atmType').hide();

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
