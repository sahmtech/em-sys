@extends('layouts.app')

@section('title', __('followup::lang.view_worker'))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang('followup::lang.view_worker')</h3>
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
                            {{ $user->user_full_name }}
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
                                    <span class="label label-success pull-right">
                                        @lang('business.is_active')
                                    </span>
                                @else
                                    <span class="label label-danger pull-right">
                                        @lang('lang_v1.inactive')
                                    </span>
                                @endif
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('followup::lang.is_booking') }}</b>
                                @if ($user->booking)
                                    <span class="label label-danger pull-right">

                                        @lang('followup::lang.booking')
                                    </span>
                                @else
                                    <span class="label label-success pull-right">
                                        @lang('followup::lang.not_booking')
                                    </span>
                                @endif
                            </li>
                        </ul>
                        <a href="{{ action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'edit'], [$user->id]) }}"
                            class="btn btn-primary btn-block">
                            <i class="glyphicon glyphicon-edit"></i>
                            @lang('messages.edit')
                        </a>

                    </div>
                    <!-- /.box-body -->
                </div>

                <div class="box box-primary">
                    <div class="box-body box-profile" style=" pointer-events: none; opacity: 0.5;">
                        <h3>@lang('essentials::lang.is_profile_complete')</h3>

                        <div>

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

                        @if (!empty($documents))
                            <div class="checkbox-group">
                                @foreach ($deliveryDocument as $document)
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
                                @endforeach
                            </div>
                        @else
                            <p> {{ trans('followup::lang.no_attachment_to_show') }}</p>
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
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-money-check" aria-hidden="true"></i>

                                @lang('followup::lang.salaries')</a>
                        </li>

                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true">
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

                                </div>
                            </div>

                            @include('user.show_details')


                        </div>

                        <div class="modal fade" id="addDocModal" tabindex="-1" role="dialog"
                            aria-labelledby="gridSystemModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">

                                    {!! Form::open(['route' => 'storeOfficialDoc', 'enctype' => 'multipart/form-data']) !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                aria-hidden="true">&times;</span></button>
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
