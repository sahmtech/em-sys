@extends('layouts.app')
@section('title', __('followup::lang.workers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.workers')</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="business_filter">@lang('essentials::lang.business_single'):</label>
                            {!! Form::select('select_company_id', $companies, null, [
                                'class' => 'form-control select2',
                                'id' => 'select_company_id',
                                'style' => 'height:40px; width:100%',
                                'placeholder' => __('lang_v1.all'),
                                'required',
                                'autofocus',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts_fillter, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('nationality_filter', __('followup::lang.nationality') . ':') !!}
                            {!! Form::select('nationality_filter', $nationalities, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('status_label', __('followup::lang.status') . ':') !!}

                            <select class="form-control" name="status_fillter" id='status_fillter' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($status_filltetr as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('essentials::lang.contract_end_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div> --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('start_date_filter', __('essentials::lang.expiration_date_from') . ':') !!}
                            {!! Form::date('start_date_filter', null, [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.select_start_date'),
                                'id' => 'start_date_filter',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('end_date_filter', __('essentials::lang.expiration_date_to') . ':') !!}
                            {!! Form::date('end_date_filter', null, [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.select_end_date'),
                                'id' => 'end_date_filter',
                            ]) !!}
                        </div>
                    </div>
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
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-3">
                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.add_essentials_workers'))
                        @slot('tool')
                            <div class="box-tools">
                                <a class="btn btn-block btn-primary" href="{{ route('add_workers_affairs') }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                                </a>
                            </div>
                        @endslot
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table" style=" table-layout: fixed !important;">
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



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            $('#status_fillter').select2();

            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {

                    url: "{{ route('workers_affairs') }}",

                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#nationality_filter').val()) {
                            d.nationality = $('#nationality_filter').val();
                        }
                        if ($('#status_fillter').val()) {
                            d.status_fillter = $('#status_fillter').val();
                        }
                        if ($('#select_company_id').val()) {
                            d.company = $('#select_company_id').val();
                        }
                        // if ($('#doc_filter_date_range').val()) {
                        //     var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                        //         .format('YYYY-MM-DD');
                        //     var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                        //         .format('YYYY-MM-DD');
                        //     d.start_date = start;
                        //     d.end_date = end;
                        // }
                    }
                },

                columns: [{
                        data: "worker_id"
                    },
                    {
                        data: "profile_image",
                        render: function(data, type, row) {
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
                        data: "emp_number"
                    },


                    {
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' +
                                '{{ route('show_workers_affairs', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: "company_name"
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
            $('#project_name_filter,#doc_filter_date_range,#nationality_filter,#status_fillter,#select_company_id')
                .on('change',
                    function() {
                        workers_table.ajax.reload();
                    });
        });
        chooseFields = function() {
            var selectedOptions = $('#choose_fields_select').val();

            var dt = $('#workers_table').DataTable();

            var fields = fields;

            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
@endsection
