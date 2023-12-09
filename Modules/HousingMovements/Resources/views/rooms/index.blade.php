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
                        {!! Form::select('htr_building_filter', $buildings, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
                @endif
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addRoomModal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endslot
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="rooms_table">
                        <thead>
                            <tr>
                                <th>@lang('housingmovements::lang.room_number')</th>
                                <th>@lang('housingmovements::lang.htr_building')</th>
                                <th>@lang('housingmovements::lang.area')</th>
                                <th>@lang('housingmovements::lang.beds_count')</th>
                                <th>@lang('housingmovements::lang.contents')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>

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
                                     $buildings, null, ['class' => 'form-control select2','style'=>'width:100%;height:40px;',   'multiple',
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

@endsection

@section('javascript')
<script type="text/javascript">
    var rooms_table;

    function reloadDataTable() {
        rooms_table.ajax.reload();
    }

    $(document).ready(function () {
        rooms_table = $('#rooms_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("rooms") }}',
                data: function (d) {
                    if ($('#htr_building_filter').length) {
                        d.htr_building = $('#htr_building_filter').val();
                    }
                }
            },
            columns: [
                { data: 'room_number' },
                { data: 'htr_building_id' },
                { data: 'area' },
                { data: 'beds_count' },
                { data: 'contents' },

                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#htr_building_filter').on('change', function () {
            reloadDataTable();
        });

        $(document).on('click', 'button.delete_room_button', function () {
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
                        success: function (result) {
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

</script>
@endsection
