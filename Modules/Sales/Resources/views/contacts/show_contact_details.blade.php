@php
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<div class="row">
	<div class="col-md-12">
		<div class="col-md-12">
      
			<h4><strong>@lang('sales::lang.more_info')</strong></h4>
		</div>
        <br>
		<div class="col-md-4">
			<p><strong>@lang( 'sales::lang.contact_number' ):</strong>{{$contact->contact_id}}</p>
			<p><strong>@lang( 'sales::lang.contact_name_en' ):</strong> @if(!empty($contact->english_name)) {{$contact->english_name}}@endif</p>
			<p><strong>@lang( 'sales::lang.commercial_register_no' ):</strong> @if(!empty($contact->commercial_register_no)){{$contact->commercial_register_no}} @endif</p>
            <p><strong>@lang( 'sales::lang.contact_mobile'):</strong> @if(!empty($contact->mobile)){{$contact->mobile}} @endif</p>
            <p><strong>@lang( 'sales::lang.alternate_number'):</strong> @if(!empty($contact->alternate_number)){{$contact->alternate_number}} @endif</p>
            <p><strong>@lang( 'sales::lang.contact_email'):</strong> @if(!empty($contact->email)){{$contact->email}} @endif</p>
            <p><strong>@lang( 'sales::lang.contact_city'):</strong> @if(!empty($contact->city)){{$contact->city}} @endif</p>

		</div>
		
	

		<div class="clearfix"></div>
		<hr>

		<div class="col-md-12">
        <strong><h4>@lang('sales::lang.Contract_signer_details'):</h4></strong>
        <br>
		</div>
		
		<div class="col-md-4">

			<p><strong>@lang('sales::lang.first_name_cs'):</strong>@if(!empty($contactSigners[0]->first_name)) {{$contactSigners[0]->first_name }}@endif</p>
			<br>
			
			<p><strong>@lang('sales::lang.last_name_cs'):</strong>  @if(!empty($contactSigners[0]->last_name)) {{$contactSigners[0]->last_name }}@endif</p>
            <br>
			<p><strong>@lang('sales::lang.nationality_cs'):</strong>  @if(!empty($contactSigners[0]->nationality_cs)) {{$contactSigners[0]->nationality_cs }}@endif</p>
		</div>
		
        <div class="col-md-4">
			<p><strong>@lang('sales::lang.english_name_cs'):</strong>@if(!empty($contactSigners[0]->english_name)) {{$contactSigners[0]->english_name }}@endif</p>
			<br>
			<p><strong>@lang('sales::lang.capacity_cs'):</strong>  @if(!empty($contactSigners[0]->capacity_cs)) {{$contactSigners[0]->capacity_cs }}@endif</p>
			<br>
			<p><strong>@lang('sales::lang.email_cs'):</strong>  @if(!empty($contactSigners[0]->email_cs)) {{$contactSigners[0]->email_cs }}@endif</p>
		</div>
		<br>
        <div class="col-md-4">
			
			<br>
			<p><strong>@lang('sales::lang.identityNO_cs'):</strong>  @if(!empty($contactSigners[0]->identity_number)) {{$contactSigners[0]->identity_number }}@endif</p>
			<br>
			<p>
			<strong>@lang('sales::lang.allow_login'):</strong>
			@if(!empty($contactSigners[0]->allow_login))
				<span>@lang('sales::lang.allowlogin')</span>
				@if(!empty($contactSigners[0]->username))
					<span style="margin-left: 10px;">{{$contactSigners[0]->username}}</span>
				@endif
			@else
				<span>@lang('sales::lang.notallowlogin')</span>
			@endif
		</p>
		</div>
		<br>

        <div class="clearfix"></div>
		<hr>

		<div class="col-md-12">
        <strong><h4>@lang('sales::lang.Contract_follower_details'):</h4></strong>
        <br>
		<div class="col-md-4">

			<p><strong>@lang('sales::lang.first_name_cf'):</strong>@if(!empty($contactFollower[0]->first_name)) {{$contactFollower[0]->first_name }}@endif</p>
			<br>
			
			<p><strong>@lang('sales::lang.last_name_cf'):</strong>  @if(!empty($contactFollower[0]->last_name)) {{$contactFollower[0]->last_name }}@endif</p>
            <br>
			
		</div>
		</div>
	</div>
</div>