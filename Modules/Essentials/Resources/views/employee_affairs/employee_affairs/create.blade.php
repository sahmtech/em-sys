@extends('layouts.app')

@section('title', __('essentials::lang.add_new_employee'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.add_new_employee')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open(['route' => 'storeEmployee', 'enctype' => 'multipart/form-data',]) !!}
        <div class="row">
            <div class="col-md-12">
                @component('components.widget')

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                            {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('business.first_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                            {!! Form::text('mid_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('business.mid_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('last_name', __('business.last_name') . ':') !!}
                            {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('profile_picture', __('user.profile_picture') . ':') !!}
                            {!! Form::file('profile_picture', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                        </div>

                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('user_type', __('user.user_type') . ':*') !!}
                            {!! Form::select(
                                'user_type',
                                [
                                    'manager' => __('user.manager'),
                                    'employee' => __('user.employee'),
                                    'worker' => __('user.worker'),
                                ],
                                null,
                                [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'required',
                                    'id' => 'userTypeSelect',
                                    'placeholder' => __('user.user_type'),
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div id="workerInput" style="display: none;" class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('assigned_to', __('sales::lang.assigned_to') . ':*') !!}
                            {!! Form::select('assigned_to', $contacts, null, [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'placeholder' => __('sales::lang.assigned_to'),
                            ]) !!}
                        </div>
                    </div>
                    {{-- <div class="clearfix"></div>
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('email', __( 'business.email' ) . ':*') !!}
                {!! Form::text('email', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'business.email' ) ]); !!}
            </div>
          </div> --}}

                    {{-- <div class="col-md-4">
            <div class="form-group">
              <div class="checkbox">
                <br/>
                <label>
                    {!! Form::checkbox('is_active', 'active', true, ['class' => 'input-icheck status']); !!} {{ __('lang_v1.status_for_user') }}
                </label>
                @show_tooltip(__('lang_v1.tooltip_enable_user_active'))
              </div>
            </div>
          </div> --}}
                    @include('user.edit_profile_form_part')

                    @if (!empty($form_partials))
                        @foreach ($form_partials as $partial)
                            {!! $partial !!}
                        @endforeach
                    @endif
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-big"
                                id="submit_user_button">@lang('messages.save')</button>
                        </div>
                    </div>

                @endcomponent
            </div>
            {{-- <div class="col-md-12">
    @component('components.widget', ['title' => __('lang_v1.roles_and_permissions')])
      <div class="col-md-4">
        <div class="form-group">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('allow_login', 1, true, 
                [ 'class' => 'input-icheck', 'id' => 'allow_login']); !!} {{ __( 'lang_v1.allow_login' ) }}
              </label>
            </div>
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="user_auth_fields">
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('username', __( 'business.username' ) . ':') !!}
          @if (!empty($username_ext))
            <div class="input-group">
              {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => __( 'business.username' ) ]); !!}
              <span class="input-group-addon">{{$username_ext}}</span>
            </div>
            <p class="help-block" id="show_username"></p>
          @else
              {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => __( 'business.username' ) ]); !!}
          @endif
          <p class="help-block">@lang('lang_v1.username_help')</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('password', __( 'business.password' ) . ':*') !!}
            {!! Form::password('password', ['class' => 'form-control', 'required', 'placeholder' => __( 'business.password' ) ]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('confirm_password', __( 'business.confirm_password' ) . ':*') !!}
            {!! Form::password('confirm_password', ['class' => 'form-control', 'required', 'placeholder' => __( 'business.confirm_password' ) ]); !!}
        </div>
      </div>
    </div>
      <div class="clearfix"></div>
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('role', __( 'user.role' ) . ':*') !!} @show_tooltip(__('lang_v1.admin_role_location_permission_help'))
            {!! Form::select('role', $roles, null, ['class' => 'form-control select2']); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      <div class="col-md-3">
          <h4>@lang( 'role.access_locations' ) @show_tooltip(__('tooltip.access_locations_permission'))</h4>
        </div>
        <div class="col-md-9">
          <div class="col-md-12">
            <div class="checkbox">
                <label>
                  {!! Form::checkbox('access_all_locations', 'access_all_locations', true, 
                ['class' => 'input-icheck']); !!} {{ __( 'role.all_locations' ) }} 
                </label>
                @show_tooltip(__('tooltip.all_location_permission'))
            </div>
          </div>
          @foreach ($locations as $location)
          <div class="col-md-12">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('location_permissions[]', 'location.' . $location->id, false, 
                [ 'class' => 'input-icheck']); !!} {{ $location->name }} @if (!empty($location->location_id))({{ $location->location_id}}) @endif
              </label>
            </div>
          </div>
          @endforeach
        </div>
    @endcomponent
  </div> --}}

            {{-- <div class="col-md-12">
    @component('components.widget', ['title' => __('sale.sells')])
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('cmmsn_percent', __( 'lang_v1.cmmsn_percent' ) . ':') !!} @show_tooltip(__('lang_v1.commsn_percent_help'))
            {!! Form::text('cmmsn_percent', null, ['class' => 'form-control input_number', 'placeholder' => __( 'lang_v1.cmmsn_percent' ) ]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('max_sales_discount_percent', __( 'lang_v1.max_sales_discount_percent' ) . ':') !!} @show_tooltip(__('lang_v1.max_sales_discount_percent_help'))
            {!! Form::text('max_sales_discount_percent', null, ['class' => 'form-control input_number', 'placeholder' => __( 'lang_v1.max_sales_discount_percent' ) ]); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      
      <div class="col-md-4">
        <div class="form-group">
            <div class="checkbox">
            <br/>
              <label>
                {!! Form::checkbox('selected_contacts', 1, false, 
                [ 'class' => 'input-icheck', 'id' => 'selected_contacts']); !!} {{ __( 'lang_v1.allow_selected_contacts' ) }}
              </label>
              @show_tooltip(__('lang_v1.allow_selected_contacts_tooltip'))
            </div>
        </div>
      </div>
      <div class="col-sm-4 hide selected_contacts_div">
          <div class="form-group">
              {!! Form::label('user_allowed_contacts', __('lang_v1.selected_contacts') . ':') !!}
              <div class="form-group">
                  {!! Form::select('selected_contact_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'style' => 'width: 100%;', 'id' => 'user_allowed_contacts' ]); !!}
              </div>
          </div>
      </div>

    @endcomponent
  </div> --}}



        </div>
        {!! Form::close() !!}
    @stop
    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {
                $('#userTypeSelect').on('change', function() {
                    var userType = $('#userTypeSelect').val();
                    if (userType === 'worker') {
                        $('#workerInput').show();
                    } else {
                        $('#workerInput').hide();
                    }
                });
            });
        </script>
        <script type="text/javascript">
            __page_leave_confirmation('#user_add_form');
            $(document).ready(function() {
                $('#selected_contacts').on('ifChecked', function(event) {
                    $('div.selected_contacts_div').removeClass('hide');
                });
                $('#selected_contacts').on('ifUnchecked', function(event) {
                    $('div.selected_contacts_div').addClass('hide');
                });

                $('#allow_login').on('ifChecked', function(event) {
                    $('div.user_auth_fields').removeClass('hide');
                });
                $('#allow_login').on('ifUnchecked', function(event) {
                    $('div.user_auth_fields').addClass('hide');
                });

                $('#user_allowed_contacts').select2({
                    ajax: {
                        url: '/contacts/customers',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term, // search term
                                page: params.page,
                                all_contact: true
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data,
                            };
                        },
                    },
                    templateResult: function(data) {
                        var template = '';
                        if (data.supplier_business_name) {
                            template += data.supplier_business_name + "<br>";
                        }
                        template += data.text + "<br>" + LANG.mobile + ": " + data.mobile;

                        return template;
                    },
                    minimumInputLength: 1,
                    escapeMarkup: function(markup) {
                        return markup;
                    },
                });

                $('form#user_add_form').validate({
                    rules: {
                        first_name: {
                            required: true,
                        },
                        email: {
                            email: true,
                            remote: {
                                url: "/business/register/check-email",
                                type: "post",
                                data: {
                                    email: function() {
                                        return $("#email").val();
                                    }
                                }
                            }
                        },
                        password: {
                            required: true,
                            minlength: 5
                        },
                        confirm_password: {
                            equalTo: "#password"
                        },

                    },
                    messages: {
                        password: {
                            minlength: 'Password should be minimum 5 characters',
                        },
                        confirm_password: {
                            equalTo: 'Should be same as password'
                        },
                        username: {
                            remote: 'Invalid username or User already exist'
                        },
                        email: {
                            remote: '{{ __('validation.unique', ['attribute' => __('business.email')]) }}'
                        }
                    }
                });


            });
        </script>
    @endsection
