@extends('layouts.app')
@section('title', __('followup::lang.reports.projectsReports'))

@section('content')


    <!-- Main content -->
    <section class="content">
        <div class="modal-header">
            <h2>
                <span>@lang('followup::lang.reports.projectsReports')</span>
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('customer_name_filter', __('sales::lang.customer_name') . ':') !!}
                            {!! Form::select('customer_name_filter', $contactLocation_fillter, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div> --}}
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

                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_name_filter">@lang('followup::lang.customer_name'):</label>
                            <select class="form-control select2" name="customer_name_filter" required id="customer_name_filter"
                                style="width: 100%;padding:2px;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="not_started">@lang('followup::lang.not_started')</option>
                                <option value="under_process">@lang('followup::lang.under_process')</option>
                                <option value="done">@lang('followup::lang.done')</option>

                            </select>
                        </div>
                    </div> --}}
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            <label for="type_filter">@lang('followup::lang.project_type'):</label>
                            <select class="form-control select2" name="type_filter" required id="type_filter"
                                style="width: 100%;padding:2px;">
                                <option value="all">@lang('lang_v1.all')</option>
                                <option value="External">@lang('followup::lang.external')</option>
                                <option value="Internal">@lang('followup::lang.internal')</option>


                            </select>
                        </div>
                    </div> --}}
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="projects_table">
                    <thead>
                        <tr>
                            {{-- <th>@lang('sales::lang.customer_name')</th> --}}
                            <th>@lang('sales::lang.project_name')</th>

                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.start_date')</th>
                            <th>@lang('sales::lang.end_date')</th>
                            <th>@lang('sales::lang.active_worker_count')</th>
                            <th>@lang('sales::lang.worker_count')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            var table = $('#projects_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects']) }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#customer_name_filter').val()) {
                            d.customer_name = $('#customer_name_filter').val();
                        }
                        // if ($('#type_filter').val()) {
                        //     d.type = $('#type_filter').val();
                        // }

                    }
                },
                columns: [{
                        data: 'contact_name'
                    },
                    // {
                    //     data: 'project'
                    // },
                    {
                        data: 'number_of_contract'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'active_worker_count'
                    },
                    {
                        data: 'worker_count'
                    }
                ]

            });

            $('#project_name_filter,#customer_name_filter,#type_filter').on('change', function() {
                table.ajax.reload();
                //   $('#projects_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
