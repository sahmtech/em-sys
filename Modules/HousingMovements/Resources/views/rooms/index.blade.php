@extends('layouts.app')
@section('title', __('housingmovements::lang.rooms'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.rooms')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    @if (!empty($buildings))
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('htr_building_filter', __('housingmovements::lang.htr_building') . ':') !!}
                                {!! Form::select('htr_building_filter', $buildings, null, [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}
                            </div>
                        </div>
                    @endif

                    <div class="form-group col-md-3">
                        {!! Form::label('room_status', __('housingmovements::lang.room_status') . ':*') !!}
                        {!! Form::select('room_status', $roomStatusOptions, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('housingmovements::lang.room_status'),
                            'id' => 'room_status',
                        ]) !!}
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary" data-toggle="modal"
                                data-target="#createRoomModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rooms_table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>@lang('housingmovements::lang.room_number')</th>
                                    <th>@lang('housingmovements::lang.htr_building')</th>
                                    <th>@lang('housingmovements::lang.area')</th>
                                    <th>@lang('housingmovements::lang.total_beds')</th>
                                    <th>@lang('housingmovements::lang.available_beds')</th>
                                    <th>@lang('housingmovements::lang.contents')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>



                        </table>
                        <div style="margin-bottom: 10px;">

                            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.housed_in_room'))
                                <button type="button" class="btn btn-warning btn-sm custom-btn" id="housed-selected">
                                    @lang('housingmovements::lang.housed')
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog"
                        aria-labelledby="gridSystemModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                {!! Form::open([
                                    'url' => action([\Modules\HousingMovements\Http\Controllers\RoomController::class, 'workers_housed']),
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

                                    <div id="roomNumbersDisplay">

                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" id="submitBtn">@lang('messages.save')</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">@lang('messages.close')</button>
                                </div>

                                {!! Form::close() !!}
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div>
                @endcomponent
            </div>



            @include('housingmovements::rooms.edit')

        </div>

        @include('housingmovements::rooms.create_modal')
    </section>
    <!-- /.content -->
    <div class="col-md-8 selectedDiv" style="display:none;">
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        var rooms_table;

        function reloadDataTable() {
            rooms_table.ajax.reload();
        }

        $(document).ready(function() {
            var workersData = @json($workers);
            var translations = {
                room: "@lang('housingmovements::lang.room')",
                available_beds: "@lang('housingmovements::lang.beds_available')",
                please_select_row: "@lang('housingmovements::lang.please_select_row')",
                add_workers: "@lang('housingmovements::lang.select')",
            };
            rooms_table = $('#rooms_table').DataTable({
                processing: true,
                serverSide: true,
                info: false,
                ajax: {
                    url: '{{ route('rooms') }}',
                    data: function(d) {
                        if ($('#htr_building_filter').length) {
                            d.htr_building = $('#htr_building_filter').val();
                        }

                        d.room_status = $('#room_status').val();
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
                        data: 'room_number'
                    },
                    {
                        data: 'htr_building_id'
                    },
                    {
                        data: 'area'
                    },
                    {
                        data: 'total_beds'
                    },
                    {
                        data: 'beds_count'
                    },
                    {
                        data: 'contents'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#htr_building_filter, #room_status').on('change', function() {
                console.log($('#room_status').val());
                reloadDataTable();
            });

            $(document).on('click', 'button.delete_room_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_room,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    rooms_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $('body').on('submit', '#createRoomModal form', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var url = $(this).attr('action');


                $.ajax({
                    method: "post",
                    url: url,
                    data: formData,
                    dataType: "json",
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            rooms_table.ajax.reload();
                            $('#createRoomModal').modal('hide');
                        } else {
                            toastr.error(result.msg);
                            $('#createRoomModal').modal('hide');
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);
                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });

            $('body').on('click', '.open-edit-modal', function() {
                var roomId = $(this).data('id');
                $('#roomIdInput').val(roomId);

                var editUrl = '{{ route('room.edit', ':roomId') }}'
                editUrl = editUrl.replace(':roomId', roomId);
                console.log(editUrl);

                $.ajax({
                    url: editUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var data = response.data;

                        $('#editroomModal select[name="htr_building"]').val(data.room
                            .htr_building_id).trigger('change');

                        $('#editroomModal input[name="room_number"]').val(data.room
                            .room_number);
                        $('#editroomModal input[name="area"]').val(data.room.area);
                        $('#editroomModal input[name="total_beds"]').val(data.room.total_beds);
                        $('#editroomModal textarea[name="contents"]').val(data.room.contents);

                        $('#editroomModal').modal('show');
                    },
                    error: function(error) {
                        console.error('Error fetching user data:', error);
                    }
                });
            });

            $('body').on('submit', '#editroomModal form', function(e) {
                e.preventDefault();

                var roomId = $('#roomIdInput').val();
                console.log(roomId);

                var urlWithId = '{{ route('updateRoom', ':roomId') }}';
                urlWithId = urlWithId.replace(':roomId', roomId);
                console.log(urlWithId);

                $.ajax({
                    url: urlWithId,
                    type: 'POST',
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            console.log(response);
                            toastr.success(response.msg);
                            reloadDataTable();
                            $('#editroomModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);

                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#rooms_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === rooms_table.rows()
                    .count());
            });




            $('#housed-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return $(this).data('id');
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));

                if (selectedRows.length > 0) {
                    var postData = {
                        ids: selectedRows,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    $.ajax({
                        type: 'POST',
                        url: '{{ route('rooms.numbers') }}',
                        data: postData,
                        success: function(response) {
                            $('#roomNumbersDisplay').empty();

                            $.each(response, function(roomId, roomInfo) {
                               
                                var workerSelectDropdown = $('<select>', {
                                    id: 'workerSelectId_' + roomId,
                                    name: 'workers[' + roomId + '][]',
                                    class: 'form-control select2 select2-workers',
                                    style: 'height: 40px; width: 320px; margin-right: 0;',
                                    multiple: true
                                });

                                workerSelectDropdown.append('<option></option>');
                                $.each(workersData, function(workerId, workerName) {
                                    workerSelectDropdown.append(new Option(
                                        workerName, workerId));
                                });

                                var roomEntryDiv = $('<div>', {
                                    class: 'room-entry',
                                    'data-room-id': roomId
                                }).append(
                                    $('<p>').append($('<span>', {
                                        style: 'color: red;',
                                        text: translations.room + ' ' +
                                            roomInfo.room_number + ' : (' +
                                            translations.available_beds +
                                            ': ' + roomInfo.beds_count +
                                            ' )'
                                    })),
                                    $('<label>', {
                                        text: translations.add_workers + ':'
                                    }),
                                    workerSelectDropdown
                                );

                                $('#roomNumbersDisplay').append(roomEntryDiv);

                                workerSelectDropdown.select2({
                                    width: 'resolve',
                                    placeholder: translations.add_workers,
                                    allowClear: true
                                });
                            });

                            $('#changeStatusModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching room info:", error);
                        }
                    });
                } else {
                    alert(translations.please_select_row);
                }
            });

            $('#submitBtn').click(function() {
                var selectedRoomsData = [];

                $('.room-entry').each(function() {
                    var roomId = $(this).data('room-id');
                    var selectedWorkers = $(this).find('select').val();

                    selectedRoomsData.push({
                        room_id: roomId,
                        workers: selectedWorkers
                    });
                });

                var postData = {
                    selectedRooms: JSON.stringify(selectedRoomsData),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    type: 'POST',
                    url: $('#housed_form').attr('action'),
                    data: postData,
                    success: function(result) {
                        console.log(result);
                        if (result.success === true) {
                            toastr.success(result.msg);
                          //  rooms_table.ajax.reload();
                            window.location.reload();

                            $('#changeStatusModal').modal('hide');
                          
                        } else {
                            toastr.error(result.msg);
                        }
                       
                    },
                    error: function(error) {
                        console.error("Error submitting data:", error);
                    }
                });
            });


        

        });
    </script>
@endsection
