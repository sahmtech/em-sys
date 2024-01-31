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
                                                        {!! Form::select(
                                                            'offical_documents_type[]',
                                                            [
                                                                'national_id' => __('essentials::lang.national_id'),
                                                                'passport' => __('essentials::lang.passport'),
                                                                'residence_permit' => __('essentials::lang.residence_permit'),
                                                                'drivers_license' => __('essentials::lang.drivers_license'),
                                                                'car_registration' => __('essentials::lang.car_registration'),
                                                                'international_certificate' => __('essentials::lang.international_certificate'),
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

                        {{-- <div class="row">
                            {!! Form::hidden('delete_file', '0', ['id' => 'delete_file_input']) !!}
                            {!! Form::hidden('doc_id', null, ['id' => 'doc_id']) !!}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-md-2 d-flex align-items-center" style="height:36px">
                                        {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                    </div>
                                    <div class="col-md-6 row">
                                        {!! Form::file('file', [
                                            'class' => 'form-control ',
                                            'style' => 'height:36px',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" style="height:36px" class="btn btn-danger deleteFile">@lang('messages.delete')</button>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                    <div class="modal-footer">



                        {{-- <button type="submit" class="btn btn-primary" >@lang('messages.save')</button> --}}
                        <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('essentials::lang.Tamm')</button>
                    </div>
                    {{-- {!! Form::close() !!} --}}
                </div>
            </div>
        </div>






        {!! Form::close() !!}
    @stop
    @section('javascript')
        <script type="text/javascript">
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
                    newRow.find('select[name="offical_documents_type[]"]').val(doc.type);
                    var viewButton = newRow.find('.view_document');
                    viewButton.attr('href', '/uploads/' + doc.file_path);
                    viewButton.attr('target', '_blank');
                    newRow.data('document-id', doc.id);
                    newRow.attr('data-initial-row', 'true');
                    newRow.find('input[name="offical_documents_previous_files[]"]').val(doc.id);
                    $('#id_documents_table').append(newRow);
                });
                $('#id_documents_table').on('change', '.fileInput', function() {
                    var fileName = $(this).val().split('\\').pop();
                    $(this).closest('tr').find('input[name="offical_documents_choosen_files[]"]').val(fileName);
                });





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
