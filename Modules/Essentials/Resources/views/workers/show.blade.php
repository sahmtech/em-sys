@extends('layouts.app')

@section('title', __('essentials::lang.view_worker'))
<style>
    /* Modal background styling */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        /* Darker overlay for better contrast */
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        transition: opacity 0.3s ease;
        /* Smooth fade-in transition */
    }

    /* Modal content styling */
    .modal-content {
        position: relative;
        width: 100%;
        max-width: 1000px;
        /* Adjusted max width for better scaling */
        height: auto;
        /* Allow height to adjust based on content */
        background: #fff;
        border-radius: 12px;
        /* Rounded corners */
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
        /* Deeper shadow for depth */
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 20px;
        /* Consistent padding */
    }

    /* Close button styling */
    .close {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 50px;
        /* Increased size for better visibility */
        height: 50px;
        /* Increased size for better visibility */
        background-color: #268de0;
        /* Red color for visibility */
        border: none;
        border-radius: 50%;
        /* Circular shape */
        font-size: 28px;
        /* Increased font size */
        font-weight: bold;
        color: #fff;
        /* White text for contrast */
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        /* Transition effects */
        display: flex;
        align-items: center;
        justify-content: center;
        /* Center the text */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        /* Add subtle shadow */
    }

    .close:hover {
        background-color: #ff1a1a;
        /* Darker red on hover */
        transform: scale(1.1);
        /* Scale effect */
    }

    /* Iframe styling */
    .modal-content iframe {
        width: 100%;
        height: 400px;
        /* Fixed height for iframe */
        border: none;
        border-radius: 0 0 12px 12px;
        /* Rounded corners */
        margin-top: 10px;
        /* Spacing above iframe */
    }

    /* Title styling */
    .modal-title {
        font-size: 28px;
        /* Title font size */
        font-weight: bold;
        /* Bold title */
        margin-bottom: 10px;
        /* Space below title */
        color: #333;
        /* Dark text color */
    }

    /* Description styling */
    .modal-description {
        font-size: 16px;
        /* Description font size */
        color: #666;
        /* Lighter text color for description */
        margin-bottom: 20px;
        /* Space below description */
        line-height: 1.5;
        /* Line height for readability */
    }

    /* Button styling */
    .modal-button {
        margin-top: 2px;
        /* Add a top margin of 2 pixels */

        align-self: flex-end;
        /* Align to the right */
        padding: 12px 24px;
        /* Button padding */
        background-color: #007bff;
        /* Primary button color */
        color: #fff;
        /* Button text color */
        border: none;
        /* No border */
        border-radius: 5px;
        /* Slightly rounded button */
        cursor: pointer;
        /* Pointer cursor */
        transition: background-color 0.3s, transform 0.2s;
        /* Smooth transition */
        font-size: 16px;
        /* Font size */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        /* Subtle shadow */
    }

    .modal-button:hover {
        background-color: #0056b3;
        /* Darker blue on hover */
        transform: scale(1.05);
        /* Scale effect on hover */
    }

    /* Adjusting for smaller screens */
    @media (max-width: 600px) {
        .modal-content {
            width: 95%;
            /* Full width on smaller screens */
            padding: 15px;
            /* Less padding on small screens */
        }

        .modal-title {
            font-size: 24px;
            /* Smaller title on small screens */
        }

        .modal-description {
            font-size: 14px;
            /* Smaller description */
        }
    }
</style>
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
                        @if ($can_edit)
                            <a href="{{ $from == 'hrm'
                                ? route('hrm.editWorker', ['id' => $user->id, 'from' => $from])
                                : route('editWorker', ['id' => $user->id, 'from' => $from]) }}"
                                class="btn btn-primary btn-block">
                                <i class="glyphicon glyphicon-edit"></i>
                                @lang('messages.edit')
                            </a>
                        @endif
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
                                <input type="checkbox" name="admissions_to_work"
                                    {{ $admissions_to_work ? 'checked' : '' }}>
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
                                                @if ($document->document)
                                                    {{ $document->document->name_ar ?? ('' . ' - ' . $document->document->name_en ?? '') }}
                                                @else
                                                    {{ $document->attachment->name_ar ?? ('' . ' - ' . $document->attachment->name_en ?? '') }}
                                                @endif
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
                            <a href="#attachments_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-file" aria-hidden="true"></i>

                                @lang('followup::lang.attachements')</a>
                        </li>

                        <li>
                            <a href="#leaves_used_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-file" aria-hidden="true"></i>

                                @lang('followup::lang.leaves_used')</a>
                        </li>

                        <li>
                            <a href="#assets_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-laptop" aria-hidden="true"></i>

                                @lang('followup::lang.asset')</a>
                        </li>
                        <li>
                            <a href="#penaltiesViolations_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-gavel" aria-hidden="true"></i>

                                @lang('followup::lang.penalties_violations')</a>
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
                                                                class="btn-modal btn btn-xs  btn-info"><i
                                                                    class="fa fa-eye" aria-hidden="true"></i>
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

                        <div class="tab-pane" id="attachments_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('essentials::lang.attachment_name')</th>
                                                    <th>@lang('essentials::lang.order_type')</th>
                                                    <th>@lang('essentials::lang.upload_date')</th>
                                                    <th>@lang('essentials::lang.added_by')</th>
                                                    <th>@lang('messages.view')</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($request_attachments as $attachment)
                                                    <tr>
                                                        <td>{{ $attachment->name ?? '' }}</td>
                                                        <td>{{ $request->requestType->type ?? '' }}</td>
                                                        <td>{{ $attachment->created_at->format('Y-m-d') ?? '' }}</td>

                                                        <td>
                                                            {{ trim(($attachment->addedBy->first_name ?? '') . ' ' . ($attachment->addedBy->mid_name ?? '') . ' ' . ($attachment->addedBy->last_name ?? '')) }}
                                                        </td>

                                                        <td>

                                                            <!-- Button to Open Modal -->
                                                            <a href="javascript:void(0)"
                                                                onclick="viewFile('{{ asset('uploads/' . $attachment->file_path) }}')"
                                                                class="btn btn-xs btn-info">
                                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                                                @lang('messages.view')
                                                            </a>

                                                            <!-- Modal for Preview -->
                                                            <div id="fileModal" class="modal" style="display: none;">
                                                                <div class="modal-content">
                                                                    <span class="close"
                                                                        onclick="closeModal()">&times;</span>
                                                                    <iframe id="fileFrame" src=""></iframe>
                                                                    <div class="modal-controls">
                                                                        <button id="printBtn"
                                                                            class="modal-button btn btn-primary"
                                                                            onclick="printFile()">
                                                                            <i class="fa fa-print" aria-hidden="true"></i>
                                                                            @lang('messages.print')</button>
                                                                        <button id="fullScreenBtn"
                                                                            class="modal-button btn btn-primary "
                                                                            onclick="toggleFullScreen()">
                                                                            <i class="fa fa-arrows-alt"
                                                                                aria-hidden="true"></i>
                                                                            @lang('messages.full_screen')</button>
                                                                        <button class="modal-button btn btn-primary"
                                                                            onclick="closeModal()">
                                                                            @lang('messages.close')
                                                                            <!-- Optional translation for close -->
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>





                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="leaves_used_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('followup::lang.leave_type')</th>
                                                    <th>@lang('essentials::lang.duration')</th>
                                                    <th>@lang('essentials::lang.gender')</th>
                                                    <th>@lang('essentials::lang.date')</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($user_leave_balances as $user_leave_balance)
                                                    <tr>
                                                        <td>{{ $user_leave_balance->leave_type->leave_type ?? '' }}</td>

                                                        <td>
                                                            {{ $user_leave_balance->amount ?? '' }}
                                                        </td>

                                                        <td>

                                                            @if ($user_leave_balance->user->gender ?? null)
                                                                {{ $user_leave_balance->user->gender === 'male' ? 'ذكر' : 'أنثى' }}
                                                            @else
                                                                {{-- Handle cases where gender is null or not set --}}
                                                                غير محدد
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $user_leave_balance->created_at ? $user_leave_balance->created_at->format('Y-m-d') : '' }}
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="assets_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('followup::lang.asset_name')</th>
                                                    <th>@lang('followup::lang.asset_code')</th>
                                                    <th>@lang('followup::lang.delivery_date')</th>
                                                    <th>@lang('followup::lang.asset_category')</th>
                                                    <th>@lang('followup::lang.quantity')</th>
                                                    <th>@lang('followup::lang.createdBy')</th>
                                                    <th>@lang('followup::lang.business_id')</th>
                                                    <th>@lang('followup::lang.note')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assets as $asset)
                                                    @php
                                                        $assetDetails = $asset->asset ?? null;
                                                        $createdBy = $assetDetails->createdBy ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $assetDetails->name ?? '' }}</td>
                                                        <td>{{ $assetDetails->asset_code ?? '' }}</td>
                                                        <td>{{ $asset->created_at ? $asset->created_at->format('Y-m-d') : '' }}
                                                        </td>
                                                        <td>{{ $assetDetails->assetCategory->name ?? '' }}</td>
                                                        <td>{{ (int) ($asset->quantity ?? 0) }}</td>
                                                        <td>
                                                            {{ trim(($createdBy->first_name ?? '') . ' ' . ($createdBy->mid_name ?? '') . ' ' . ($createdBy->last_name ?? '')) ?: '' }}
                                                        </td>
                                                        <td>{{ $assetDetails->company->name ?? '' }}</td>

                                                        <td>{{ $assetDetails->description ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="penaltiesViolations_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="payroll_group_table"
                                            style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>@lang('followup::lang.descrption')</th>
                                                    <th>@lang('followup::lang.amount')</th>
                                                    <th>@lang('followup::lang.date')</th>
                                                    <th>@lang('followup::lang.createdBy')</th>
                                                    <th>@lang('followup::lang.company')</th>
                                                    <th>@lang('followup::lang.implement_status')</th>
                                                    <th>@lang('followup::lang.application_date')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($penalties as $penaltie)
                                                    <tr>
                                                        <td>{{ $penaltie->violationPenalties->descrption ?? '' }}</td>

                                                        <td>{{ $penaltie->violationPenalties->amount ?? '' }}</td>

                                                        <td>{{ $penaltie->created_at ? $penaltie->created_at->format('Y-m-d') : '' }}
                                                        </td>
                                                        <td>
                                                            {{ trim(($penaltie->addedBy->first_name ?? '') . ' ' . ($penaltie->addedBy->mid_name ?? '') . ' ' . ($penaltie->addedBy->last_name ?? '')) ?: '' }}
                                                        </td>



                                                        <td>{{ $penaltie->company->name ?? '' }}</td>
                                                        <td>
                                                            @if ($penaltie->status === 0)
                                                                <i class="fas fa-times-circle text-danger"></i>
                                                                @lang('followup::lang.Not implemented')
                                                            @elseif($penaltie->status === 1)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                                @lang('followup::lang.Implemented')
                                                            @else
                                                                {{ $penaltie->status ?? '' }}
                                                            @endif
                                                        </td>

                                                        <td>
                                                            {{ $penaltie->application_date ? $penaltie->application_date : '' }}
                                                        </td>



                                                    </tr>
                                                @endforeach
                                            </tbody>

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

        $(document).ready(function() {
            $(document).on('click', '.btn-modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url'); // Get the URL from the data-url attribute

                // Load content into the modal
                $('.view_modal .modal-body').load(url, function() {
                    $('#viewModal').modal('show'); // Show the modal after content is loaded
                });
            });
        });

        function viewFile(filePath) {
            const fileExtension = filePath.split('.').pop().toLowerCase();
            const fileFrame = document.getElementById('fileFrame');
            const fileModal = document.getElementById('fileModal');

            if (fileExtension === 'xlsx' || fileExtension === 'xls') {
                alert('This file is an Excel document. It will be downloaded for viewing.');
                window.location.href = filePath; // Initiate download
            } else if (fileExtension === 'pdf' || ['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                fileFrame.src = filePath; // Set the iframe source
                fileModal.style.display = 'flex'; // Show the modal
                resetModalSize(); // Reset size when opening a new file
                toggleFullScreen(); // Automatically request full screen
            } else {
                alert('Unsupported file type.');
            }
        }


        function closeModal() {
            // Hide the modal
            document.getElementById('fileModal').style.display = 'none';
            // Reset the iframe src to prevent caching issues
            document.getElementById('fileFrame').src = '';
        }

        function printFile() {
            const iframe = document.getElementById('fileFrame');
            if (iframe.src) {
                iframe.contentWindow.print(); // Call the print function from the iframe
            } else {
                alert('No file to print.');
            }
        }

        function toggleFullScreen() {
            const fileFrameSrc = document.getElementById('fileFrame').src;
            if (fileFrameSrc) {
                // Open the file in a new window with full dimensions
                const newWindow = window.open(fileFrameSrc, '_blank', 'width=' + screen.width + ',height=' + screen.height +
                    ',top=0,left=0,resizable=yes');
                newWindow.focus(); // Focus the new window
            } else {
                alert('No file is currently open to view in full screen.');
            }
        }
    </script>
@endsection
