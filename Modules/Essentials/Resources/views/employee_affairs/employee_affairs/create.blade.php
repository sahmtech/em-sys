@extends('layouts.app')

@section('title', __('essentials::lang.add_new_employee'))

@section('content')
<head>
    <style>
    #video {
        transform: scaleX(-1); /* Flip the video horizontally */
    }
</style>
</head>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.add_new_employee')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open(['route' => 'storeEmployee', 'enctype' => 'multipart/form-data']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12 box box-primary">
                    <h4>@lang('essentials::lang.basic_info'):</h4>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                            {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('business.first_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                            {!! Form::text('mid_name', null, [
                                'class' => 'form-control',
                                
                                'placeholder' => __('business.mid_name'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('last_name', __('business.last_name') . ':') !!}
                            {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('email', __('business.email') . ':') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('emp_number', __('essentials::lang.emp_number') . ':') !!}
                            {!! Form::text('emp_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.emp_number')]) !!}
                        </div>
                    </div>

                     <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('user_type', __('user.user_type') . ':*') !!}
                            {!! Form::select(
                                'user_type',
                                [
                                    'manager' => __('user.manager'),
                                    'employee' => __('user.employee'),
                                   
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

             
               <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('profile_picture', __('user.profile_picture') . ':') !!}
                        {!! Form::file('profile_picture', ['class' => 'form-control', 'id' => 'fileInputWrapper', 'accept' => 'image/*']) !!}
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="captureButton">@lang('essentials::lang.capture_photo')</button>
                    </div>

                    <!-- Hidden file input to store the captured photo -->
               
                </div>
                      @include('essentials::employee_affairs.employee_affairs.popup_camera_modal')
                   

                  
                </div>

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

                    <div class="modal fade" data-file-path="{{ $contract->file_path ?? '' }}" id="ContractFilePopupModal"
                                tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">


                                        <input type="hidden" name="delete_contract_file" value="0" id="delete_contract_file_input">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">@lang('essentials::lang.contract_file')</h4>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row" id="contractFilePreviewRow" style="display: none;">
                                                <div class="form-group col-md-12">
                                                    <iframe src="" id="popupContractFilePreview" style="width: 100%; height: 400px;"
                                                        frameborder="0"></iframe>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        {!! Form::file('contract_file', [
                                                            'class' => 'form-control',
                                                            'style' => 'height:40px; ',
                                                            'accept' => '.*',
                                                        ]) !!}

                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button"
                                                        class="btn btn-danger deleteContractFile">@lang('messages.delete')</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('essentials::lang.Tamm')</button>
                                        </div>

                                    </div>
                                </div>
                            </div>



            </div>


        </div>
        {!! Form::close() !!}
    </section>
@stop

@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('.hijri-date-picker').on('change', function() {
                var hijriDate = $(this).val();


                if (hijriDate) {

                    var gregorianDate = HijriDate.toGregorian(hijriDate, 'YYYY-MM-DD');


                    $('#user_dob').val(gregorianDate);
                } else {

                    $('#user_dob').val('');
                }
            });
        });
    </script>

    <script>
          $(document).ready(function() {
                let contractFileChanged = false;

                $('#ContractFileLink').on('click', function(e) {
                    e.preventDefault();
                    openContractFilePopup();
                });

                $('input[type="file"]').on('change', function(event) {
                    previewContractFile(event);
                    contractFileChanged = true;
                    $('#delete_contract_file_input').val('0');
                    enableSaveButton();
                });


                $('#update_contract_file_form').submit(function(e) {
                    if (!contractFileChanged) {
                        e.preventDefault();
                    }
                });

                function openContractFilePopup() {
                    const modal = $('#ContractFilePopupModal');
                    const filePath = modal.data('file-path');
                    const filePreviewIframe = $('#popupContractFilePreview');
                    const filePreviewRow = $('#contractFilePreviewRow');

                    if (filePath) {
                        filePreviewIframe.attr('src', '/uploads/' + filePath);
                        filePreviewRow.show();
                    } else {
                        filePreviewIframe.attr('src', '');
                        filePreviewRow.hide();
                    }

                    modal.modal('show');
                }



                function enableSaveButton() {
                    $('.saveFile').prop('disabled', !contractFileChanged);
                }

                function previewContractFile(event) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var output = document.getElementById('popupContractFilePreview');
                        output.src = e.target.result;
                        document.getElementById('contractFilePreviewRow').style.display =
                            '';
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }

                $('.deleteContractFile').on('click', function() {
                    $('#popupContractFilePreview').attr('src', '');
                    $('input[type="file"]').val('');
                    $('#delete_contract_file_input').val('1');
                    ibanFileChanged = true;
                    enableSaveButton();
                    document.getElementById('contractFilePreviewRow').style.display =
                        'none';
                });
            });

    </script>

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
                            q: params.term,
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
