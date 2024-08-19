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
            <div class="col-md-12" style="margin-bottom: 5px;">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#askForWorkerModal">
                    @lang('essentials::lang.ask_for_worker')
                </button>
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

        <div class="modal fade" id="askForWorkerModal" tabindex="-1" role="dialog"
            aria-labelledby="askForWorkerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="askForWorkerModalLabel">@lang('essentials::lang.ask_for_worker')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('essentials::lang.close')">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="askForWorkerForm">
                            @csrf
                            <div class="form-group">
                                <label for="worker_identifier">@lang('essentials::lang.worker_identifier')</label>
                                <input type="text" class="form-control" id="worker_identifier" name="worker_identifier"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">@lang('essentials::lang.submit')</button>
                        </form>

                        <!-- Worker info section -->
                        <div id="worker-info" style="display:none; margin-top: 20px;">
                            <h5>@lang('essentials::lang.worker_information')</h5>
                            <p><strong>@lang('essentials::lang.full_name'):</strong> <span id="worker_full_name"></span></p>
                            <p><strong>@lang('essentials::lang.emp_number'):</strong> <span id="worker_emp_number"></span></p>
                            <p><strong>@lang('essentials::lang.status'):</strong> <span id="worker_status"></span></p>
                            <p><strong>@lang('essentials::lang.sub_status'):</strong> <span id="worker_sub_status"></span></p>

                            <p><strong>@lang('essentials::lang.id_proof_number'):</strong> <span id="worker_id_proof_number"></span></p>
                            <p><strong>@lang('essentials::lang.residence_permit_expiration'):</strong> <span id="worker_residence_permit_expiration"></span>
                            </p>
                            <p><strong>@lang('essentials::lang.passport_number'):</strong> <span id="worker_passport_number"></span></p>
                            <p><strong>@lang('essentials::lang.passport_expire_date'):</strong> <span id="worker_passport_expire_date"></span></p>
                            <p><strong>@lang('essentials::lang.border_number'):</strong> <span id="worker_border_no"></span></p>
                            <p><strong>@lang('essentials::lang.company_name'):</strong> <span id="worker_company_name"></span></p>
                            <p><strong>@lang('essentials::lang.assigned_to'):</strong> <span id="worker_assigned_to"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        if ($('#start_date_filter').val()) {
                            d.start_date = $('#start_date_filter').val();
                        }
                        if ($('#end_date_filter').val()) {
                            d.end_date = $('#end_date_filter').val();
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


            $('#project_name_filter,#end_date_filter,#start_date_filter,#nationality_filter,#status_fillter,#select_company_id')
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
    <script>
        $(document).ready(function() {
            $('#askForWorkerForm').on('submit', function(e) {
                e.preventDefault();

                var worker_identifier = $('#worker_identifier').val();

                $.ajax({
                    url: '{{ route('get-worker-info') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        worker_identifier: worker_identifier
                    },
                    success: function(response) {
                        if (response.success) {

                            $('#worker_full_name').text(response.data.full_name);
                            $('#worker_emp_number').text(response.data.emp_number);

                            $('#worker_status').text(response.data.status);
                            $('#worker_sub_status').text(response.data.sub_status);
                            $('#worker_id_proof_number').text(response.data.id_proof_number);
                            $('#worker_residence_permit_expiration').text(response.data
                                .residence_permit_expiration);
                            $('#worker_passport_number').text(response.data.passport_number);
                            $('#worker_passport_expire_date').text(response.data
                                .passport_expire_date);
                            $('#worker_border_no').text(response.data.border_no);
                            $('#worker_company_name').text(response.data.company_name);
                            $('#worker_assigned_to').text(response.data.assigned_to);

                            $('#worker-info').show();
                        } else {
                            $('#worker-info').hide();
                            alert('@lang('essentials::lang.worker_not_found')');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#worker-info').hide();
                        alert('@lang('essentials::lang.error_occurred')');
                    }
                });
            });
        });
    </script>
@endsection
