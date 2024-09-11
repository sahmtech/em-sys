@extends('layouts.app')

@section('title', __('essentials::lang.view_worker'))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang('essentials::lang.view_worker')</h3>
            </div>
            <div class="col-md-4 col-xs-12 mt-15 pull-right">
                {!! Form::select('user_id', $users, $user->id, ['class' => 'form-control select2', 'id' => 'user_id']) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">

                <div class="box box-primary">
                    <div class="box-body box-profile">
                        @php
                            if (isset($user->media->display_url)) {
                                $img_src = $user->media->display_url;
                            } else {
                                $img_src = '/uploads/' . $user->profile_image;
                            }
                        @endphp

                        <img class="profile-user-img img-responsive img-circle" src="{{ $img_src }}"
                            alt="User profile picture">

                        <h3 class="profile-username text-center">
                            {{ $user->first_name . ' ' . $user->mid_name . ' ' . $user->last_name }}
                        </h3>

                        <p class="text-muted text-center" title="@lang('user.role')">
                            {{ $user->role_name }}
                        </p>

                        <ul class="list-group list-group-unbordered">
                            {{-- <li class="list-group-item">
                                <b>@lang( 'business.username' )</b>
                                <a class="pull-right">{{$user->username}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>@lang( 'business.email' )</b>
                                <a class="pull-right">{{$user->email}}</a>
                            </li> --}}
                            <li class="list-group-item">
                                <b>{{ __('lang_v1.status_for_user') }}</b>
                                @if ($user->status == 'active')
                                    <span class="label label-success pull-right" style="padding: 6px">
                                        @lang('business.is_active')
                                    </span>
                                @else
                                    <span class="label label-danger pull-right" style="padding: 6px">
                                        @lang('lang_v1.inactive')
                                    </span>
                                @endif
                            </li>
                            @if ($user->status != 'active')
                                <li class="list-group-item">
                                    <b>{{ __('lang_v1.status') }}</b>

                                    <span class="label label-success pull-right" style="padding: 6px">
                                        {{ __('essentials::lang.' . $user->sub_status) }}

                                    </span>

                                </li>
                            @endif
                            <li class="list-group-item">
                                <b>{{ __('followup::lang.is_booking') }}</b>
                                @if ($user->booking)
                                    <span class="label label-danger pull-right" style="padding: 6px">

                                        @lang('followup::lang.booking')
                                    </span>
                                @else
                                    <span class="label label-success pull-right" style="padding: 6px">
                                        @lang('followup::lang.not_booking')
                                    </span>
                                @endif
                            </li>
                        </ul>


                    </div>
                    <!-- /.box-body -->
                </div>

                <div class="box box-primary">

                    <div class="box-body box-profile">
                        <h3>@lang('essentials::lang.is_profile_complete')</h3>

                        <div>

                            <label>
                                <input type="checkbox" name="contracts"
                                    {{ $user->profile_image ? 'checked' : '' }}>@lang('essentials::lang.profile_picture')
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="contracts" {{ $Contract ? 'checked' : '' }}> @lang('essentials::lang.contracts')
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="admissions_to_work" {{ $admissions_to_work ? 'checked' : '' }}>
                                @lang('essentials::lang.admissions_to_work')
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="qualifications"
                                    {{ $Qualification ? 'checked' : '' }}>@lang('essentials::lang.qualifications')
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="health_insurance"> @lang('essentials::lang.health_insurance')
                            </label>
                        </div>


                    </div>
                    <!-- /.box-body -->
                </div>
                <div class="box box-primary" id="attachments-box">
                    <div class="box-body box-profile">
                        <h3>@lang('followup::lang.attachments')</h3>

                        @if (!empty($documents))
                            <div class="checkbox-group">
                                @foreach ($documents as $document)
                                    @if (isset($document->file_path) || isset($document->attachment))
                                        <div class="checkbox">
                                            <label>
                                                @if ($document->file_path || $document->attachment)
                                                    <a href="/uploads/{{ $document->file_path ?? $document->attachment }}"
                                                        data-file-url="{{ $document->file_path ?? $document->attachment }}">
                                                        {{ trans('followup::lang.' . $document->type) }}
                                                    </a>
                                                @else
                                                    {{ trans('followup::lang.' . $document->type) }}
                                                @endif
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p> {{ trans('followup::lang.no_attachment_to_show') }}</p>
                        @endif
                    </div>
                </div>


                <div class="box box-primary" id="attachments-box">
                    <div class="box-body box-profile">
                        <h3>@lang('followup::lang.document_delivery')</h3>

                        @if (!empty($document_delivery))
                            <div class="checkbox-group">
                                @foreach ($document_delivery as $document)
                                    <div class="checkbox">
                                        <label>

                                            <a href="/uploads/{{ $document->file_path }}"
                                                data-file-url="{{ $document->file_path }}">
                                                {{ $document->document->name_ar . ' - ' . $document->document->name_en }}
                                            </a>

                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p> {{ trans('followup::lang.no_document_delivery_to_show') }}</p>
                        @endif
                    </div>
                </div>

            </div>


            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs nav-justified">
                        <li class="active">
                            <a href="#user_info_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-user"
                                    aria-hidden="true"></i> @lang('essentials::lang.employee_info')</a>
                        </li>
                        <li>
                            <a href="#payrolls_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-money-check" aria-hidden="true"></i>

                                @lang('followup::lang.salaries')</a>
                        </li>

                        <li>
                            <a href="#timesheet_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-clock" aria-hidden="true"></i>


                                @lang('followup::lang.timesheet')</a>
                        </li>

                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-pen-square"
                                    aria-hidden="true"></i> @lang('lang_v1.activities')</a>
                        </li>
                    </ul>



                    <div class="tab-content">
                        <div class="tab-pane active" id="user_info_tab">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="col-md-6">
                                        @php
                                            $selected_contacts = '';
                                        @endphp
                                        @if (count($user->contactAccess))
                                            @php
                                                $selected_contacts_array = [];
                                            @endphp
                                            @foreach ($user->contactAccess as $contact)
                                                @php
                                                    $selected_contacts_array[] = $contact->name;
                                                @endphp
                                            @endforeach
                                            @php
                                                $selected_contacts = implode(', ', $selected_contacts_array);
                                            @endphp
                                        @else
                                            @php
                                                $selected_contacts = __('lang_v1.all');
                                            @endphp
                                        @endif
                                        <p>
                                            <strong>@lang('lang_v1.allowed_contacts'): </strong>
                                            {{ $selected_contacts }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @include('user.show_details')


                        </div>
                        <div class="tab-pane" id="payrolls_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('essentials::lang.company')</th>
                                                    <th>@lang('essentials::lang.project')</th>
                                                    <th>@lang('essentials::lang.date')</th>
                                                    <th>@lang('essentials::lang.the_total')</th>
                                                    <th>@lang('essentials::lang.status')</th>
                                                    <th>@lang('messages.view')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($payrolls as $payroll)
                                                    <tr>
                                                        <td>{{ $payroll['company'] }}</td>
                                                        <td>{{ $payroll['project_name'] }}</td>
                                                        <td>{{ $payroll['payrollGroup']['payroll_date'] }}</td>
                                                        <td>{{ $payroll['final_salary'] }}</td>
                                                        @if ($payroll['status'] == 'paid')
                                                            <td><a class="btn btn-xs  btn-success"> @lang('lang_v1.paid') </a>
                                                            </td>
                                                        @else
                                                            <td><a class="btn btn-xs  btn-warning"> @lang('lang_v1.yet_to_be_paind') </a>
                                                            </td>
                                                        @endif
                                                        <td><a href="#"
                                                                data-href="{{ route('show_payroll_details', ['id' => $payroll['id']]) }}"
                                                                data-container=".view_modal"
                                                                class="btn-modal btn btn-xs  btn-info"><i class="fa fa-eye"
                                                                    aria-hidden="true"></i>
                                                                @lang('messages.view') </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="timesheet_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('worker.sponser')</th>
                                                    <th>@lang('worker.project')</th>
                                                    <th>@lang('essentials::lang.date')</th>
                                                    <th>@lang('worker.wd')</th>
                                                    <th>@lang('worker.basic')</th>
                                                    <th>@lang('worker.monthly_cost')</th>


                                                    <th>@lang('worker.housing')</th>
                                                    <th>@lang('worker.transport')</th>
                                                    <th>@lang('worker.other_allowances')</th>
                                                    <th>@lang('worker.total_salary')</th>


                                                    <th>@lang('worker.absence_day')</th>
                                                    <th>@lang('worker.absence_amount')</th>
                                                    <th>@lang('worker.other_deduction')</th>
                                                    <th>@lang('worker.over_time_h')</th>
                                                    <th>@lang('worker.over_time')</th>

                                                    <th>@lang('worker.other_addition')</th>




                                                    <th>@lang('worker.deductions')</th>
                                                    <th>@lang('worker.additions')</th>
                                                    <th>@lang('worker.final_salary')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($timesheets as $timesheet)
                                                    <tr>

                                                        <td>{{ $timesheet['sponser'] }}</td>
                                                        <td>{{ $timesheet['project'] }}</td>
                                                        <td>{{ $timesheet['timesheet_date'] }}</td>
                                                        <td>{{ $timesheet['wd'] }}</td>
                                                        <td>{{ number_format($timesheet['basic'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['monthly_cost'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['housing'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['transport'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['other_allowances'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['total_salary'], 2) }}</td>
                                                        <td>{{ $timesheet['absence_day'] }}</td>
                                                        <td>{{ number_format($timesheet['absence_amount'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['other_deduction'], 2) }}</td>
                                                        <td>{{ $timesheet['over_time_h'] }}</td>
                                                        <td>{{ number_format($timesheet['over_time'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['other_addition'], 2) }}</td>


                                                        <td>{{ number_format($timesheet['deductions'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['additions'], 2) }}</td>
                                                        <td>{{ number_format($timesheet['final_salary'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="addDocModal" tabindex="-1" role="dialog"
                            aria-labelledby="gridSystemModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">

                                    {!! Form::open(['route' => 'storeOfficialDoc', 'enctype' => 'multipart/form-data']) !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">@lang('essentials::lang.add_Doc')</h4>
                                    </div>

                                    <div class="modal-body">

                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                                {!! Form::select('employee', $users, null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('essentials::lang.select_employee'),
                                                    'required',
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                                                {!! Form::select(
                                                    'doc_type',
                                                    [
                                                        'national_id' => __('essentials::lang.national_id'),
                                                        'passport' => __('essentials::lang.passport'),
                                                        'residence_permit' => __('essentials::lang.residence_permit'),
                                                        'drivers_license' => __('essentials::lang.drivers_license'),
                                                        'car_registration' => __('essentials::lang.car_registration'),
                                                        'international_certificate' => __('essentials::lang.international_certificate'),
                                                    ],
                                                    null,
                                                    ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_type'), 'required'],
                                                ) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':*') !!}
                                                {!! Form::number('doc_number', null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:40px',
                                                    'placeholder' => __('essentials::lang.doc_number'),
                                                    'required',
                                                ]) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':*') !!}
                                                {!! Form::date('issue_date', null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:40px',
                                                    'placeholder' => __('essentials::lang.issue_date'),
                                                    'required',
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':*') !!}
                                                {!! Form::text('issue_place', null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:40px',
                                                    'placeholder' => __('essentials::lang.issue_place'),
                                                    'required',
                                                ]) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                                {!! Form::select(
                                                    'status',
                                                    [
                                                        'valid' => __('essentials::lang.valid'),
                                                        'expired' => __('essentials::lang.expired'),
                                                    ],
                                                    null,
                                                    [
                                                        'class' => 'form-control',
                                                        'style' => 'height:40px',
                                                        'placeholder' => __('essentials::lang.select_status'),
                                                        'required',
                                                    ],
                                                ) !!}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                                                {!! Form::date('expiration_date', null, [
                                                    'class' => 'form-control',
                                                    'style' => 'height:40px',
                                                    'placeholder' => __('essentials::lang.expiration_date'),
                                                    'required',
                                                ]) !!}
                                            </div>

                                            <div class="form-group col-md-6">
                                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                                                {!! Form::file('file', null, [
                                                    'class' => 'form-control',
                                                    'placeholder' => __('essentials::lang.file'),
                                                    'required',
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                                        <button type="button" class="btn btn-default"
                                            data-dismiss="modal">@lang('messages.close')</button>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>



                        <div class="tab-pane" id="activities_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('activity_log.activities')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
    <!-- document & note.js -->


    <script type="text/javascript">
        $(document).ready(function() {
            $('#user_id').change(function() {
                if ($(this).val()) {
                    window.location = "{{ url('/users') }}/" + $(this).val();
                }
            });
        });
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            $('input[type="checkbox"]').prop('disabled', true);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.file-link').on('click', function(e) {
                e.preventDefault();
                var fileUrl = '/uploads/' + $(this).data('file-url');
                openFile(fileUrl);
            });

            function openFile(fileUrl) {
                window.open(fileUrl, '_blank');
            }
        });
    </script>

@endsection
