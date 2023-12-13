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
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('essentials::lang.contract_end_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                    @php
                        $default_fields = [$fields[0], $fields[1], $fields[2], $fields[3], $fields[4], $fields[5], $fields[6]];

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
                        height: 36px;
                        border-radius: 4px;">تطبيق</button>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            {{-- <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>

                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('followup::lang.contract_end_date')</th> --}}
                            <td style="width: 100px !important;">@lang('followup::lang.name')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.eqama')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.project_name')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.nationality')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.eqama_end_date')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.admissions_date')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.contract_end_date')</td>
                            <td style="width: 100px !important;">@lang('essentials::lang.mobile_number')</td>
                            <td style="width: 100px !important;">@lang('business.email')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.department')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.profession')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.specialization')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.status')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.Basic_salary')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.total_salary')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.gender')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.marital_status')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.blood_group')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.bank_code')</td>




                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

{{-- @section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var date_filter = null;
            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url:  "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index']) }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#nationality_filter').val()) {
                            d.nationality = $('#nationality_filter').val();
                        }
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.filter_start_date = start;
                            d.filter_end_date = end;
                            d.date_filter = date_filter;
                        }
                    }
                },
                // columns: [{
                //         data: 'worker'
                //     },
                //     {
                //         data: 'id_proof_number'
                //     },
                //     {
                //         data: 'contact_name'
                //     },
                //     {
                //         data: 'essentials_salary'
                //     },


                //     {
                //         data: 'nationality'
                //     },
                //     {
                //         data: 'residence_permit_expiration'
                //     },
                //     {
                //         data: 'contract_end_date'
                //     },
                // ]
                columns: [{
                        data: 'worker'
                    },
                    {
                        data: 'residence_permit'
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
                        data: 'contract_end_date'
                    },
                    {
                        "data": "contact_number"
                    }, {
                        "data": "email"
                    }, {
                        "data": "essentials_department_id"
                    }, {
                        "data": "profession",
                        name: 'profession'
                    },
                    {
                        "data": "specialization",
                        name: 'specialization'
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
                        data: 'essentials_salary'

                    },
                    {
                        data: 'total_salary'
                    },
                    {
                        data: 'gender'
                    },
                    {
                        data: 'marital_status'
                    },
                    {
                        data: 'blood_group'
                    }, {
                        data: 'bank_code',

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
                date_filter = null;
                reloadDataTable();
            });
            $('#project_name_filter, #nationality_filter').on('change', function() {
                workers_table.ajax.reload();
            });
            $('#doc_filter_date_range').on('change', function() {
                date_filter = 1;
                workers_table.ajax.reload();
            });
        });

        chooseFields = function() {
            var selectedOptions = $('#choose_fields_select').val();

            var dt = $('#workers_table').DataTable();

            var fields = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12,
                13, 14, 15,
                16, 17, 18
            ];

            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
@endsection --}}
@section('javascript')
    <script>
        $(document).ready(function() {

            // $('#workers_table').DataTable({

            // });

            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {

                    url: "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projectWorkers']) }}",
                    // url: "{{ route('projectWorkers') }}",

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
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },

                columns: [{
                        data: 'worker'
                    },
                    {
                        data: 'residence_permit'
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
                        data: 'contract_end_date'
                    },
                    {
                        "data": "contact_number"
                    }, {
                        "data": "email"
                    }, {
                        "data": "essentials_department_id"
                    }, {
                        "data": "profession",
                        name: 'profession'
                    },
                    {
                        "data": "specialization",
                        name: 'specialization'
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
                        data: 'essentials_salary'

                    },
                    {
                        data: 'total_salary'
                    },
                    {
                        data: 'gender'
                    },
                    {
                        data: 'marital_status'
                    },
                    {
                        data: 'blood_group'
                    }, {
                        data: 'bank_code',

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
            //  [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12,
            //     13, 14, 15,
            //     16, 17, 18
            // ];

            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
@endsection
