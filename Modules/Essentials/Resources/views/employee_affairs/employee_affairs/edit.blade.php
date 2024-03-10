@extends('layouts.app')

@section('title', __('essentials::lang.edit_employee'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('essentials::lang.edit_employee')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action(
                [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'update'],
                [$user->id],
            ),
            'files' => true,
            'method' => 'PUT',
            'id' => 'user_edit_form',
        ]) !!}

        <div class="col-md-12 box box-primary">
            <h4>@lang('essentials::lang.basic_info'):</h4>


            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                    {!! Form::text('first_name', $user->first_name, [
                        'class' => 'form-control',
                        'required',
                        'style' => 'height:40px',
                        'placeholder' => __('business.first_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('mid_name', __('business.mid_name') . ':') !!}
                    {!! Form::text('mid_name', $user->mid_name, [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'placeholder' => __('business.mid_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('last_name', __('business.last_name') . ':') !!}
                    {!! Form::text('last_name', $user->last_name, [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'placeholder' => __('business.last_name'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_type', __('user.user_type') . ':*') !!}
                    {!! Form::select(
                        'user_type',
                        [
                            'manager' => __('user.manager'),
                            'employee' => __('user.employee'),
                        ],
                        $user->user_type,
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

        </div>

        @include('essentials::employee_affairs.employee_affairs.edit_profile_form_part', [
            'bank_details' => !empty($user->bank_details) ? json_decode($user->bank_details, true) : null,
        ])

        @if (!empty($form_partials))
            @foreach ($form_partials as $partial)
                {!! $partial !!}
            @endforeach
        @endif

        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-big" id="submit_user_button">@lang('messages.update')</button>
            </div>
        </div>


        <div class="modal fade" id="editIdAttachementsModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog  modal-lg" role="document">

                <div class="modal-content">

                    {{-- {!! Form::open(['route' => 'updateEmployeeOfficalDocuments', 'enctype' => 'multipart/form-data']) !!} --}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.id_attachements')</h4>
                    </div>

                    <div class="modal-body">
                        <button type="button"
                            class="btn btn-success add_new_document">{{ __('essentials::lang.add_new_document') }}</button>
                        <div class="col-md-12">
                            <div class="form-group">

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="id_documents_table">
                                        <tr class="template-row" style="display: none;">
                                            <td>
                                                <div class="col-md-12">
                                                    <div class="col-md-3">
                                                        {!! Form::hidden('offical_documents_type[]', null, []) !!}
                                                        {!! Form::select(
                                                            'offical_documents_type_select[]',
                                                            [
                                                                'national_id' => __('essentials::lang.national_id'),
                                                                'passport' => __('essentials::lang.passport'),
                                                                'residence_permit' => __('essentials::lang.residence_permit'),
                                                                'drivers_license' => __('essentials::lang.drivers_license'),
                                                                'car_registration' => __('essentials::lang.car_registration'),
                                                                'international_certificate' => __('essentials::lang.international_certificate'),
                                                                // 'visa' => _('essentials::lang.visa'),
                                                            ],
                                                            null,
                                                            [
                                                                'class' => 'form-control',
                                                                'style' => 'height:40px',
                                                                'placeholder' => __('essentials::lang.select_document_type'),
                                                            ],
                                                        ) !!}
                                                    </div>

                                                    <div class="col-md-1">
                                                        <a href="#" style="height:36px"
                                                            class="btn btn-info view_document">{{ __('messages.view') }}</a>

                                                    </div>
                                                    <div class="col-md-1">

                                                        <button type="button" style="height:36px"
                                                            class="btn btn-primary edit_document">{{ __('messages.edit') }}
                                                        </button>
                                                    </div>
                                                    <div class="col-md-4 fileInputContainer" style= "display: none;">
                                                        {!! Form::hidden('offical_documents_previous_files[]', null, ['class' => 'previous-file']) !!}
                                                        {!! Form::hidden('offical_documents_choosen_files[]', null, ['class' => 'chosen-file']) !!}
                                                        {!! Form::file('offical_documents_files[]', [
                                                            'class' => 'form-control fileInput',
                                                            'style' => 'height:36px; ',
                                                        ]) !!}
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" style="height:36px"
                                                            class="btn btn-danger remove_document">{{ __('messages.delete') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">



                        {{-- <button type="submit" class="btn btn-primary" >@lang('messages.save')</button> --}}
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('essentials::lang.Tamm')</button>
                    </div>
                    {{-- {!! Form::close() !!} --}}
                </div>
            </div>
        </div>

        <div class="modal fade" data-file-path="{{ !empty($user->bank_details) ? json_decode($user->bank_details, true)['Iban_file'] ?? '' : '' }}"
            id="ibanFilePopupModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">


                    <input type="hidden" name="delete_iban_file" value="0" id="delete_iban_file_input">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.Iban_file')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row" id="ibanFilePreviewRow" style="display: none;">
                            <div class="form-group col-md-12">
                                <iframe src="" id="popupIbanFilePreview" style="width: 100%; height: 400px;"
                                    frameborder="0"></iframe>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::file('Iban_file', [
                                        'class' => 'form-control',
                                        'style' => 'height:36px; ',
                                        'accept' => '.*',
                                    ]) !!}
                                      @if(!empty(json_decode($user->bank_details, true)['Iban_file']))
                                        <input type="hidden" name="existing_iban_file" value="{{ json_decode($user->bank_details, true)['Iban_file'] }}">
                                    @endif

                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-danger deleteIbanFile">@lang('messages.delete')</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('essentials::lang.Tamm')</button>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" data-file-path="{{ $qualification->file_path ?? '' }}" id="filePopupModal" tabindex="-1"
            role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">


                    <input type="hidden" name="delete_qualification_file" value="0"
                        id="delete_qualification_file_input">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.qualification_attachements')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row" id="filePreviewRow" style="display: none;">
                            <div class="form-group col-md-12">
                                <iframe src="" id="popupFilePreview" style="width: 100%; height: 400px;"
                                    frameborder="0"></iframe>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::file('qualification_file', [
                                        'class' => 'form-control',
                                        'style' => 'height:36px; ',
                                        'accept' => '.pdf,.doc,.docx',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-danger deleteFile">@lang('messages.delete')</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('essentials::lang.Tamm')</button>
                    </div>

                </div>
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
                                    {!! Form::file('contract_file',
                                    

                                     [
                                        'class' => 'form-control',
                                        'style' => 'height:36px; ',
                                        'accept' => '.*',
                                    ]) !!}
                                     @if(!empty($contract->file_path))
                                        <input type="hidden" name="existing_contract_file" value="{{ $contract->file_path }}">
                                    @endif

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



        {!! Form::close() !!}
    @stop
    @section('javascript')
    
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
                let ibanFileChanged = false;

                $('#ibanFileLink').on('click', function(e) {
                    e.preventDefault();
                    openIbanFilePopup();
                });

                $('input[type="file"]').on('change', function(event) {
                    previewIbanFile(event);
                    ibanFileChanged = true;
                    $('#delete_iban_file_input').val('0');
                    enableSaveButton();
                });


                $('#update_iban_file_form').submit(function(e) {
                    if (!ibanFileChanged) {
                        e.preventDefault(); // Prevent form submission if no changes made
                    }
                });

                function openIbanFilePopup() {
                    const modal = $('#ibanFilePopupModal');
                    const filePath = modal.data('file-path');
                    const filePreviewIframe = $('#popupIbanFilePreview');
                    const filePreviewRow = $('#ibanFilePreviewRow');

                    if (filePath) {
                        filePreviewIframe.attr('src', '/uploads/' + filePath);
                        filePreviewRow.show(); // Show the preview row
                    } else {
                        filePreviewIframe.attr('src', '');
                        filePreviewRow.hide(); // Hide the preview row if there's no file
                    }

                    modal.modal('show');
                }



                function enableSaveButton() {
                    $('.saveFile').prop('disabled', !fileChanged);
                }

                function previewIbanFile(event) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var output = document.getElementById('popupIbanFilePreview');
                        output.src = e.target.result;
                        document.getElementById('ibanFilePreviewRow').style.display =
                            '';
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }

                $('.deleteIbanFile').on('click', function() {
                    $('#popupIbanFilePreview').attr('src', '');
                    $('input[type="file"]').val('');
                    $('#delete_iban_file_input').val('1');
                    ibanFileChanged = true;
                    enableSaveButton();
                    document.getElementById('ibanFilePreviewRow').style.display =
                        'none';
                });
            });


            // $(document).ready(function() {
            //     let contractFileChanged = false;

            //     $('#ContractFileLink').on('click', function(e) {
            //         e.preventDefault();
            //         openContractFilePopup();
            //     });

            //     $('input[type="file"]').on('change', function(event) {
            //         previewContractFile(event);
            //         contractFileChanged = true;
            //         $('#delete_contract_file_input').val('0');
            //         enableSaveButton();
            //     });


            //     $('#update_contract_file_form').submit(function(e) {
            //         if (!contractFileChanged) {
            //             e.preventDefault();
            //         }
            //     });

            //     function openContractFilePopup() {
            //         const modal = $('#ContractFilePopupModal');
            //         const filePath = modal.data('file-path');
            //         const filePreviewIframe = $('#popupContractFilePreview');
            //         const filePreviewRow = $('#contractFilePreviewRow');

            //         if (filePath) {
            //             filePreviewIframe.attr('src', '/uploads/' + filePath);
            //             filePreviewRow.show();
            //         } else {
            //             filePreviewIframe.attr('src', '');
            //             filePreviewRow.hide();
            //         }

            //         modal.modal('show');
            //     }



            //     function enableSaveButton() {
            //         $('.saveFile').prop('disabled', !contractFileChanged);
            //     }

            //     function previewContractFile(event) {
            //         var reader = new FileReader();
            //         reader.onload = function(e) {
            //             var output = document.getElementById('popupContractFilePreview');
            //             output.src = e.target.result;
            //             document.getElementById('contractFilePreviewRow').style.display =
            //                 '';
            //         };
            //         reader.readAsDataURL(event.target.files[0]);
            //     }



            //     $('.deleteContractFile').on('click', function() {
            //         $('#popupContractFilePreview').attr('src', '');
            //         $('input[type="file"]').val('');
            //         $('#delete_contract_file_input').val('1');
            //         ibanFileChanged = true;
            //         enableSaveButton();
            //         document.getElementById('contractFilePreviewRow').style.display =
            //             'none';
            //     });
            // });



            $(document).ready(function() {
                let fileChanged = false;

                $('#qualificationFileLink').on('click', function(e) {
                    e.preventDefault();
                    openFilePopup();
                });

                $('input[type="file"]').on('change', function(event) {
                    previewFile(event);
                    fileChanged = true;
                    $('#delete_qualification_file_input').val('0');
                    enableSaveButton();
                });

                function enableSaveButton() {
                    $('.saveFile').prop('disabled', !fileChanged);
                }

                $('#update_qualification_file_form').submit(function(e) {
                    if (!fileChanged) {
                        e.preventDefault(); // Prevent form submission if no changes made
                    }
                });

                function openFilePopup() {
                    const modal = $('#filePopupModal');
                    const filePath = modal.data('file-path');
                    const filePreviewIframe = $('#popupFilePreview');
                    const filePreviewRow = $('#filePreviewRow');

                    if (filePath) {
                        filePreviewIframe.attr('src', '/uploads/' + filePath);
                        filePreviewRow.show(); // Show the preview row
                    } else {
                        filePreviewIframe.attr('src', '');
                        filePreviewRow.hide(); // Hide the preview row if there's no file
                    }

                    modal.modal('show');
                }

                function previewFile(event) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var output = document.getElementById('popupFilePreview');
                        output.src = e.target.result;
                        document.getElementById('filePreviewRow').style.display =
                            ''; // Show the row by clearing the display style
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }

                $('.deleteFile').on('click', function() {
                    $('#popupFilePreview').attr('src', ''); // Remove iframe source
                    $('input[type="file"]').val(''); // Clear file input
                    $('#delete_qualification_file_input').val('1'); // Indicate that the file should be deleted
                    fileChanged = true;
                    enableSaveButton();
                    document.getElementById('filePreviewRow').style.display =
                        'none'; // Hide the row by setting display to none
                });
            });






            $(document).ready(function() {
                $('.add_new_document').on('click', function() {
                    var newRow = $('.template-row').first().clone();
                    newRow.show();
                    newRow.find('input, select').val('');
                    newRow.find('select').prop('disabled', false);
                    $('#id_documents_table').append(newRow);
                    var fileInputContainer = newRow.find('.fileInputContainer');
                    fileInputContainer.toggle();
                    if (!fileInputContainer.is(':visible')) {
                        fileInputContainer.find('.fileInput').val('');
                    }
                    newRow.find('.view_document').prop('disabled', true);
                });
                $('#id_documents_table').on('click', '.remove_document', function() {
                    var row = $(this).closest('tr');
                    if (row.data('initial-row')) {
                        var documentId = row.data('document-id');
                        if (documentId) {
                            var hiddenInput = $('<input>').attr({
                                type: 'hidden',
                                name: 'deleted_documents[]',
                                value: documentId
                            });
                            $('form').append(hiddenInput);
                        }
                        row.hide();
                        row.find('input, select').prop('disabled', true);
                    } else {
                        row.remove();
                    }
                });
                $('#id_documents_table').on('click', '.edit_document', function() {
                    var fileInputContainer = $(this).closest('tr').find('.fileInputContainer');
                    fileInputContainer.toggle();
                    if (!fileInputContainer.is(':visible')) {
                        fileInputContainer.find('.fileInput').val('');
                    }
                });
                $(document).on('click', '.id_attachements_btn', function(e) {
                    e.preventDefault();
                    $('#editIdAttachementsModal').modal('show');
                });

                var documents = @json($officalDocuments);
                $('#id_documents_table tbody').find('tr').not('.template-row').remove();
                documents.forEach(function(doc) {
                    var newRow = $('.template-row').first().clone();
                    newRow.show();
                    newRow.find('select[name="offical_documents_type_select[]"]').val(doc.type);
                    newRow.find('select[name="offical_documents_type_select[]"]').prop('disabled', true);
                    newRow.find('input[name="offical_documents_type[]"]').val(doc.type);

                    var viewButton = newRow.find('.view_document');

                    if (doc.file_path) {

                        viewButton.attr('href', '/uploads/' + doc.file_path);
                        viewButton.attr('target', '_blank');
                    } else {

                        viewButton.removeAttr('href');
                        viewButton.css('pointer-events', 'none');
                        viewButton.removeClass('btn-info').addClass('btn-secondary');

                        viewButton.on('click', function(e) {
                            e.preventDefault(); // Prevent the link from being followed
                        });
                    }

                    newRow.data('document-id', doc.id);
                    newRow.attr('data-initial-row', 'true');
                    newRow.find('input[name="offical_documents_previous_files[]"]').val(doc.id);
                    $('#id_documents_table').append(newRow);
                });

                $('#id_documents_table').on('change', '.fileInput', function() {
                    var fileName = $(this).val().split('\\').pop();
                    $(this).closest('tr').find('input[name="offical_documents_choosen_files[]"]').val(fileName);
                });
                $('#id_documents_table').on('change', 'select[name="offical_documents_type_select[]"]', function() {
                    var parentRow = $(this).closest('tr');
                    var hiddenInput = parentRow.find('input[name="offical_documents_type[]"]');
                    hiddenInput.val($(this).val());
                });
            });


            $(document).ready(function() {
                __page_leave_confirmation('#user_edit_form');

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


            });
        </script>



    @endsection
