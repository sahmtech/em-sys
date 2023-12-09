@extends('layouts.app')
@section('title', __('housingmovements::lang.buildings'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.buildings')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                @if (!empty($cities))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('city_filter', __('housingmovements::lang.city') . ':') !!}
                        {!! Form::select('city_filter', $cities, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
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
                        <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addBuildingModal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endslot
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="buildings_table">
                        <thead>
                            <tr>
                                <th>@lang('housingmovements::lang.building_name')</th>
                                <th>@lang('housingmovements::lang.address')</th>
                                <th>@lang('housingmovements::lang.city')</th>
                                <th>@lang('housingmovements::lang.building_guard')</th>
                                <th>@lang('housingmovements::lang.building_supervisor')</th>
                                <th>@lang('housingmovements::lang.building_cleaner')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>

        <div class="modal fade building_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        <div class="modal fade" id="addBuildingModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeBuilding']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('housingmovements::lang.add_building')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('name', __('housingmovements::lang.building_name') . ':*') !!}
                                {!! Form::text('name', null,
                                     ['class' => 'form-control ',
                                      'placeholder' => __('housingmovements::lang.building_name'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('address', __('housingmovements::lang.address') . ':') !!}
                                {!! Form::text('address', null,
                                     ['class' => 'form-control',
                                      'placeholder' => __('housingmovements::lang.address'),'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('city', __('housingmovements::lang.city') . ':*') !!}
                                {!! Form::select('city', $cities, null,
                                     ['class' => 'form-control', 'placeholder' => __('housingmovements::lang.city'), 'required']) !!}
                            </div>
        
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('guard', __('housingmovements::lang.building_guard') . ':*') !!}
                                {!! Form::select('guard', $users2, null,
                                     ['class' => 'form-control select2','style'=>'width:100%;height:40px;',   'multiple',
                                      'placeholder' => __('housingmovements::lang.building_guard'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('supervisor', __('housingmovements::lang.building_supervisor') . ':*') !!}
                                {!! Form::select('supervisor', $users2, null,
                                     ['class' => 'form-control select2','style'=>'width:100%;height:40px;',   'multiple',
                                      'placeholder' => __('housingmovements::lang.building_supervisor'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('cleaner', __('housingmovements::lang.building_cleaner') . ':*') !!}
                                {!! Form::select('cleaner', $users2, null, ['class' => 'form-control select2','style'=>'width:100%;height:40px;',   'multiple',
                                     'placeholder' => __('housingmovements::lang.building_cleaner'), 'required']) !!}
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
    var buildings_table;

    function reloadDataTable() {
        buildings_table.ajax.reload();
    }

    $(document).ready(function () {
        buildings_table = $('#buildings_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("buildings") }}',
                data: function (d) {
                    if ($('#city_filter').length) {
                        d.city = $('#city_filter').val();
                    }
                }
            },
            columns: [
                { data: 'name' },
                { data: 'address' },
                { data: 'city_id' },
                { data: 'guard_id' },
                { data: 'supervisor_id' },
                { data: 'cleaner_id' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#city_filter').on('change', function () {
            reloadDataTable();
        });

        $(document).on('click', 'button.delete_building_button', function () {
             swal({
                title: LANG.sure,
                text: LANG.confirm_delete_building,
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
                                buildings_table.ajax.reload();
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
