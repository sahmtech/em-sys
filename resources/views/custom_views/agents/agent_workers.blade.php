@extends('layouts.app')
@section('title', __('agent.workers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('agent.workers')</span>
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
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <td class="table-td-width-100px">@lang('followup::lang.name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.eqama')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.project_name')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.nationality')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.eqama_end_date')</td>
                            <td class="table-td-width-100px">@lang('followup::lang.admissions_date')</td>
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


            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {


                    url: "{{ route('agent_workers') }}",

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
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' +
                                '{{ route('show_agent_worker', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
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
                        data: "contact_number"
                    },
                    {
                        data: "email"
                    },
                    {
                        data: "essentials_department_id"
                    },
                    {
                        data: "profession",
                        name: 'profession'
                    },
                    {
                        data: "specialization",
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
                        data: 'gender'
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
    </script>
@endsection
