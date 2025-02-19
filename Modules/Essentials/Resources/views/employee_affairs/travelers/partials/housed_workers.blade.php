@extends('layouts.app')
@section('title', __('housingmovements::lang.housed'))
@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.housed')</span>
        </h1>
    </section>



    <!-- Main content -->
    <section class="content">
        @include('essentials::employee_affairs.layouts.nav_trevelers')


        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $salesProjects, null, [
                                'class' => 'form-control select2',
                                'id' => 'project_name_filter',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('housingmovements::lang.arrival_date') . ':') !!}
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
            @php
                $colspan = 5;

            @endphp
            <div class="col-md-8 selectedDiv" style="display:none;">
            </div>
            <table class="table table-bordered table-striped ajax_view hide-footer" id="workers_table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>@lang('housingmovements::lang.worker_name')</th>
                        <th>@lang('housingmovements::lang.project')</th>
                        <th>@lang('housingmovements::lang.location')</th>
                        <th>@lang('housingmovements::lang.arrival_date')</th>
                        <th>@lang('housingmovements::lang.passport_number')</th>
                        <th>@lang('housingmovements::lang.profession')</th>
                        <th>@lang('housingmovements::lang.nationality')</th>


                    </tr>
                </thead>



                <tfoot>
                    <tr>
                        <td colspan="5">
                            <div style="display: flex; width: 100%;">


                                &nbsp;


                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.housed_in_room'))
                                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="housed-selected">
                                        @lang('housingmovements::lang.housed')
                                    </button>
                                @endif




                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\Essentials\Http\Controllers\TravelersController::class, 'housed_data']),
                            'method' => 'post',
                            'id' => 'housed_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('housingmovements::lang.housed')</h4>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('building', __('housingmovements::lang.building') . ':*') !!}
                                    {!! Form::select('building', $buildings, null, [
                                        'class' => 'form-control select2',
                                        'required',
                                        'style' => 'width:100%;padding:2px;',
                                        'placeholder' => __('housingmovements::lang.select_building'),
                                        'id' => 'buildingSelector',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('room', __('housingmovements::lang.room') . ':') !!}
                                {!! Form::select('room', $availableRooms, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'placeholder' => __('housingmovements::lang.room'),
                                    'id' => 'roomSelector',
                                ]) !!}

                                <span id="bedCountMessage" class="text-info"></span>

                            </div>


                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('housingmovements::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.notes'),
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

    <script type="text/javascript">
        var workers_table;

        function reloadDataTable() {
            workers_table.ajax.reload();
        }

        $(document).ready(function() {
            workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('emp_housed_workers') }}',
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name_filter = $('#project_name_filter').val();
                        }

                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.filter_start_date = start;
                            d.filter_end_date = end;
                            d.date_filter = d.date_filter;
                        }
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
                        "data": "full_name"
                    },
                    {
                        "data": "project"
                    },
                    {
                        "data": "location"
                    },
                    {
                        "data": "arrival_date"
                    },
                    {
                        "data": "passport_number"
                    },
                    {
                        "data": "profession"
                    },
                    {
                        "data": "nationality"
                    },

                ]
            });


            $('#project_name_filter').on('change', function() {
                date_filter = null;

                reloadDataTable();
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

            $('#doc_filter_date_range').on('change', function() {
                date_filter = 1;
                workers_table.ajax.reload();
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#workers_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === workers_table.rows()
                    .count());
            });
            $('#housed-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });

            $('#buildingSelector').change(function() {
                var buildingId = $(this).val();
                if (buildingId) {
                    $.ajax({
                        url: '/housingmovements/getRooms/' + buildingId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#roomSelector').empty();
                            $('#roomSelector').append(
                                '<option selected disabled>Select room</option>');
                            $.each(data, function(id, room) {
                                $('#roomSelector').append('<option value="' + id +
                                    '" data-beds_count="' + room.beds_count + '">' +
                                    room.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#roomSelector').empty();
                    $('#bedCountMessage').text('');
                    $('#errorMessage').text('');
                }
            });

            $('#roomSelector').change(function() {
                var selectedOption = $(this).find('option:selected');
                var beds_count = selectedOption.data('beds_count');
                $('#bedCountMessage').text("Selected room has " + beds_count + " beds.");

            });

            $('#changeStatusModal').on('shown.bs.modal', function(e) {
                $('#roomSelector').select2({
                    dropdownParent: $(
                        '#changeStatusModal'),
                    width: '100%',
                });
            });

            $('#submitsBtn').click(function(e) {
                // e.preventDefault();
                var formData = new FormData($('#housed_form')[0]);
                $.ajax({
                    type: 'POST',
                    url: $('#housed_form').attr(
                        'action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        console.log(result);
                        if (result.success === true) {

                            toastr.success(result.msg);
                            $('#changeStatusModal').modal('hide');
                            window.location.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });



            });
        });
    </script>





@endsection
