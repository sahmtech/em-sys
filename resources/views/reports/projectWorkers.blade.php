@extends('layouts.app')
@section('title', __('essentials::lang.projects_workers'))

@section('content')

    {{-- <section class="cont
    ent-header">

    </section> --}}


    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @else
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    @endif
    <section class="content">


        <div class="modal-header">
         
        <h1>@lang('essentials::lang.projects_workers')
        </h1>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                        @php

                            $default_fields = [
                                $fields[0],
                                $fields[1],
                                $fields[2],
                                $fields[3],
                                $fields[4],
                                $fields[5],
                                $fields[6],
                            ];

                            $default = array_keys($default_fields);

                        @endphp

                        <div style="row">
                            <div class="col-md-11">
                                <div class="form-group">
                                    {!! Form::label('choose_fields', __('followup::lang.choose_fields') . ' ') !!}
                                    {!! Form::select('choose_fields_select[]', $fields, $default, [
                                        'class' => 'form-control select2',
                                        'multiple',
                                        'id' => 'choose_fields_select',
                                    ]) !!}
                                </div>

                            </div>

                            <div class="col-md-1 ">
                                <button class="btn btn-primary pull-right btn-flat" onclick="chooseFields();"
                                    style="margin-top: 24px;
                                width: 62px;
                                height: 40px;
                                border-radius: 4px;">تطبيق</button>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="workers_table"
                                style=" table-layout: fixed !important;
                           ">
                                <thead>
                                    <tr>

                                        <td>#</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.profile_image')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.employee_number')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.name')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.eqama')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.company_name')</td>


                                        <td class="table-td-width-100px">@lang('followup::lang.passport_numer')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.passport_expire_date')</td>


                                        <td class="table-td-width-100px">@lang('essentials::lang.border_number')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.dob')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.insurance')</td>

                                        <td class="table-td-width-100px">@lang('followup::lang.project_name')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.nationality')</td>



                                        <td class="table-td-width-100px">@lang('followup::lang.eqama_end_date')</td>

                                        <td class="table-td-width-100px">@lang('followup::lang.admissions_date')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.admissions_type')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.admissions_status')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.contract_end_date')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.mobile_number')</td>
                                        <td class="table-td-width-100px">@lang('business.email')</td>

                                        <td class="table-td-width-100px">@lang('followup::lang.profession')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.status')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.Basic_salary')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.total_salary')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.gender')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.marital_status')</td>
                                        <td class="table-td-width-100px">@lang('followup::lang.blood_group')</td>

                                        <td class="table-td-width-100px">@lang('followup::lang.bank_code')</td>
                                        <td class="table-td-width-100px">@lang('essentials::lang.travel_categorie')</td>





                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent
                </div>







            </div>
        </div>



        {!! Form::close() !!}
    </section>

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            // $('#workers_table').DataTable({

            // });

            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {

                    url: "{{ route('project_workers') }}",

                  
                },


                columns: [

                    {
                        "data": "worker_id"
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
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' + '{{ route('showWorker', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },

                    {
                        data: 'id_proof_number'
                    },
                    {
                        "data": "company_name"
                    },
                    {
                        data: 'passport_number'
                    },
                    {
                        data: 'passport_expire_date'
                    },
                    {
                        data: 'border_no'
                    }, {
                        data: 'dob'
                    },
                    {
                        data: 'insurance'
                    },
                    {
                        data: 'contact_name'
                    },
                    {
                        data: 'nationality'
                    },


                    {
                        data: 'residence_permit_expiration'
                    },
                    {
                        data: 'admissions_date'
                    },
                    {
                        data: 'admissions_type',
                        render: function(data, type, row) {

                            if (data === 'first_time') {
                                return '@lang('essentials::lang.first_time')';
                            } else if (data === 'after_vac') {
                                return '@lang('essentials::lang.after_vac')';
                            } else {
                                return '@lang('essentials::lang.no_addmission_yet')';
                            }
                        }
                    },
                    {
                        data: 'admissions_status',
                        render: function(data, type, row) {
                            if (data === 'on_date') {
                                return '@lang('essentials::lang.on_date')';
                            } else if (data === 'delay') {
                                return '@lang('essentials::lang.delay')';
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'contract_end_date'
                    },
                    {
                        data: "contact_number"
                    },
                    {
                        data: "email"
                    },

                    {
                        data: "profession",
                        name: 'profession'
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

                        data: 'essentials_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }

                    },
                    {
                        data: 'total_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }
                    },

                    {
                        data: 'gender',
                        render: function(data, type, row) {
                            if (data === 'male') {
                                return '@lang('lang_v1.male')';
                            } else if (data === 'female') {
                                return '@lang('lang_v1.female')';

                            } else {
                                return '@lang('lang_v1.others')';
                            }
                        }
                    },
                    {
                        data: 'marital_status'
                    },
                    {
                        data: 'blood_group'
                    },
                    {
                        data: 'bank_code',

                    },
                    {
                        data: 'categorie_id',

                    },


                ]
            });
            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            $('#project_name_filter,#doc_filter_date_range,#nationality_filter,#status_fillter').on('change',
                function() {
                    workers_table.ajax.reload();
                });
        });
        chooseFields = function() {
            var selectedOptions = $('#choose_fields_select').val();

            var dt = $('#workers_table').DataTable();

            var fields = fields;
            // var fields = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12,
            //     13, 14, 15,
            //     16, 17, 18
            // ];

            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
@endsection
