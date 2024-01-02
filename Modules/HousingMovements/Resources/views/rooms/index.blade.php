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
                        <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#createRoomModal">
                                    <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rooms_table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="largerCheckbox" id="chkAll" /></th>
                                    <th>@lang('housingmovements::lang.room_number')</th>
                                    <th>@lang('housingmovements::lang.htr_building')</th>
                                    <th>@lang('housingmovements::lang.area')</th>
                                    <th>@lang('housingmovements::lang.beds_count')</th>
                                    <th>@lang('housingmovements::lang.contents')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>



                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <div style="display: flex; width: 100%;">

                                            {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']) !!}

                                            @include('housingmovements::rooms.room_housed_modal')

                                            {!! Form::submit(__('housingmovements::lang.housed'), [
                                                'class' => 'btn btn-xs btn-success',
                                                'id' => 'rooms-selected',
                                            ]) !!}



                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
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
        var translations = {
            cantHoused: '{{ __('housingmovements::lang.cant_housed') }}',
            notAvailable: '{{ __('housingmovements::lang.not_avaiable') }}',

            room_number: '{{ __('housingmovements::lang.room_number') }}',
            worker_name: '{{ __('housingmovements::lang.worker_name') }}',
            transfer_to_room:'{{ __('housingmovements::lang.transfer_to_room') }}',
        };
        var rooms_table;

        function reloadDataTable() {
            rooms_table.ajax.reload();
        }

        $(document).ready(function() {
            rooms_table = $('#rooms_table').DataTable({
                processing: true,
                serverSide: true,
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
                        data: 'id',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' +
                                data + '" />';
                        }
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


            $('#rooms_table').on('change', '.tblChk', function() {

                if ($('.tblChk:checked').length == $('.tblChk').length) {
                    $('#chkAll').prop('checked', true);
                } else {
                    $('#chkAll').prop('checked', false);
                }
                getCheckRecords();
            });

$("#chkAll").change(function () {
          
          if ($(this).prop('checked')) {
              $('.tblChk').prop('checked', true);
          } else {
              $('.tblChk').prop('checked', false);
          }
          getCheckRecords();
});


    

$(document).ready(function () {
    $('#roomsModal').on('hidden.bs.modal', function () {
        location.reload();
    });
});


$('#rooms-selected').on('click', function (e) {
    e.preventDefault();

    var selectedRows = getCheckRecords();

    if (selectedRows.length > 0) {
        var i = 0;
        $.ajax({
            url: '{{ route('getSelectedroomsData') }}',
            type: 'post',
            data: {
                selectedRows: selectedRows
            },
            success: function (data) {

                
                console.log(data);
                $('.modal-body').empty();

                var inputClasses = 'form-group col-md-2';
                var labelRow = $('<div class="row" style="padding-bottom: 10px;">');
                labelRow.append('<div class="col-md-4">' + translations.room_number + '</div>');
                labelRow.append('<div class="col-md-4">' + translations.worker_name + '</div>');
                labelRow.append('<div class="col-md-4" style="display: none;">' + translations.transfer_to_room + '</div>');
                $('.modal-body').append(labelRow);

                $.each(data.rooms, function (index, room) {
                    var roomIDInput = $('<input>', {
                        type: 'hidden',
                        name: 'room_id[]',
                        class: inputClasses,
                        required: true,
                        value: room.room_id
                    });

                    var roomnumberInput = $('<input>', {
                        type: 'text',
                        name: 'room_number[]',
                        class: inputClasses,
                        style: ' height:36px; width:150px; margin-right: 0;',
                        placeholder: translations.room_number,
                        required: true,
                        value: room.room_number
                    });                  
// var roomSelect = $('<select>', {
//     id: 'roomSelectId_' + index,  // Corrected ID generation
//     name: 'transfer_to_room_id[]',
//     class: inputClasses + ' select2',
//     style: 'height: 40px; width:220px; margin-right: 0; display: none;', // initially hidden
// });

// // Populate room options for transfer
// $.each(data.rooms, function (transferIndex, transferRoom) {
//     if (transferRoom.room_id !== room.room_id) {
//         var option = $('<option>', {
//             value: transferRoom.room_id,
//             text: transferRoom.room_number
//         });
//         roomSelect.append(option);
//     }
// });


                    var workerSelect = $('<select>', {
                        id: 'workerSelectId_' + index, 
                     
                        name: 'worker_id[]',
                        class: inputClasses + ' select2',
                        style: 'height: 40px; width:220px; margin-right: 0;',
                     
                        multiple: true,
                       // disabled: room.beds_count === 0 
                    });

                       // Populate worker options for the current room
                    $.each(data.workers[room.room_id], function (workerId, workerName) {
                        var option = $('<option>', {
                            value: workerId,
                            text: workerName
                        });
                        workerSelect.append(option);
                    });
//                     console.log(room.beds_count );
//                     if (room.beds_count === 0) {
//     $('#roomSelectId_' + index).show();  // Corrected ID reference
//     $('#roomSelectId_' + index).select2({
//         dropdownParent: $('#roomsModal'),
//     });
// } else {
//     $('#roomSelectId_' + index).hide();
// }

                    var row = $('<div class="row" style="margin-bottom: 10px;">');
                    row.append('<div class="col-md-6"></div>');
                    row.append('<div class="col-md-6"></div>');
                    row.append(roomnumberInput);
                    row.append(workerSelect);
                   // row.append(roomSelect);
                    $('.modal-body').append(row);

                    $('#workerSelectId_' + index).select2({
                        dropdownParent: $('#roomsModal'),
                    });

                    
                    $('#roomSelectId_' + index).select2({
                        dropdownParent: $('#roomsModal'),
                    });


                    if (room.beds_count === 0) {
                       

                        swal({
                            text: translations.cantHoused + ' ' +
                                room.room_number + ' ' +
                                translations.notAvailable,
                            icon: 'error',
                            button: 'OK',
                        });
                        $('#room_form').modal('hide');
                        reloadDataTable();
                    }
                });

                $('#roomsModal').modal('show');
            },
            error: function (xhr, status, error) {
                // Handle the error, you can log it or show an alert
                console.error('Error:', error);
                swal({
                    text: 'An error occurred while fetching data.',
                    icon: 'error',
                    button: 'OK',
                });
            }
        });
    } else {
        $('input#selected_rows').val('');
        swal({
            title: "@lang('lang_v1.no_row_selected')",
            icon: "warning",
            button: "OK",
        });
    }
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




$('body').on('click', '.open-edit-modal', function() {
        var roomId = $(this).data('id');
        $('#roomIdInput').val(roomId);

        var editUrl = '{{ route("room.edit", ":roomId") }}'
        editUrl = editUrl.replace(':roomId', roomId);
     

        $.ajax({
            url: editUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var data = response.data;

                $('#editroomModal select[name="htr_building"]').val(data.room.htr_building_id).trigger('change');
               
                $('#editroomModal input[name="room_number"]').val(data.room.room_number);
                $('#editroomModal input[name="area"]').val(data.room.area);
                $('#editroomModal input[name="beds_count"]').val(data.room.beds_count);
                $('#editroomModal textarea[name="contents"]').val(data.room.contents);

                $('#editroomModal').modal('show');
            },
            error: function(error) {
                console.error('Error fetching user data:', error);
            }
        });
    });

$('body').on('submit', '#editroomModal form', function (e) {
    e.preventDefault();

    var roomId = $('#roomIdInput').val();
 

    var urlWithId = '{{ route("updateRoom", ":roomId") }}';
    urlWithId = urlWithId.replace(':roomId', roomId);
 

    $.ajax({
        url: urlWithId,
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
             
                toastr.success(response.msg, 'Success');
                reloadDataTable();
                $('#editroomModal').modal('hide');
            } else {
                toastr.error(response.msg);
            
            }
        },
        error: function (error) {
            console.error('Error submitting form:', error);
            
            toastr.error('An error occurred while submitting the form.', 'Error');
        },
    });
});

        });

        function getCheckRecords() {
            var selectedRows = [];
            $(".selectedDiv").html("");

            $('.tblChk:checked').each(function() {
                if ($(this).prop('checked')) {
                    const rec = "<strong>" + $(this).attr("data-id") + " </strong>";
                
                    $(".selectedDiv").append(rec);
                    selectedRows.push($(this).attr("data-id"));

                

                }

            });

            return selectedRows;
        }



    </script>
@endsection
