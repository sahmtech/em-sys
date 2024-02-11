@extends('layouts.app')
@section('title', __('sales::lang.lead_contacts'))

@section('content')
    @include('sales::layouts.nav_contact')

    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.lead_contacts')</span>
        </h1>
    </section>



    <section class="content">



        @component('components.widget', ['class' => 'box-primary'])
    
        @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("sales.add_draft_contact"))
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addContactModal">
                        <i class="fa fa-plus"></i> @lang('sales::lang.add_draft_contact')
                    </button>
                </div>
            @endslot
        @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cust_table">
                    <thead>
                        <tr>
                            {{-- <th>
                                <input type="checkbox" id="select-all">
                            </th> --}}
                            <th>#</th>
                            <th>@lang('sales::lang.contact_number')</th>
                            <th>@lang('sales::lang.supplier_business_name')</th>
                            <th>@lang('sales::lang.commercial_register_no')</th>
                            <th>@lang('sales::lang.created_by')</th>
                            <th>@lang('sales::lang.contact_mobile')</th>
                            <th>@lang('sales::lang.contact_email')</th>
                            <th>@lang('sales::lang.created_at')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>
                </table>
                {{-- <div style="margin-bottom: 10px;">
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="change-status-selected">
                        @lang('sales::lang.change_contact_status')
                    </button>
                </div> --}}
            </div>
        @endcomponent


        <div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'sale.storeCustomer']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('sales::lang.add_contact')</h4>
                    </div>

                    <div class="modal-body">


                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('contact_name', __('sales::lang.contact_name') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    {!! Form::text('contact_name', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('sales::lang.contact_name'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('name_en', __('sales::lang.name_en') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-font"></i>
                                    </span>
                                    {!! Form::text('name_en', null, [
                                        'class' => 'form-control',
                                        // 'required',
                                        'placeholder' => __('sales::lang.name_en'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        {{-- <div class="col-md-4 business">
                            <div class="form-group">
                                {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-briefcase"></i>
                                    </span>
                                    {!! Form::text('supplier_business_name', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('business.business_name'),
                                    ]) !!}
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-md-4 business">
                            <div class="form-group">
                                {!! Form::label('commercial_register_no', __('sales::lang.commercial_register_no') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-briefcase"></i>
                                    </span>
                                    {!! Form::text('commercial_register_no', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('sales::lang.commercial_register_no'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('mobile', __('contact.mobile') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-mobile"></i>
                                    </span>
                                    {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => __('contact.mobile')]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-phone"></i>
                                    </span>
                                    {!! Form::text('alternate_number', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('contact.alternate_contact_number'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('email', __('business.email') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                                </div>
                            </div>
                        </div>



                        <div class="form-group col-md-4">
                            {!! Form::label('arabic_name_for_city', __('essentials::lang.city') . ':') !!}
                            {!! Form::select('city', $cities, null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.city'),
                                'id' => 'cityDropdown',
                                'style' => 'height:40px',
                                'data-url' => route('getEnglishNameForCity'),
                            ]) !!}
                        </div>

                        <div class="form-group col-md-4">
                            {!! Form::label('english_name_for_city', __('essentials::lang.english_name_for_city') . ':') !!}
                            {!! Form::text('english_name_for_city', null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.english_name_for_city'),
                                'id' => 'relatedInput',
                                'readonly' => 'readonly',
                            ]) !!}
                        </div>


                        <div class="clearfix"></div>
                        <div class="col-md-6 lead_additional_div">
                            <div class="col-md-6 lead_additional_div">

                                <input type="hidden" name="selected_user_id" id="selected_user_id" value="">
                                <div class="clearfix"></div>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" id="moreInfoButton" class="btn btn-primary center-block more_btn"
                                    data-target="#more_div">@lang('sales::lang.add_Contract_signer') <i class="fa fa-chevron-down"></i></button>
                            </div>

                            <div id="more_div" class="hide">
                                {!! Form::hidden('position', null, ['id' => 'position']) !!}
                                <div class="col-md-12">
                                    <hr />
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('first_name', __('sales::lang.first_name_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            {!! Form::text('first_name_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.first_name_cs'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('last_name', __('sales::lang.last_name_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            {!! Form::text('last_name_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.last_name_cs'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('english_name', __('sales::lang.english_name_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-font"></i>
                                            </span>
                                            {!! Form::text('english_name_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.english_name_cs'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('mobile_cs', __('contact.mobile') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-mobile"></i>
                                            </span>
                                            {!! Form::text('mobile_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('contact.mobile'),
                                                'pattern' => '05\d{8}',
                                                'title' => 'Mobile number must start with 05 and be 10 digits long',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('nationality_cs', __('sales::lang.nationality_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-globe"></i>
                                            </span>
                                            {!! Form::select(
                                                'nationality_cs',
                                                $nationalities,
                                                !empty($user->nationality_id) ? $user->nationality_id : null,
                                                ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.nationality')],
                                            ) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('email_cs', __('sales::lang.email_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-envelope"></i>
                                            </span>
                                            {!! Form::text('email_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cs')]) !!}
                                        </div>
                                    </div>


                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('identityNO_cs', __('sales::lang.identityNO_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('identityNO_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.identityNO_cs'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <label>
                                                {!! Form::checkbox('allow_login_cs', 1, false, ['id' => 'allow_login_cs_checkbox']) !!} <strong>@lang('sales::lang.allow_login')</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="col-md-3" id="username_cs_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('username_cs', __('sales::lang.username_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('username_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cs')]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" id="password_cs_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('password_cs', __('sales::lang.password_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('password_cs', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cs')]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" id="confirm_password_cs_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('confirm_password_cs', __('sales::lang.confirm_password_cs') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('confirm_password_cs', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.confirm_password_cs'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>



                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" id="contract_follower"
                                    class="btn btn-primary center-block more_btn"
                                    data-target="#more_div">@lang('sales::lang.add_Contract_follower') <i class="fa fa-chevron-down"></i></button>
                            </div>

                            <div id="more_div2" class="hide">
                                {!! Form::hidden('position', null, ['id' => 'position']) !!}
                                <div class="col-md-12">
                                    <hr />
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('first_name', __('sales::lang.first_name_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            {!! Form::text('first_name_cf', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.first_name_cf'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('last_name', __('sales::lang.last_name_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            {!! Form::text('last_name_cf', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.last_name_cf'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>



                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('english_name', __('sales::lang.english_name_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-font"></i>
                                            </span>
                                            {!! Form::text('english_name_cf', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.english_name_cf'),
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>



                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('email_cf', __('sales::lang.email_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-envelope"></i>
                                            </span>
                                            {!! Form::text('email_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.email_cf')]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('mobile_cf', __('sales::lang.mobile_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-mobile"></i>
                                            </span>
                                            {!! Form::text('mobile_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.mobile_cf')]) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <label>
                                                {!! Form::checkbox('allow_login_cf', 1, false, ['id' => 'allow_login_cf_checkbox']) !!} <strong>@lang('sales::lang.allow_login')</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="col-md-3" id="username_cf_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('username_cf', __('sales::lang.username_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('username_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.username_cf')]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" id="password_cf_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('password_cf', __('sales::lang.password_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('password_cf', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.password_cf')]) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" id="confirm_password_cf_wrapper" style="display: none;">
                                    <div class="form-group">
                                        {!! Form::label('confirm_password_cf', __('sales::lang.confirm_password_cf') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-info"></i>
                                            </span>
                                            {!! Form::text('confirm_password_cf', null, [
                                                'class' => 'form-control',
                                                'placeholder' => __('sales::lang.confirm_password_cf'),
                                            ]) !!}
                                        </div>
                                    </div>
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
        </div>

     
    </section>
   
@endsection

@section('javascript')



    <script>
        $(document).ready(function() {


            var customers_table = $('#cust_table').DataTable({
                ajax: {
                    url: "{{ route('draft_contacts') }}",

                },
                processing: true,
                serverSide: true,
                info: false,


                columns: [
                    // {
                    //     data: null,
                    //     render: function(data, type, row, meta) {
                    //         return '<input type="checkbox" class="select-row" data-id="' + row.id +
                    //             '">';
                    //     },
                    //     orderable: false,
                    //     searchable: false,
                    // },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'contact_id',
                        name: 'contact_id'
                    },
                    {
                        data: 'supplier_business_name',
                        name: 'supplier_business_name'
                    },


                    {
                        data: 'commercial_register_no',
                        name: 'commercial_register_no'
                    },

                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },

                ]
            });

            $('#cityDropdown').on('change', function() {
                var selectedCity = $(this).val();
                var url = $(this).data('url');

                $.get(url, {
                    city: selectedCity
                }, function(data) {

                    $('#relatedInput').val(data.relatedData);
                });
            });

            $('input[name="mobile"]').on('input', function() {
                let mobileNumber = $(this).val();


                if (mobileNumber.length > 10) {
                    mobileNumber = mobileNumber.slice(0, 10);
                    $(this).val(mobileNumber);
                }


                if (!mobileNumber.startsWith('05')) {
                    if (mobileNumber.length >= 2) {
                        mobileNumber = '05' + mobileNumber.slice(2);
                        $(this).val(mobileNumber);
                    }
                }
            });

            // $('#allow_login_cs_checkbox').change(function() {

            //     if ($(this).prop('checked')) {
            //         $('#username_cs').prop('required', true);
            //         $('#password_cs').prop('required', true);
            //         $('#confirm_password_cs').prop('required', true);


            //         $('#username_cs_wrapper').show();
            //         $('#password_cs_wrapper').show();
            //         $('#confirm_password_cs_wrapper').show();
            //     } else {

            //         $('#username_cs').prop('required', false);
            //         $('#password_cs').prop('required', false);
            //         $('#confirm_password_cs').prop('required', false);

            //         $('#username_cs_wrapper').hide();
            //         $('#password_cs_wrapper').hide();
            //         $('#confirm_password_cs_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cf_checkbox').change(function() {
            //     if ($(this).prop('checked')) {
            //         $('#username_cf').prop('required', true);
            //         $('#password_cf').prop('required', true);
            //         $('#confirm_password_cf').prop('required', true);

            //         $('#username_cf_wrapper').show();
            //         $('#password_cf_wrapper').show();
            //         $('#confirm_password_cf_wrapper').show();
            //     } else {
            //         $('#username_cf').prop('required', false);
            //         $('#password_cf').prop('required', false);
            //         $('#confirm_password_cf').prop('required', false);

            //         $('#username_cf_wrapper').hide();
            //         $('#password_cf_wrapper').hide();
            //         $('#confirm_password_cf_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cs_checkbox').change(function() {
            //     if (this.checked) {
            //         $('#username_cs_wrapper').show();
            //         $('#password_cs_wrapper').show();
            //         $('#confirm_password_cs_wrapper').show();
            //     } else {
            //         $('#username_cs_wrapper').hide();
            //         $('#password_cs_wrapper').hide();
            //         $('#confirm_password_cs_wrapper').hide();
            //     }
            // });

            // $('#allow_login_cf_checkbox').change(function() {
            //     if (this.checked) {
            //         $('#username_cf_wrapper').show();
            //         $('#password_cf_wrapper').show();
            //         $('#confirm_password_cf_wrapper').show();
            //     } else {
            //         $('#username_cf_wrapper').hide();
            //         $('#password_cf_wrapper').hide();
            //         $('#confirm_password_cf_wrapper').hide();
            //     }
            // });


            $('#moreInfoButton').click(function() {
                $('#more_div').toggleClass('hide');
            });


            $('#contract_follower').click(function() {
                $('#more_div2').toggleClass('hide');
            });


            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#cust_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === cust_table.rows()
                    .count());
            });

            $('#change-status-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),

                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });


            // $(document).on('click', '.btn-change-to-lead', function() {
            //     var contactId = $(this).data('contact-id');


            //     if (requestId) {
            //         $.ajax({
            //             url: '{{ route('changeDraftStatus', ['contactId' => ':contactId']) }}'.replace(
            //                 ':contactId', contactId),
            //             method: 'GET',
            //             success: function(response) {
            //                 console.log(response);
            //                 if (response.success == true) {
            //                     toastr.success(response.msg);
            //                     customers_table.ajax.reload();
            //                 } else {
            //                     toastr.error(response.msg);
            //                 }
            //             }
            //         })
            //     }
            // });
        
$(document).on('click', '.btn-change-to-lead', function() {
    var contactId = $(this).data('contact-id');
 
    if (contactId) {
        $.ajax({
            url: '{{ route('changeDraftStatus', ['contactId' => ':contactId']) }}'.replace(
                            ':contactId', contactId),
        
            method: 'GET',
            success: function(response) {
                console.log(response);
                if (response.success == true) {
                    toastr.success(response.msg);
                    customers_table.ajax.reload();
                } else {
                    toastr.error(response.msg);
                }
            }
        });
    }
});




        });
    </script>

@endsection
