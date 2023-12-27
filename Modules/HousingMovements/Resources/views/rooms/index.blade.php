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
                                data-target="#addRoomModal">
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


            <div class="modal fade room_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        <div class="modal fade" id="addRoomModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeRoom']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.add_room')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                {!! Form::label('room_number', __('housingmovements::lang.room_number') . ':*') !!}
                                {!! Form::number('room_number', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.room_number'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-4">
                                {!! Form::label('area', __('housingmovements::lang.area') . ':') !!}
                                {!! Form::text('area', null,
                                     ['class' => 'form-control',
                                      'placeholder' => __('housingmovements::lang.area'),'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('htr_building', __('housingmovements::lang.htr_building') . ':*') !!}
                                {!! Form::select('htr_building',
                                     $buildings, null, ['class' => 'form-control select2','style'=>'width:100%;height:40px;',
                                     'placeholder' => __('housingmovements::lang.htr_building'), 'required']) !!}
                            </div>
        
                        
                            <div class="form-group col-md-4">
                                {!! Form::label('beds_count', __('housingmovements::lang.beds_count') . ':*') !!}
                                {!! Form::number('beds_count', null, ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.beds_count'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-8">
                                {!! Form::label('contents', __('housingmovements::lang.contents') . ':*') !!}
                                {!! Form::textarea('contents', null, ['class' => 'form-control ', 'placeholder' => __('housingmovements::lang.contents'),'row'=>'1']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
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
                        // Add the room_status filter
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

            $('#rooms-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();


                if (selectedRows.length > 0) {
                    $('#roomsModal').modal('show');
                    var i = 0;
                    $.ajax({
                        url: '{{ route('getSelectedroomsData') }}',
                        type: 'post',
                        data: {
                            selectedRows: selectedRows
                        },
                        success: function(data) {
                            $('.modal-body').empty();

                            var inputClasses = 'form-group col-md-4 ';

                            $.each(data.rooms, function(index, room) {
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
                                    style: 'height: 40px',
                                    placeholder: '{{ __('housingmovements::lang.room_number') }}',
                                    required: true,
                                    value: room.room_number
                                });

                                var workerSelect = $('<select>', {
                                    id: 'workerSelectId',
                                    name: 'worker_id[]',
                                    class: inputClasses + ' select2',
                                    style: 'height: 40px; width: 350px;',
                                    required: true,
                                });



                                // Populate worker dropdown options
                                $.each(data.workers, function(workerId, workerName) {
                                    var option = $('<option>', {
                                        value: workerId,
                                        text: workerName
                                    });
                                    workerSelect.append(option);
                                });


                                // Append elements to modal body
                                $('.modal-body').append(roomIDInput, roomnumberInput,
                                    workerSelect);
                                $('#workerSelectId').select2({
                                    dropdownParent: $('#roomsModal'),
                                });



                                // Check if beds_count is 0 and show an error message
                                if (room.beds_count === 0) {

                                    swal({

                                        text: translations.cantHoused + ' ' +
                                            room.room_number + ' ' +
                                            translations.notAvailable,
                                        icon: 'error',
                                        button: 'OK',
                                    });
                                    $('#room_form').modal('hide');
                                }
                            });
                        }
                    });

                    $('#submitArrived').click(function() {

                        $.ajax({


                            url: $('#room_form').attr('action'),
                            type: 'post',
                            data: $('#room_form').serialize(),

                            success: function(response) {

                                console.log(response);
                                console.log($('#room_form').attr('action'));

                                $('#room_form').modal('hide');
                                reloadDataTable();
                            }
                        });
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

            $('#bulk_edit').submit(function(e) {

                e.preventDefault();

                var formData = $(this).serializeArray();
                console.log(formData);
                console.log($(this).attr('action'));
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: formData,
                    success: function(response) {

                        console.log(response);


                        $('#roomsModal').modal('hide');
                        reloadDataTable();
                    }
                });
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




        });

        function getCheckRecords() {
            var selectedRows = [];
            $(".selectedDiv").html("");

            $('.tblChk:checked').each(function() {
                if ($(this).prop('checked')) {
                    const rec = "<strong>" + $(this).attr("data-id") + " </strong>";
                    console.log(rec);
                    $(".selectedDiv").append(rec);
                    selectedRows.push($(this).attr("data-id"));

                    console.log(selectedRows);

                }

            });

            return selectedRows;
        }
    </script>
@endsection
