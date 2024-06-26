@extends('layouts.app')
@section('title', __('sales::lang.edit_customer'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.edit_customer')</span>
        </h1>
    </section>
    <section class="content">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {!! Form::open([
                    'route' => ['sale.UpdateCustomer', $contact->id],
                    'method' => 'put',
                    'id' => 'add_country_form',
                ]) !!}


                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('sales::lang.edit_customer')</h4>
                </div>

                <div class="modal-body">
                    <div class="row">




                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-id-badge"></i>
                                    </span>
                                    <input type="hidden" id="hidden_id" value="{{ $contact->id }}">
                                    {!! Form::text('contact_id', $contact->contact_id, [
                                        'class' => 'form-control',
                                        'placeholder' => __('lang_v1.contact_id'),
                                    ]) !!}
                                </div>
                                <p class="help-block">
                                    @lang('lang_v1.leave_empty_to_autogenerate')
                                </p>
                            </div>
                        </div>

                    </div>



                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('supplier_business_name', __('business.business_name') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-id-badge"></i>
                                </span>
                                <input type="hidden" id="hidden_id" value="{{ $contact->supplier_business_name }}">
                                {!! Form::text('supplier_business_name', $contact->supplier_business_name, [
                                    'class' => 'form-control',
                                    'placeholder' => __('business.business_name'),
                                ]) !!}
                            </div>

                        </div>

                    </div>
                    <input type="hidden" id="page" name="page" value="{{ $page }}">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('commercial_register_no', __('sales::lang.commercial_register_no') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-id-badge"></i>
                                </span>
                                <input type="hidden" id="hidden_id" value="{{ $contact->commercial_register_no }}">
                                {!! Form::text('commercial_register_no', $contact->commercial_register_no, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.commercial_register_no'),
                                ]) !!}
                            </div>

                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-id-badge"></i>
                                </span>
                                <input type="hidden" id="hidden_id" value="{{ $contact->mobile }}">
                                {!! Form::text('mobile', $contact->mobile, ['id' => 'mobile', 'class' => 'form-control']) !!}
                            </div>
                            <span id="mobile-error" class="text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-id-badge"></i>
                                </span>
                                <input type="hidden" id="hidden_id" value="{{ $contact->alternate_number }}">
                                {!! Form::text('alternate_number', $contact->alternate_number, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.alternate_number'),
                                ]) !!}
                            </div>

                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                {!! Form::email('email', $contact->email ?? '', ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <h4 class="modal-title">@lang('sales::lang.Contract_signer_details')</h4>
                    <div class="col-md-12">
                        <hr />
                    </div>
                    <br>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('first_name', __('sales::lang.first_name_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                @if (isset($contactSigners) && !empty($contactSigners->first_name))
                                    {!! Form::text('first_name_cs', $contactSigners->first_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.first_name_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('first_name_cs', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.first_name_cs'),
                                    ]) !!}
                                @endif


                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('last_name', __('sales::lang.last_name_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->last_name) ? $contactSigners->last_name : '' }}">

                                @if (isset($contactSigners) && !empty($contactSigners->last_name))
                                    {!! Form::text('last_name_cs', $contactSigners->last_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.last_name_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('last_name_cs', '', ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cs')]) !!}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('english_name', __('sales::lang.english_name_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->english_name) ? $contactSigners->english_name : '' }}">

                                @if (isset($contactSigners) && !empty($contactSigners->english_name))
                                    {!! Form::text('english_name_cs', $contactSigners->english_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.english_name_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('english_name_cs', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.english_name_cs'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('nationality_cs', __('sales::lang.nationality_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                @if (!empty($contactSigners->country->id))
                                    {!! Form::select('nationality_cs', $nationalities, $contactSigners->country->id, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('Select Nationality'),
                                    ]) !!}
                                @else
                                    {!! Form::select('nationality_cs', $nationalities, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('Select Nationality'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email_cs', __('sales::lang.email_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->email) ? $contactSigners->email : '' }}">

                                @if (isset($contactSigners) && !empty($contactSigners->email))
                                    {!! Form::text('email_cs', $contactSigners->email ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.email_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('email_cs', '', ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cs')]) !!}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('identityNO_cs', __('sales::lang.identityNO_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->id_proof_number) ? $contactSigners->id_proof_number : '' }}">

                                @if (isset($contactSigners) && !empty($contactSigners->id_proof_number))
                                    {!! Form::text('identityNO_cs', $contactSigners->id_proof_number ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.identityNO_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('identityNO_cs', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.identityNO_cs'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-md-12">
    <div class="form-group">
        <div class="input-group">
            <label>
            @if (isset($contactSigners) && !empty($contactSigners->allow_login))
                {!! Form::checkbox('allow_login', 1, $contactSigners->allow_login == 1, ['id' => 'allow_login']); !!}
                @else
                {!! Form::checkbox('allow_login', 1, null, ['id' => 'allow_login']); !!}
            @endif
                <strong>@lang('sales::lang.allow_login')</strong>
            </label>
        </div>
    </div>
</div> --}}



                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('username_cs', __('sales::lang.username_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="username_cs_id"
                                    value="{{ !empty($contactSigners->username) ? $contactSigners->username : '' }}">
                                @if (isset($contactSigners) && !empty($contactSigners->username))
                                    {!! Form::text('username_cs', $contactSigners->username ?? '', [
                                        'id' => 'username_cs',
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.username_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::text('username_cs', '', [
                                        'id' => 'username_cs',
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.username_cs'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div> --}}

                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('password_cs', __('sales::lang.password_cs') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="password_cs_id"
                                    value="{{ !empty($contactSigners->password) ? $contactSigners->password : '' }}">
                                @if (isset($contactSigners) && !empty($contactSigners->password))
                                    {!! Form::password('password_cs', [
                                        'id' => 'password_cs',
                                        'style' => 'height:40px;',
                                        'class' => 'form-control password',
                                        'placeholder' => __('sales::lang.password_cs'),
                                    ]) !!}
                                @else
                                    {!! Form::password('password_cs', [
                                        'id' => 'password_cs',
                                        'style' => 'height:40px;',
                                        'class' => 'form-control password',
                                        'placeholder' => __('sales::lang.password_cs'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div> --}}





                    <div class="clearfix"></div>

                    <h4 class="modal-title">@lang('sales::lang.Contract_follower_details')</h4>
                    <div class="col-md-12">
                        <hr />
                    </div>
                    <br>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('first_name', __('sales::lang.first_name_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->first_name))
                                    {!! Form::text('first_name_cf', $contactFollower->first_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.first_name_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('first_name_cf', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.first_name_cf'),
                                    ]) !!}
                                @endif


                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('last_name', __('sales::lang.last_name_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->last_name))
                                    {!! Form::text('last_name_cf', $contactFollower->last_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.last_name_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('last_name_cf', '', ['class' => 'form-control', 'placeholder' => __('sales::lang.last_name_cf')]) !!}
                                @endif


                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('english_name', __('sales::lang.english_name_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->english_name))
                                    {!! Form::text('english_name_cf', $contactFollower->english_name ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.english_name_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('english_name_cf', '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.english_name_cf'),
                                    ]) !!}
                                @endif


                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email_cf', __('sales::lang.email_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->first_name) ? $contactSigners->first_name : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->email))
                                    {!! Form::text('email_cf', $contactFollower->email ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.email_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('email_cf', '', ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cf')]) !!}
                                @endif


                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('mobile_cf', __('sales::lang.mobile_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="hidden_id"
                                    value="{{ !empty($contactSigners->contact_number) ? $contactSigners->contact_number : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->contact_number))
                                    {!! Form::text('mobile_cf', $contactFollower->contact_number ?? '', [
                                        'class' => 'form-control',
                                        'id' => 'mobile_cf',
                                        'placeholder' => __('sales::lang.mobile_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('mobile_cf', '', [
                                        'class' => 'form-control',
                                        'id' => 'mobile_cf',
                                        'placeholder' => __('sales::lang.mobile_cf'),
                                    ]) !!}
                                @endif


                            </div>
                            <div id="mobile_cf_error" class="text-danger"></div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        {{-- <div class="form-group">
        <div class="input-group">
            <label>
            @if (isset($contactFollower) && !empty($contactFollower->allow_login))
                {!! Form::checkbox('allow_login_cf', 1, $contactFollower->allow_login == 1, ['id' => 'allow_login_cf']); !!}
                @else
                {!! Form::checkbox('allow_login_cf', 1, null, ['id' => 'allow_login_cf']); !!}                                                             
                @endif
                <strong>@lang('sales::lang.allow_login')</strong>
            </label>
        </div>
    </div> --}}
                    </div>


                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('username_cf', __('sales::lang.username_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="username_cf_id"
                                    value="{{ !empty($contactFollower->username) ? $contactFollower->username : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->username))
                                    {!! Form::text('username_cf', $contactFollower->username ?? '', [
                                        'class' => 'form-control',
                                        'id' => 'username_cf',
                                        'placeholder' => __('sales::lang.username_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::text('username_cf', '', [
                                        'class' => 'form-control',
                                        'id' => 'username_cf',
                                        'placeholder' => __('sales::lang.username_cf'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div> --}}

                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('password_cf', __('sales::lang.password_cf') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                <input type="hidden" id="password_cf"
                                    value="{{ !empty($contactFollower->password) ? $contactFollower->password : '' }}">

                                @if (isset($contactFollower) && !empty($contactFollower->password))
                                    {!! Form::password('password_cf', [
                                        'class' => 'form-control',
                                        'id' => 'password_cf',
                                        'style' => 'height:40px;',
                                        'placeholder' => __('sales::lang.password_cf'),
                                    ]) !!}
                                @else
                                    {!! Form::password('password_cf', [
                                        'class' => 'form-control',
                                        'id' => 'password_cf',
                                        'style' => 'height:40px;',
                                        'placeholder' => __('sales::lang.password_cf'),
                                    ]) !!}
                                @endif
                            </div>
                        </div>
                    </div> --}}



                </div>
                <div class="clearfix"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.update')</button>

                </div>

                {!! Form::close() !!}

            </div><!-- /.modal-content -->

        </div><!-- /.modal-dialog -->
    </section>
@endsection

@section('javascript')

    <script>
        $(document).ready(function() {

            $('#allow_login').change(function() {

                if ($(this).is(':checked')) {

                    $('#username_cs').val($('#username_cs_id').val());
                    $('#password_cs').val($('#password_cs_id').val());



                } else {

                    $('#username_cs').val('');
                    $('#password_cs').val('');
                }
            });


            $('#allow_login').change();
        });
    </script>
    <script>
        $(document).ready(function() {

            $('#allow_login_cf').change(function() {

                if ($(this).is(':checked')) {

                    $('#username_cf').val($('#username_cf_id').val());
                    $('#password_cf').val($('#password_cf_id').val());
                } else {

                    $('#username_cf').val('');
                    $('#password_cf').val('');
                }
            });


            $('#allow_login_cf').change();
        });
    </script>
    <script>
        var translations = {
            validate_mobile: @json(__('lang_v1.validate_mobile')),
        };
    </script>
    <script>
        $(document).ready(function() {
            $('#mobile').on('input', function() {
                var mobileNumber = $(this).val();
                var regexPattern = /^05\d{0,8}$/;

                if (!regexPattern.test(mobileNumber)) {
                    $('#mobile-error').text(translations.validate_mobile);
                } else {
                    $('#mobile-error').text('');
                }
            });
        });
    </script>







@endsection
