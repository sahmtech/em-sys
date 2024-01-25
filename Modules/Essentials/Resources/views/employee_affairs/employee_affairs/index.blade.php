@extends('layouts.app')
@section('title', __('essentials::lang.employees'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('essentials::lang.manage_employees')
        </h1>
        <!-- <ol class="breadcrumb">
                                                                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                                                    <li class="active">Here</li>
                                                                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="business_filter">@lang('essentials::lang.business_single'):</label>
                    {!! Form::select('select_company_id', $companies, null, [
                        'class' => 'form-control select2',
                        'id' => 'select_company_id',
                        'style' => 'height:36px; width:100%',
                        'placeholder' => __('lang_v1.all'),
                        'required',
                        'autofocus',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.job_title'):</label>
                    {!! Form::select('specializations-select', $job_titles, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px; width:100%',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="nationalities_filter">@lang('essentials::lang.nationality'):</label>
                    {!! Form::select('nationalities_select', $nationalities, request('nationalities_select'), [
                        'class' => 'form-control select2',
                        'placeholder' => __('lang_v1.all'),
                        'style' => 'height:36px; width:100%',
                        'id' => 'nationalities_select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('essentials::lang.status'):</label>
                    <select class="form-control select2" name="status_filter" required id="status_filter"
                        style="height:36px; width:100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="active">@lang('sales::lang.active')</option>
                        <option value="inactive">@lang('sales::lang.inactive')</option>
                        <option value="terminated">@lang('sales::lang.terminated')</option>
                        <option value="vecation">@lang('sales::lang.vecation')</option>


                    </select>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-3">

                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{ route('createEmployee') }}">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </a>
                        </div>
                    @endslot

                </div>


            </div>



            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('essentials::lang.profile_image')</th>
                            <th>@lang('essentials::lang.employee_number')</th>
                            <th>@lang('essentials::lang.company')</th>
                            <th>@lang('essentials::lang.employee_name')</th>

                            <th>@lang('essentials::lang.Identity_proof_id')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.total_salary')</th>
                            <th>@lang('essentials::lang.admissions_date')</th>
                            <th>@lang('essentials::lang.contract_end_date')</th>

                            <th>@lang('essentials::lang.department')</th>
                            <th>@lang('essentials::lang.job_title')</th>

                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('essentials::lang.status')</th>
                            <th>@lang('messages.view')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="addQualificationModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeQualification', 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_qualification')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee', [], null, [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'placeholder' => __('essentials::lang.select_employee'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                                {!! Form::select(
                                    'qualification_type',
                                    [
                                        'bachelors' => __('essentials::lang.bachelors'),
                                        'master' => __('essentials::lang.master'),
                                        'PhD' => __('essentials::lang.PhD'),
                                        'diploma' => __('essentials::lang.diploma'),
                                    ],
                                    null,
                                    ['class' => 'form-control', 'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('general_specialization', __('essentials::lang.general_specialization') . ':') !!}
                                {!! Form::select('general_specialization', $specializations, null, [
                                    'class' => 'form-control',
                                    'style' => 'height:36px',
                                    'id' => 'professionSelect',
                                    'placeholder' => __('essentials::lang.select_specialization'),
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('sub_specialization', __('essentials::lang.sub_specialization') . ':') !!}
                                {!! Form::select('sub_specialization', [], null, [
                                    'class' => 'form-control',
                                    'style' => 'height:36px',
                                    'id' => 'specializationSelect',
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                                {!! Form::date('graduation_year', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.graduation_year'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="clearfix"></div>

                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                                {!! Form::text('graduation_institution', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.graduation_institution'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                                {!! Form::select('graduation_country', $countries, null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.select_country'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                    {!! Form::number('degree', !empty($qualification->degree) ? $qualification->degree : null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.degree'),
                                        'step' => 'any',
                                        'onkeyup' => 'getGPA()',
                                    ]) !!}
                                </div>
                            </div>

                            <div class=" col-md-6">
                                <div class="form-group">
                                    {!! Form::label('great_degree', __('essentials::lang.great_degree') . ':') !!}
                                    {!! Form::number('great_degree', !empty($qualification->great_degree) ? $qualification->great_degree : null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.great_degree'),
                                        'step' => 'any',
                                        'onkeyup' => 'getGPA()',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class=" col-md-6">
                                <div class="form-group">
                                    {!! Form::label('marksName', __('essentials::lang.marksName') . ':') !!}
                                    {!! Form::text('marksName', !empty($qualification->marksName) ? $qualification->marksName : null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.marksName'),
                                        'step' => 'any',
                                        'readonly',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>




        <div class="modal fade" id="add_doc" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeOfficialDoc', 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_Doc')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employees2', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employees2', [], null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.select_employee'),
                                    'required',
                                    'style' => 'height:40px',
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
                                        'Iban' => __('essentials::lang.Iban'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.select_type'),
                                        'required',
                                    ],
                                ) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':*') !!}
                                {!! Form::number('doc_number', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.doc_number'),
                                    'required',
                                    'style' => 'height:40px',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':*') !!}
                                {!! Form::date('issue_date', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.issue_date'),
                                    'required',
                                    'style' => 'height:40px',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':*') !!}
                                {!! Form::text('issue_place', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.issue_place'),
                                    'required',
                                    'style' => 'height:40px',
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
                                    'placeholder' => __('essentials::lang.expiration_date'),
                                    'required',
                                    'style' => 'height:40px',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                                {!! Form::file('file', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.file'),
                                    'required',
                                    'style' => 'height:40px',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>


        <div class="modal fade" id="addContractModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {{-- --}}
                    {!! Form::open(['route' => 'storeContract', 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <div class="form-group col-md-6">
                                {!! Form::label('offer_price', __('sales::lang.offer_price') . ':') !!}
                                {!! Form::select('offer_price', $offer_prices, null, [
                                    'class' => 'form-control',
                                    'id' => 'offer_price',
                                    'placeholder' => __('sales::lang.select_offer_price'),
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('contract_signer', __('sales::lang.contract_signer') . ':*') !!}
                                {!! Form::text('contract_signer', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.contract_signer'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('contract_follower', __('sales::lang.contract_follower') . ':*') !!}
                                {!! Form::text('contract_follower', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.contract_follower'),
                                    'required',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('start_date', __('essentials::lang.contract_start_date') . ':*') !!}
                                {!! Form::date('start_date', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.contract_start_date'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('end_date', __('essentials::lang.contract_end_date') . ':*') !!}
                                {!! Form::date('end_date', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.contract_end_date'),
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                                {!! Form::select(
                                    'status',
                                    ['valid' => __('sales::lang.valid'), 'finished' => __('sales::lang.finished')],
                                    null,
                                    ['class' => 'form-control', 'placeholder' => __('essentials::lang.status'), 'required'],
                                ) !!}
                            </div>
                            <div class="form-group col-md-8">
                                {!! Form::label('contract_items', __('sales::lang.contract_items') . ':*') !!}
                                {!! Form::select('contract_items[]', $items, null, [
                                    'class' => 'form-control select2',
                                    'multiple' => 'multiple',
                                    'placeholder' => __('sales::lang.select_contract_items'),
                                    'required',
                                ]) !!}
                            </div>


                            <div class="form-group col-md-6">
                                {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                                {!! Form::select(
                                    'is_renewable',
                                    ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')],
                                    null,
                                    ['class' => 'form-control'],
                                ) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file')]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('sales::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('sales::lang.notes'),
                                    'rows' => 2,
                                ]) !!}
                            </div>


                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>



    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).on('click', '.btn-modal1', function(e) {
            e.preventDefault();
            var userId = $(this).data('row-id');
            var userName = $(this).data('row-name');

            $('#addQualificationModal').modal('show');


            $('#employee').empty();
            $('#employee').append('<option value="' + userId + '">' + userName + '</option>');
        });
    </script>


    <script type="text/javascript">
        $(document).on('click', '.btn-modal2', function(e) {
            e.preventDefault();
            var userId = $(this).data('row-id');
            var userName = $(this).data('row-name');

            $('#add_doc').modal('show');


            $('#employees2').empty();
            $('#employees2').append('<option value="' + userId + '">' + userName + '</option>');
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            var users_table = $('#employees').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employees') }}",
                    data: function(d) {
                        d.specialization = $('#specializations-select').val();
                        d.nationality = $('#nationalities_select').val();
                        d.status = $('#status_filter').val();
                        d.company = $('#select_company_id').val();

                        console.log(d);
                    },
                },


                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "profile_image",
                        "render": function(data, type, row) {
                            if (data) {

                                var imageUrl = '/uploads/' + data;
                                return '<img src="' + imageUrl +
                                    '" alt="Profile Image" class="img-thumbnail" width="50" height="50" style=" border-radius: 50%;">';
                            } else {
                                return '@lang('essentials::lang.no_image')';
                            }
                        }
                    },
                    {
                        "data": "emp_number"
                    },
                    {
                        "data": "company_id"
                    },

                    {
                        "data": "full_name"
                    },

                    {
                        "data": "id_proof_number"
                    },
                    {
                        "data": "nationality"
                    },
                    {
                        "data": "total_salary"
                    },
                    {
                        "data": "admissions_date"
                    },
                    {
                        "data": "contract_end_date"
                    },

                    {
                        "data": "essentials_department_id"
                    },
                    {
                        "data": "profession",
                        name: 'profession'
                    },

                    {
                        "data": "contact_number"
                    },
                    {
                        "data": "email"
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'active') {
                                return '@lang('essentials::lang.active')';
                            } else if (data === 'vecation') {
                                return '@lang('essentials::lang.vecation')';
                            } else if (data === 'inactive') {
                                return '@lang('essentials::lang.inactive')';
                            } else if (data === 'terminated') {
                                return '@lang('essentials::lang.terminated')';
                            } else {
                                return ' ';
                            }
                        }
                    },
                    {
                        "data": "view"
                    },
                    {
                        "data": "action"
                    }
                ],
                "createdRow": function(row, data, dataIndex) {
                    var contractEndDate = data.contract_end_date;
                    console.log(contractEndDate);
                    var currentDate = moment().format("YYYY-MM-DD");

                    if (contractEndDate !== null && contractEndDate !== undefined) {
                        var daysRemaining = moment(contractEndDate).diff(currentDate, 'days');

                        if (daysRemaining <= 0) {
                            $('td', row).eq(8).addClass('text-danger');
                        } else if (daysRemaining <= 25) {
                            $('td', row).eq(8).addClass(
                                'text-warning');
                        }
                    }
                }

            });



            $('#employees tbody').on('click', 'tr', function(e) {
                var cellIndex = $(e.target).closest('td').index();
                var lastIndex = $(this).children('td').length - 1;

                if (cellIndex !== lastIndex) {
                    var data = users_table.row(this).data();
                    console.log(data);
                    if (data) {
                        window.location = '{{ route('showEmployee', ['id' => ':id']) }}'.replace(':id', data
                            .id);
                    }
                }

            });

            $('#specializations-select, #nationalities_select, #status-select, #select_company_id').change(
                function() {
                    console.log('Specialization selected: ' + $(this).val());
                    console.log('Nationality selected: ' + $('#nationalities_select').val());
                    console.log('Status selected: ' + $('#status_filter').val());
                    console.log('loc selected: ' + $('#select_company_id').val());
                    users_table.ajax.reload();

                });







            $(document).on('click', 'button.delete_user_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_user,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    users_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });



            var professionSelect = $('#professionSelect');
            var specializationSelect = $('#specializationSelect');


            professionSelect.on('change', function() {
                var selectedProfession = $(this).val();
                console.log(selectedProfession);
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '{{ route('specializations') }}',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        profession_id: selectedProfession
                    },
                    success: function(data) {
                        specializationSelect.empty();
                        $.each(data, function(id, name) {
                            specializationSelect.append($('<option>', {
                                value: id,
                                text: name
                            }));
                        });
                    }
                });
            });


        });

        function getGPA() {
            const GPA = [{
                    PercentageTo: 100,
                    PercentageFrom: 85,
                    marksName: '{{ __('essentials::lang.veryExcellent') }}',
                    Grade: "A+",
                },
                {
                    PercentageTo: 84,
                    PercentageFrom: 80,
                    marksName: '{{ __('essentials::lang.excellent') }}',
                    Grade: "A",
                },
                {
                    PercentageTo: 79,
                    PercentageFrom: 75,
                    marksName: '{{ __('essentials::lang.veryGood') }}',
                    Grade: "B+",
                },
                {
                    PercentageTo: 74,
                    PercentageFrom: 70,
                    marksName: '{{ __('essentials::lang.veryGood') }}',
                    Grade: "B",
                },
                {
                    PercentageTo: 69,
                    PercentageFrom: 65,
                    marksName: '{{ __('essentials::lang.good') }}',
                    Grade: "B-",
                },
                {
                    PercentageTo: 64,
                    PercentageFrom: 60,
                    marksName: '{{ __('essentials::lang.good') }}',
                    Grade: "C+",
                },
                {
                    PercentageTo: 59,
                    PercentageFrom: 55,
                    marksName: '{{ __('essentials::lang.weak') }}',
                    Grade: "C",
                },
                {
                    PercentageTo: 54,
                    PercentageFrom: 50,
                    marksName: '{{ __('essentials::lang.weak') }}',
                    Grade: "C-",
                },
                {
                    PercentageTo: 49,
                    PercentageFrom: 45,
                    marksName: '{{ __('essentials::lang.bad') }}',
                    Grade: "D",
                },
                {
                    PercentageTo: 44,
                    PercentageFrom: 40,
                    marksName: '{{ __('essentials::lang.bad') }}',
                    Grade: "D-",
                },
                {
                    PercentageTo: 39,
                    PercentageFrom: 0,
                    marksName: '{{ __('essentials::lang.fail') }}',
                    Grade: "F",
                },
            ];
            var great_degree = document.getElementById('great_degree').value;
            var degree = document.getElementById('degree').value;

            if (degree > great_degree) {
                document.getElementById("marksName").style.color = "red";
                document.getElementById('marksName').value = 'يجب ان تكون الدرجة العطمة اعلى من الدرجة';
            }
            var greatDegree = 100 / great_degree;
            GPA.forEach(gpaMark => {
                if (degree >= gpaMark.PercentageFrom / greatDegree && degree <= gpaMark.PercentageTo /
                    greatDegree) {

                    document.getElementById('marksName').value = gpaMark.marksName +
                        '  ( ' + gpaMark.Grade + ' )'
                }

            });


        }
    </script>


@endsection
