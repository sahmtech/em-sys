@extends('layouts.app')
@section('title', __('essentials::lang.qualifications'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.qualifications')</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('qualification_type_filter', __('essentials::lang.qualification_type') . ':') !!}
                            {!! Form::select(
                                'qualification_type_filter',
                                [
                                    'bachelors' => __('essentials::lang.bachelors'),
                                    'master' => __('essentials::lang.master'),
                                    'PhD' => __('essentials::lang.PhD'),
                            
                                    'diploma' => __('essentials::lang.diploma'),
                                    'minor' => __('essentials::lang.minor'),
                                    'sponsorship' => __('essentials::lang.sponsorship'),
                                ],
                                null,
                                [
                                    'class' => 'form-control',
                                    'id' => 'qualification_type_filter',
                                    'style' => 'width:100%;height:40px',
                                    'placeholder' => __('lang_v1.all'),
                                ],
                            ) !!}
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('major_filter', __('essentials::lang.major') . ':') !!}
                            {!! Form::select('major_filter', $spacializations, null, [
                                'class' => 'form-control',
                                'id' => 'major_filter',
                                'style' => 'width:100%;height:40px',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addQualificationModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="qualifications_table">
                            <thead>
                                <tr>
                                    <th class='table-td-width-60px'>@lang('essentials::lang.employee')</th>
                                    <th class='table-td-width-100px'>@lang('essentials::lang.qualification_type')</th>
                                    {{-- <th class='table-td-width-60px'>@lang('essentials::lang.major')</th> --}}
                                    <th class='table-td-width-60px'>@lang('essentials::lang.general_specialization')</th>
                                    <th class='table-td-width-60px'>@lang('essentials::lang.sub_specialization')</th>
                                    <th class='table-td-width-60px'>@lang('essentials::lang.graduation_year')</th>
                                    <th class='table-td-width-80px'>@lang('essentials::lang.graduation_institution')</th>
                                    <th class='table-td-width-60px'>@lang('essentials::lang.graduation_country')</th>
                                    <th class='table-td-width-40px'>@lang('essentials::lang.degree')</th>
                                    <th class='table-td-width-100px'>@lang('essentials::lang.great_degree')</th>
                                    <th class='table-td-width-60px'>@lang('essentials::lang.marksName')</th>
                                    <th class='table-td-width-100px'>@lang('essentials::lang.qualification_file')</th>
                                    <th class='table-td-width-100px'>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
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
                                    {!! Form::select('employee', $users, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width:100%;height:40px',
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
                                            'minor' => __('essentials::lang.minor'),
                                            'sponsorship' => __('essentials::lang.sponsorship'),
                                        ],
                                        null,
                                        ['class' => 'form-control', 'required', 'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('general_specialization', __('essentials::lang.general_specialization') . ':') !!}
                                    {!! Form::select('general_specialization', $spacializations, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'id' => 'professionSelect',
                                        'placeholder' => __('essentials::lang.select_specialization'),
                                    ]) !!}
                                </div>


                                <div class="form-group col-md-6">
                                    {!! Form::label('sub_specialization', __('essentials::lang.sub_specialization') . ':') !!}
                                    {!! Form::select('sub_specialization', [], null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'id' => 'specializationSelect',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':*') !!}
                                    {!! Form::date('graduation_year', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.graduation_year'),
                                        'required',
                                    ]) !!}
                                </div>
                                <div class="clearfix"></div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':*') !!}
                                    {!! Form::text('graduation_institution', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.graduation_institution'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':*') !!}
                                    {!! Form::select('graduation_country', $countries, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.select_country'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class=" col-md-6">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('essentials::lang.qualification_file', __('essentials::lang.qualification_file') . ':') !!}
                                        {!! Form::file('qualification_file', [
                                            'class' => 'form-control',
                                            'style' => 'height:40px',
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


            @include('essentials::employee_affairs.employees_qualifications.add_qualification_file_modal')
        </div>
    </section>
    @include('essentials::employee_affairs.employees_qualifications.edit')
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {


            $('#major_filter_select').select2();
            $('#addQualificationModal').on('shown.bs.modal', function(e) {
                $('#employee').select2({
                    dropdownParent: $(
                        '#addQualificationModal'),
                    width: '100%',
                });

                $('#spacializations_select').select2({
                    dropdownParent: $(
                        '#addQualificationModal'),
                    width: '100%',
                });

                $('#select_country').select2({
                    dropdownParent: $(
                        '#addQualificationModal'),
                    width: '100%',
                });
            });


            //----------------------------------------------------------------------------------
            $(document).on('click', '.view_qualification_file_modal', function(e) {
                e.preventDefault();


                var fileUrl = $(this).data('href') ?? null;
                var doc_id = $(this).data('id');
                $('#doc_id').val(doc_id);
                if (fileUrl != null) {
                    console.log(fileUrl);
                    $('#iframeQualDocViewer').attr('src', fileUrl);
                    $('#iframeQualDocViewer').show();

                } else {

                    $('#iframeQualDocViewer').hide();

                }
                $('#addQualificationFileModal').modal('show');
            });

            $('#addQualificationFileModal').on('hidden.bs.modal', function() {
                $('#iframeQualDocViewer').attr('src', '');
            });

            let fileChanged = false;
            $('.deleteFile').on('click', function() {
                $('#iframeQualDocViewer').attr('src', '');
                $('input[type="file"]').val('');
                $('#delete_file_input').val('1');
                $('#iframeQualDocViewer').hide();
                fileChanged = true;
                enableSaveButton();
            });

            function enableSaveButton() {
                $('.saveFile').prop('disabled', !fileChanged);
            }

            $('input[type="file"]').on('change', function(event) {
                var file = event.target.files[0];

                if (file) {
                    var fileType = file.type;
                    var url = '';


                    if (fileType.match(/image.*/)) {

                        url = URL.createObjectURL(file);
                    } else if (fileType === 'application/pdf') {

                        url = URL.createObjectURL(file);
                    } else {

                        alert('File type not supported for preview');
                        return;
                    }


                    $('#iframeQualDocViewer').attr('src', url).show();
                } else {

                }
                fileChanged = true;
                enableSaveButton();
            });

            //-----------------------------------------------------------------------------
            var qualifications_table;

            function reloadDataTable() {
                qualifications_table.ajax.reload();
            }

            qualifications_table = $('#qualifications_table').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('qualifications') }}",
                    data: function(d) {
                        if ($('#qualification_type_filter').length) {
                            d.qualification_type = $('#qualification_type_filter').val();
                        }
                        if ($('#major_filter').length) {
                            d.major = $('#major_filter').val();

                        }
                    }
                },

                columns: [{
                        data: 'user'
                    },

                    {
                        data: 'qualification_type',
                        render: function(data, type, row) {
                            if (data === 'bachelors') {
                                return '@lang('essentials::lang.bachelors')';
                            } else if (data === 'master') {
                                return '@lang('essentials::lang.master')';
                            } else if (data === 'PhD') {
                                return '@lang('essentials::lang.PhD')';
                            } else if (data === 'diploma') {
                                return '@lang('essentials::lang.diploma')';
                            } else if (data === 'minor') {
                                return '@lang('essentials::lang.minor')';
                            } else if (data === 'sponsorship') {
                                return '@lang('essentials::lang.sponsorship')';
                            } else {
                                return data;
                            }

                        }
                    },

                    {
                        data: 'specialization'
                    },
                    {
                        data: 'sub_specialization'
                    }, {
                        data: 'graduation_year'
                    },
                    {
                        data: 'graduation_institution'
                    },
                    {
                        data: 'graduation_country'
                    },
                    {
                        data: 'degree'
                    },
                    {
                        data: 'great_degree'
                    },
                    {
                        data: 'marksName'
                    },
                    {
                        data: 'qualification_file'
                    },
                    {
                        data: 'action'
                    },
                ],
            });


            $(document).on('change', '#qualification_type_filter, #major_filter', function() {
                console.log($('#qualification_type_filter').val());
                console.log($('#major_filter').val());
                reloadDataTable();
            });


            $(document).on('click', 'button.delete_qualification_button', function() {

                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_qualification,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",

                            success: function(result) {

                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    qualifications_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });


            $(document).on('click', '.open-edit-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var doc_id = $(this).data('id');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var qualification = response.qualification;
                        console.log(qualification);
                        $('#editQualificationModal').find('[name="id"]').val(
                            qualification
                            .id);
                        $('#editQualificationModal').find('[name="employee"]')
                            .val(
                                qualification
                                .employee_id);
                        $('#editQualificationModal').find('[name="qualification_type"]')
                            .val(
                                qualification
                                .qualification_type);
                        $('#editQualificationModal').find('[name="general_specialization"]')
                            .val(
                                qualification
                                .specialization);
                        $('#editQualificationModal').find('[name="sub_specialization"]')
                            .val(
                                qualification
                                .sub_specialization);
                        $('#editQualificationModal').find('[name="graduation_institution"]')
                            .val(
                                qualification
                                .graduation_institution);
                        $('#editQualificationModal').find('[name="graduation_year"]')
                            .val(
                                qualification
                                .graduation_year);
                        $('#editQualificationModal').find(
                                '[name="graduation_country"]')
                            .val(
                                qualification
                                .graduation_country);
                        $('#editQualificationModal').find('[name="_degree"]')
                            .val(
                                qualification
                                .degree);
                        $('#editQualificationModal').find('[name="_great_degree"]')
                            .val(
                                qualification
                                .great_degree);
                        $('#editQualificationModal').find('[name="_marksName"]')
                            .val(
                                qualification
                                .marksName);

                        $('#editQualificationModal').modal('show');
                    },
                    error: function(xhr, status, error) {

                        console.error("Error in AJAX request:", error);
                    }
                });


            })

        });

        $(document).on('click', '#printDocButton', function() {
            var iframe = document.getElementById('iframeDocViewer');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } else {
                alert('No document to print');
            }
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

    <script type="text/javascript">
        $(document).ready(function() {


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
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            console.log('done');

            var professionSelect = $('#editprofessionSelect');
            console.log(professionSelect);
            var specializationSelect = $('#editspecializationSelect');


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
    </script>

@endsection
