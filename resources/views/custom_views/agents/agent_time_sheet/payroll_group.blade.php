@extends('layouts.app')
@section('title', __('agent.time_sheet'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('agent.time_sheet_for')</span> {{ $date }}
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <div style="margin-bottom: 10px;">
                    <div class="col-md-12">
                        <div class="col-md-1">
                            <form action="{{ route('agentTimeSheet.timeSheet') }}" method="POST">
                                @csrf <!-- Laravel CSRF token for security -->
                                <input type="hidden" name="editWorkerIds" id='editWorkerIds'>
                                <input type="hidden" name="month_year" value="{{ $month_year }}">
                                <!-- Example hidden input -->
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-edit"></i>
                                    @lang('worker.edit')
                                </button>
                            </form>
                        </div>

                        <div class="col-md-1">
                            <form action="{{ route('agentTimeSheet.submitTmeSheet') }}" method="POST" id="submitForm">
                                @csrf <!-- Laravel CSRF token for security -->
                                <input type="hidden" name="totals" id="totals">
                                <input type="hidden" name="ids" id="ids"> <!-- Example hidden input -->
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check"></i>
                                    @lang('worker.submit')
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br>
                </div>
                <table class="table table-bordered table-striped" id="workers_table_timesheet"
                    style="table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <td style="width: 10px;">
                                <input type="checkbox" id="select-all">
                            </td>
                            <td style="width: 10px;">
                                #
                            </td>
                            <td style="width: 100px;">@lang('worker.name')</td>
                            <td style="width: 100px;">@lang('worker.eqama_number')</td>
                            <td style="width: 100px;">@lang('worker.project')</td>
                            <td style="width: 100px;">@lang('worker.nationality')</td>
                            <td style="width: 100px;">@lang('worker.total_salary')</td>
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

            var employee_ids = @json($employee_ids);
            var month_year = @json($month_year);

            var workers_table_timesheet = $('#workers_table_timesheet').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('agentTimeSheet.getPayrollGroup') }}",
                    data: {

                        'employee_ids': employee_ids // Passing the variable here
                    },

                    dataSrc: function(json) {
                        var data = json.data;
                        let totals = '';
                        let ids = '';
                        data.forEach(element => {
                            totals += element.total + ',';
                            ids += element.id + ',';
                        });
                        totals = totals.replace(/,$/, "");
                        ids = ids.replace(/,$/, "");
                        $('#totals').val(totals);
                        $('#ids').val(ids);
                        return json.data;
                    }
                },

                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id +
                                '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'eqama_number'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'nationality'
                    },
                    {
                        data: 'total',
                        name: 'total',
                    },
                ]
            });
            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#workers_table_timesheet').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === users_table.rows()
                    .count());
            });

            function updateSelectedRowIds() {
                selectedIds = []; // Reset the array to ensure it's up to date
                $('.select-row:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });
                // Convert the array into a JSON string and set it as the value of the hidden input
                $('#editWorkerIds').val(JSON.stringify(selectedIds));
            }
            $('#allWorkerIds').val(employee_ids);
            // Event listener for checkbox changes
            $(document).on('change', '.select-row', function() {
                updateSelectedRowIds();
            });

            // Optionally, handle "select all" checkbox changes
            $('#select-all').change(function() {
                var isChecked = $(this).is(':checked');
                $('.select-row').prop('checked', isChecked);
                updateSelectedRowIds();
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
