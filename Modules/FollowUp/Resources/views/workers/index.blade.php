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
                        height: 40px;
                        border-radius: 4px;">تطبيق</button>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table">
                    <thead>
                        <tr>
                            {{-- <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>

                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('followup::lang.contract_end_date')</th> --}}
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <td class="table-td-width-100px">@lang('followup::lang.id')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.company')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.eqama')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.project_name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.nationality')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.eqama_end_date')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.admissions_date')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.admissions_type')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.admissions_status')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.contract_end_date')</td>
                            <td class="table-td-width-100px">@lang('essentials::lang.mobile_number')</td>
                            <td class="table-td-width-100px">@lang('business.email')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.department')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.profession')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.specialization')</td>
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
                <div style="margin-bottom: 10px;">
 
                    @if(auth()->user()->hasRole('Admin#1') ||  auth()->user()->can('followup.cancle_worker_project'))
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="cancle-project-selected">
                        @lang('followup::lang.cancle_worker_project')
                    </button>
                    @endif
                </div>

            </div>
            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'cancleProject']),
                            'method' => 'post',
                            'id' => 'cancle_project_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('followup::lang.cancle_worker_project')</h4>
                        </div>

                        <div class="modal-body">
                            
                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                                <div class="form-group col-md-6">
                                    {!! Form::label('canceled_date', __('followup::lang.canceled_date') . ':') !!}
                                    {!! Form::date('canceled_date', null, [
                                        'class' => 'form-control',
                                        'style' => ' height: 40px',
                                        'placeholder' => __('followup::lang.canceled_date'),
                                      
                                    ]) !!}
                                </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('notes', __('followup::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('followup::lang.note'),
                                    'rows' => 2,
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="submitsBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
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
               
                info: false,
                ajax: {

                    url: "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index']) }}",
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

                columns: [
                    
                
                   {
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id + '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data:"id"
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
                        data: 'company_id'                
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
        
            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#workers_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === workers_table.rows()
                    .count());
            });

            $('#cancle-project-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });
            $('#submitsBtn').click(function() {
                var formData = new FormData($('#cancle_project_form')[0]);
                console.log('1111111111111');
                $.ajax({
                    type: 'POST',
                    url: $('#cancle_project_form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success == true) {
                            toastr.success(result.msg);
                            workers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });

                $('#changeStatusModal').modal('hide');
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
