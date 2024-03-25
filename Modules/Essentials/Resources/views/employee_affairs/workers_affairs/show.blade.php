@extends('layouts.app')

@section('title', __('followup::lang.view_worker'))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang('followup::lang.view_worker')</h3>
            </div>
            {{-- <div class="col-md-4 col-xs-12 mt-15 pull-right">
                {!! Form::select('user_id', $users, $user->id, ['class' => 'form-control select2', 'id' => 'user_id']) !!}
            </div> --}}
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
                        <a id="profileImageLink" href="#">
                            <img class="profile-user-img img-responsive img-circle" src="{{ $img_src }}"
                                alt="User profile picture" id="profileImage">
                        </a>
                        <h3 class="profile-username text-center">
                            {{ $user->first_name . ' ' .$user->mid_name .' '. $user->last_name }}
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
                        <a href="{{ action([\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'edit'], [$user->id]) }}"
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
                                                       
                                                        @if ($document instanceof \Modules\Essentials\Entities\EssentialsOfficialDocument)
                                                            {{ trans('followup::lang.' .$document->type) }}
                                                        @elseif ($document instanceof \Modules\Essentials\Entities\EssentialsEmployeesContract)
                                                            {{ trans('followup::lang.contract_file') }}
                                                        @elseif ($document instanceof \Modules\Essentials\Entities\EssentialsEmployeesQualification)
                                                            {{ trans('essentials::lang.qualification_file') }}
                                                        @endif
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

                            @php
                                $custom_labels = json_decode(session('business.custom_labels'), true);
                            @endphp
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-12">

                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('lang_v1.admission_date'):</strong>
                                            @if (!empty($admissions_to_work->admissions_date))
                                                {{ @format_date($admissions_to_work->admissions_date) }}
                                            @endif
                                        </p>
                                        <p><strong>@lang('lang_v1.dob'):</strong>
                                            @if (!empty($user->dob))
                                                {{ @format_date($user->dob) }}
                                            @endif
                                        </p>
                                        <p><strong>@lang('lang_v1.nationality'):</strong>
                                            {{ !empty($nationality) ? json_decode($nationality, true)['nationality'] : '' }}
                                        </p>


                                        <p><strong>@lang('lang_v1.gender'):</strong>
                                            @if (!empty($user->gender))
                                                @lang('lang_v1.' . $user->gender)
                                            @endif
                                        </p>
                                        <p><strong>@lang('lang_v1.marital_status'):</strong>
                                            @if (!empty($user->marital_status))
                                                @lang('lang_v1.' . $user->marital_status)
                                            @endif
                                        </p>
                                        <p><strong>@lang('lang_v1.mobile_number'):</strong> {{ $user->contact_number ?? '' }}</p>
                                    </div>



                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('lang_v1.id_proof_name'):</strong>
                                            @if ($user->id_proof_name === null)
                                                {{ '' }}
                                            @elseif ($user->id_proof_name === 'eqama')
                                                @lang('essentials::lang.' . $user->id_proof_name)
                                            @elseif ($user->id_proof_name === 'border')
                                                @lang('essentials::lang.border_number')
                                            @elseif ($user->id_proof_name === 'national_id' || $user->id_proof_name === 'هوية وطنية')
                                                @lang('essentials::lang.' . $user->id_proof_name)
                                            @elseif ($user->id_proof_name === 'هوية وطنية')
                                                @lang($user->id_proof_name)
                                            @endif
                                        </p>
                                    </div>


                                    <div class="col-md-4">
                                        <p><strong>@lang('lang_v1.id_proof_number'):</strong>
                                            {{ $user->id_proof_number ?? ($user->border_no ?? '') }}</p>

                                    </div>

                                    <div class="clearfix"></div>
                                    <hr>

                                    <div class="col-md-4">
                                        <p><strong>@lang('essentials::lang.company'):</strong>
                                            {{ $user->company?->name ?? '' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <p><strong>@lang('followup::lang.project'):</strong>
                                            {{ $user->assignedTo?->name ?? '' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        @if ($user->assignedTo === null)
                                            <p><strong>@lang('essentials::lang.city'):</strong>
                                                {{ '' }}</p>
                                        @else
                                            <p><strong>@lang('essentials::lang.city'):</strong>
                                                {{ json_decode($user->assignedTo?->project_city?->name)->ar ?? '' }}</p>
                                        @endif

                                    </div>

                                    <div class="col-md-4">
                                        <p><strong>@lang('followup::lang.customer_name'):</strong>
                                            {{ $user->assignedTo?->contact?->supplier_business_name ?? '' }}</p>
                                    </div>
                                    @if ($user->booking)
                                        <div class="clearfix"></div>
                                        <hr>
                                        <div class="col-md-12">
                                            <h4>@lang('followup::lang.booking_details'):</h4>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>@lang('followup::lang.project'):</strong>
                                                {{ $user->booking->saleProject?->name ?? '' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>@lang('housingmovements::lang.booking_start_Date'):</strong>
                                                {{ $user->booking->booking_start_Date ?? '' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>@lang('housingmovements::lang.booking_end_Date'):</strong>
                                                {{ $user->booking->booking_end_Date ?? '' }}</p>
                                        </div>
                                    @endif

                                    <div class="clearfix"></div>
                                    <hr>




                                    <div class="col-md-12">
                                        <h4>@lang('lang_v1.bank_details'):</h4>
                                    </div>
                                    @php
                                        $bank_details = !empty($user->bank_details) ? json_decode($user->bank_details, true) : [];
                                    @endphp
                                    <div class="col-md-4">
                                        <p><strong>@lang('lang_v1.account_holder_name'):</strong>
                                            {{ $bank_details['account_holder_name'] ?? '' }}</p>
                                        <p><strong>@lang('lang_v1.account_number'):</strong> {{ $bank_details['account_number'] ?? '' }}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('lang_v1.bank_name'):</strong> {{ $bank_name ?? '' }}</p>
                                        <p><strong>@lang('lang_v1.bank_code'):</strong> {{ $bank_details['bank_code'] ?? '' }}</p>
                                    </div>
                                    <div class="col-md-4">

                                        <p><strong>@lang('lang_v1.branch'):</strong> {{ $bank_details['branch'] ?? '' }}</p>

                                    </div>

                                    @if (!empty($view_partials))
                                        @foreach ($view_partials as $partial)
                                            {!! $partial !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>



                        </div>


                        <div class="modal fade" id="imagePopupModal" tabindex="-1" role="dialog"
                            aria-labelledby="gridSystemModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">

                                    {!! Form::open([
                                        'url' => action(
                                            [
                                                \Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class,
                                                'updateEmployeeProfilePicture',
                                            ],
                                            [$user->id],
                                        ),
                                        'enctype' => 'multipart/form-data',
                                        'method' => 'PUT',
                                        'id' => 'update_profile_picture_form',
                                    ]) !!}
                                    {!! Form::hidden('delete_image', '0', ['id' => 'delete_image_input']) !!}

                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">@lang('essentials::lang.edit_profile_picture')</h4>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                <img src="" id="popupImage" alt="@lang('essentials::lang.profile_picture')"
                                                    style="max-width: 100%; height: auto;" />
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::file('profile_picture', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                                                </div>

                                            </div>
                                            <div class="col-md-3">
                                                <button type="button"
                                                    class="btn btn-danger deleteImage">@lang('messages.delete')</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary saveImage"
                                            disabled>@lang('messages.save')</button>
                                        <button type="button" class="btn btn-default"
                                            data-dismiss="modal">@lang('messages.close')</button>
                                    </div>
                                    {!! Form::close() !!}
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
            $('#profileImageLink').on('click', function(e) {
                e.preventDefault(); // Prevent default anchor action
                openImagePopup();
            });

            let imageChanged = false;

            $('#profileImageLink').on('click', function(e) {
                e.preventDefault();
                openImagePopup();
            });

            $('.deleteImage').on('click', function() {
                $('#popupImage').attr('src', ''); // Remove image source
                $('input[type="file"]').val(''); // Clear file input
                $('#delete_image_input').val('1'); // Indicate that the image should be deleted
                imageChanged = true;
                enableSaveButton();
            });


            $('input[type="file"]').on('change', function() {
                previewImage(event);
                imageChanged = true;
                enableSaveButton();
            });

            function enableSaveButton() {
                $('.saveImage').prop('disabled', !imageChanged);
            }

            $('#update_profile_picture_form').submit(function(e) {
                if (!imageChanged) {
                    e.preventDefault(); // Prevent form submission if no changes made
                }
            });

            function openImagePopup() {
                console.log('before');
                $('#popupImage').attr('src', $('#profileImage').attr('src'));
                $('#imagePopupModal').modal('show');
                console.log($('#profileImage').attr('src'));
                console.log('after');
            }

            function previewImage(event) {
                var reader = new FileReader();
                reader.onload = function() {
                    var output = document.getElementById('popupImage');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }

        });




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
