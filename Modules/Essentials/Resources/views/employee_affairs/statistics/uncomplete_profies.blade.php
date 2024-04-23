@extends('layouts.app')
@section('title', __('essentials::lang.uncomplemete_profiles'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.uncomplemete_profiles')
        </h1>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('user_type_filter', __('essentials::lang.employee_type') . ':') !!}
                                {!! Form::select(
                                    'user_type_filter',
                                    [
                                        'worker' => __('essentials::lang.worker'),
                                        'employee' => __('essentials::lang.employee'),
                                        'manager' => __('essentials::lang.a_manager'),
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control select2',
                                        'style' => 'width:100%',
                                        'id' => 'user_type_filter',
                                        'placeholder' => __('lang_v1.all'),
                                    ],
                                ) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('company_filter', __('essentials::lang.company') . ':') !!}
                                {!! Form::select('company_filter', $companies, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'id' => 'company_filter',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('project_filter', __('followup::lang.project_name') . ':') !!}
                                {!! Form::select('project_filter', $projects, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'id' => 'project_filter',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('missing_files_filter', __('essentials::lang.missing_file') . ':') !!}
                                {!! Form::select('missing_files_filter', $missing_files, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'id' => 'missing_files_filter',
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="expired_residencies">
                                <thead>
                                    <tr>

                                        <th>@lang('essentials::lang.emp_name')</th>
                                        <th>@lang('essentials::lang.eqama_number')</th>
                                        <th>@lang('followup::lang.project')</th>
                                        <th>@lang('essentials::lang.sponsor')</th>
                                        <th>@lang('essentials::lang.missings_files')</th>
                                        <th>@lang('essentials::lang.missings_info')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent

                </div>
            </div>

        </section>
    @endsection

    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {


                var expired_residencies;

                function reloadDataTable() {
                    expired_residencies.ajax.reload();
                }
                $(document).on('change',
                    '#user_type_filter, #company_filter, #project_filter, #missing_files_filter',
                    function() {

                        reloadDataTable();
                    });

                expired_residencies = $('#expired_residencies').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('uncomplete_profiles') }}",
                        data: function(d) {
                            if ($('#user_type_filter').val() && $('#user_type_filter').val() != 'all') {
                                d.user_type_filter = $('#user_type_filter').val();
                            }
                            if ($('#company_filter').val() && $('#company_filter').val() != 'all') {
                                d.company_filter = $('#company_filter').val();
                            }
                            if ($('#project_filter').val() && $('#project_filter').val() != 'all') {
                                d.project_filter = $('#project_filter').val();
                            }
                            if ($('#missing_files_filter').val() && $('#missing_files_filter').val() !=
                                'all') {
                                d.missing_files_filter = $('#missing_files_filter').val();
                            }

                        }
                    },

                    columns: [

                        {
                            data: 'worker_name'
                        },
                        {
                            data: 'id_proof_number'
                        },
                        {
                            data: 'project'
                        },
                        {
                            data: 'sponsor'
                        },

                        {
                            data: 'missings_files',
                            render: function(data, type, row) {

                                return type === 'display' && data != null ? data.replace(/\\n/g,
                                    '<br>') : data;
                            }
                        },
                        {
                            data: 'missings_info',
                            render: function(data, type, row) {

                                return type === 'display' && data != null ? data.replace(/\\n/g,
                                    '<br>') : data;
                            }
                        },

                    ],
                });




            });
        </script>
    @endsection
