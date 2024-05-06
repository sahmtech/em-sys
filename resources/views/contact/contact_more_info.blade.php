@php
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp

@if(!empty($contact->custom_field1))
    <strong>{{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field1 }}
    </p>
@endif

@if(!empty($contact->custom_field2))
    <strong>{{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field2 }}
    </p>
@endif

@if(!empty($contact->custom_field3))
    <strong>{{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field3 }}
    </p>
@endif

@if(!empty($contact->custom_field4))
    <strong>{{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field4 }}
    </p>
@endif

@if(!empty($contact->custom_field5))
    <strong>{{ $custom_labels['contact']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field5 }}
    </p>
@endif

@if(!empty($contact->custom_field6))
    <strong>{{ $custom_labels['contact']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field6 }}
    </p>
@endif

@if(!empty($contact->custom_field7))
    <strong>{{ $custom_labels['contact']['custom_field_7'] ?? __('lang_v1.custom_field', ['number' => 7]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field7 }}
    </p>
@endif
@if(!empty($contact->custom_field8))
    <strong>{{ $custom_labels['contact']['custom_field_8'] ?? __('lang_v1.custom_field', ['number' => 8]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field8 }}
    </p>
@endif
@if(!empty($contact->custom_field9))
    <strong>{{ $custom_labels['contact']['custom_field_9'] ?? __('lang_v1.custom_field', ['number' => 9]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field9 }}
    </p>
@endif

@if(!empty($contact->custom_field10))
    <strong>{{ $custom_labels['contact']['custom_field_10'] ?? __('lang_v1.custom_field', ['number' => 10]) }}</strong>
    <p class="text-muted">
        {{ $contact->custom_field10 }}
    </p>
@endif

<div class="col-md-12">
    <strong>
        <h4>@lang('sales::lang.Contract_signer_details'):</h4>
    </strong>
    <br>
</div>

<div class="col-md-4">

    <p><strong>@lang('sales::lang.first_name_cs'):</strong>
        @if (!empty($contactSigners->first_name))
            {{ $contactSigners->first_name }}
        @endif
    </p>
    <br>

    <p><strong>@lang('sales::lang.last_name_cs'):</strong>
        @if (!empty($contactSigners->last_name))
            {{ $contactSigners->last_name }}
        @endif
    </p>
    <br>
    <p><strong>@lang('sales::lang.nationality_cs'):</strong>
        @if (!empty($contactSigners->country->id))
            {{ $contactSigners->country->nationality }}
        @endif
    </p>
</div>

<div class="col-md-4">
    <p><strong>@lang('sales::lang.english_name_cs'):</strong>
        @if (!empty($contactSigners->english_name))
            {{ $contactSigners->english_name }}
        @endif
    </p>
    <br>

    <p><strong>@lang('sales::lang.identityNO_cs'):</strong>
        @if (!empty($contactSigners->identity_number))
            {{ $contactSigners->identity_number }}
        @endif
    </p>
    {{-- <br>
    <p>
        <strong>@lang('sales::lang.allow_login'):</strong>
        @if (!empty($contactSigners->allow_login))
            <span>@lang('sales::lang.allowlogin')</span>
        @else
            <span>@lang('sales::lang.notallowlogin')</span>
        @endif
    </p> --}}

</div>

<div class="col-md-4">

    <p><strong>@lang('sales::lang.email_cs'):</strong>
        @if (!empty($contactSigners->email))
            {{ $contactSigners->email }}
        @endif
    </p>
</div>
<br>


