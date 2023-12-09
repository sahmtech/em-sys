<!-- worker_modal.blade.php -->
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="viewWorkerModalLabel">{{ __('messages.view') }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>@lang('lang_v1.username'):</strong> {{ $user->full_name ?? '' }}</p>
                    <p><strong>@lang('lang_v1.dob'):</strong> @if(optional($user->dob)->format_date) @endif</p>
                    <p><strong>@lang('lang_v1.gender'):</strong> @if($user->gender) @lang('lang_v1.' . $user->gender) @endif</p>
                    <p><strong>@lang('lang_v1.marital_status'):</strong> @if($user->marital_status) @lang('lang_v1.' . $user->marital_status) @endif</p>
                    <p><strong>@lang('lang_v1.blood_group'):</strong> {{ $user->blood_group ?? '' }}</p>
                    <p><strong>@lang('lang_v1.mobile_number'):</strong> {{ $user->contact_number ?? '' }}</p>
                    <p><strong>@lang('business.alternate_number'):</strong> {{ $user->alt_number ?? '' }}</p>
                    <p><strong>@lang('lang_v1.family_contact_number'):</strong> {{ $user->family_number ?? '' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>@lang('business.passport_number'):</strong> {{ $user->passport_number ?? '' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>@lang('lang_v1.permanent_address'):</strong><br>
                    <p>{{ $user->permanent_address ?? '' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>@lang('lang_v1.current_address'):</strong><br>
                    <p>{{ $user->current_address ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
