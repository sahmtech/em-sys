@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('agent.time_sheet')</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                {{-- @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
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
                @endcomponent --}}
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table_timesheet" style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <td style="width: 100px;">@lang('worker.name')</td>
                            <td style="width: 100px;">@lang('worker.eqama_number')</td>
                            <td style="width: 100px;">@lang('worker.location')</td>
                            <td style="width: 100px;">@lang('worker.nationality')</td>
                            <td style="width: 100px;">@lang('worker.monthly_cost')</td>
                            <td style="width: 100px;">@lang('worker.wd')</td>
                            <td style="width: 100px;">@lang('worker.actual_work_days')</td>
                            <td style="width: 100px;">@lang('worker.daily_work_hours')</td>
                            <td style="width: 100px;">@lang('worker.absence_day')</td>
                            <td style="width: 100px;">@lang('worker.absence_amount')</td>
                            <td style="width: 100px;">@lang('worker.over_time_h')</td>
                            <td style="width: 100px;">@lang('worker.over_time')</td>
                            <td style="width: 100px;">@lang('worker.other_deduction')</td>
                            <td style="width: 100px;">@lang('worker.other_addition')</td>
                            <td style="width: 100px;">@lang('worker.cost2')</td>
                            <td style="width: 100px;">@lang('worker.invoice_value')</td>
                            <td style="width: 100px;">@lang('worker.vat')</td>
                            <td style="width: 100px;">@lang('worker.total')</td>
                            <td style="width: 100px;">@lang('worker.sponser')</td>
                            <td style="width: 100px;">@lang('worker.basic')</td>
                            <td style="width: 100px;">@lang('worker.housing')</td>
                            <td style="width: 100px;">@lang('worker.transport')</td>
                            <td style="width: 100px;">@lang('worker.other_allowances')</td>
                            <td style="width: 100px;">@lang('worker.total_salary')</td>
                            <td style="width: 100px;">@lang('worker.deductions')</td>
                            <td style="width: 100px;">@lang('worker.additions')</td>
                            <td style="width: 100px;">@lang('worker.final_salary')</td>
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


            var workers_table_timesheet = $('#workers_table_timesheet').DataTable({
                processing: true,
                serverSide: true,

                ajax: {


                    url: "{{ route('agentTimeSheet.index') }}",

                    // data: function(d) {
                    //     if ($('#project_name_filter').val()) {
                    //         d.project_name = $('#project_name_filter').val();
                    //     }
                    //     if ($('#nationality_filter').val()) {
                    //         d.nationality = $('#nationality_filter').val();
                    //     }
                    //     if ($('#status_fillter').val()) {
                    //         d.status_fillter = $('#status_fillter').val();
                    //     }
                    //     if ($('#doc_filter_date_range').val()) {
                    //         var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                    //             .format('YYYY-MM-DD');
                    //         var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                    //             .format('YYYY-MM-DD');
                    //         d.start_date = start;
                    //         d.end_date = end;
                    //     }
                    // }
                    dataSrc: function(json) {
                        // Do something on success here
                        console.log("Ajax request successful");
                        console.log(json); // This logs the returned data from the server

                        // For example, you can check the length of the data
                        if (json.data.length > 0) {
                            console.log("Data retrieved successfully");
                        } else {
                            console.log("No data found");
                        }

                        // Return the data to populate the table
                        return json.data;
                    }
                },

                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'eqama_number'
                    },
                    {
                        data: 'location'
                    },
                    {
                        data: 'nationality'
                    },
                    
                    {
                        data: 'monthly_cost'
                    },
                    {
                        data: 'wd'
                    },
                    {
                        data: 'actual_work_days'
                    },
                    {
                        data: 'daily_work_hours'
                    },
                    {
                        data: 'absence_day'
                    },
                    {
                        data: 'absence_amount'
                    },
                    {
                        data: 'over_time_h'
                    },
                    {
                        data: 'over_time'
                    },
                    {
                        data: 'other_deduction'
                    },
                    {
                        data: 'other_addition'
                    },
                    {
                        data: 'cost2'
                    },
                    {
                        data: 'invoice_value'
                    },
                    {
                        data: 'vat'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'sponser'
                    },
                    {
                        data: 'basic'
                    },
                    {
                        data: 'housing'
                    },
                    {
                        data: 'transport'
                    },
                    {
                        data: 'other_allowances'
                    },
                    {
                        data: 'total_salary'
                    },
                    {
                        data: 'deductions'
                    },
                    {
                        data: 'additions'
                    },
                    {
                        data: 'final_salary'
                    }


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
                    workers_table_timesheet.ajax.reload();
                });
        });
        chooseFields = function() {
            var selectedOptions = $('#choose_fields_select').val();
            var dt = $('#workers_table_timesheet').DataTable();
            var fields = fields;
            dt.columns(fields).visible(false);
            dt.columns(selectedOptions).visible(true);

        }
    </script>
@endsection
