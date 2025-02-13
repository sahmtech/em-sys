@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.water_weights'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.water_weights')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="form-group col-md-3">
                        {!! Form::label('weight_type_filter', __('operationsmanagmentgovernment::lang.weight_type')) !!}
                        {!! Form::select(
                            'weight_type_filter',
                            [
                                '6_tons' => __('operationsmanagmentgovernment::lang.6_tons'),
                                '18_tons' => __('operationsmanagmentgovernment::lang.18_tons'),
                                '34_tons' => __('operationsmanagmentgovernment::lang.34_tons'),
                            ],
                            null,
                            [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'weight_type_filterSelect',
                            ],
                        ) !!}
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal"
                                data-target="#addWaterWeightModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="water_weights_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.company')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.driver')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.plate_number')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.weight_type')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.water_droping_location')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.sample_result')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.date')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.created_by')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <!-- Add Water Weight Modal -->
            <div class="modal fade" id="addWaterWeightModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'operationsmanagmentgovernment.water_weight.store', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.add_water_weight')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">

                                <div class="form-group col-md-6">
                                    {!! Form::label('project_id', __('operationsmanagmentgovernment::lang.project') . ':*') !!}
                                    {!! Form::select('project_id', $projects, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'project_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('driver', __('operationsmanagmentgovernment::lang.driver') . ':*') !!}
                                    {!! Form::text('driver', null, [
                                        'class' => 'form-control',
                                        'id' => 'edit_driver',
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('plate_number', __('operationsmanagmentgovernment::lang.plate_number') . ':') !!}
                                    {!! Form::text('plate_number', null, ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label(
                                        'water_droping_location',
                                        __('operationsmanagmentgovernment::lang.water_droping_location') . ':',
                                    ) !!}
                                    {!! Form::text('water_droping_location', null, ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('weight_type', __('operationsmanagmentgovernment::lang.weight_type') . ':*') !!}
                                    {!! Form::select('weight_type', ['6_tons' => '6 Tons', '18_tons' => '18 Tons', '34_tons' => '34 Tons'], null, [
                                        'class' => 'form-control select2',
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('sample_result', __('operationsmanagmentgovernment::lang.sample_result') . ':') !!}
                                    {!! Form::text('sample_result', null, ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('date', __('operationsmanagmentgovernment::lang.date') . ':*') !!}
                                    {!! Form::date('date', null, ['class' => 'form-control', 'required']) !!}
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

            <!-- Edit Water Weight Modal -->
            <div class="modal fade" id="editWaterWeightModal" tabindex="-1" role="dialog"
                aria-labelledby="editModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['id' => 'editWaterWeightForm', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.edit_water_weight')</h4>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" id="water_weight_id" name="water_weight_id">
                            <div class="row">


                                <div class="form-group col-md-6">
                                    {!! Form::label('project_id', __('operationsmanagmentgovernment::lang.project') . ':*') !!}
                                    {!! Form::select('project_id', $projects, null, [
                                        'class' => 'form-control select2',
                                        'required',
                                        'id' => 'edit_project_id',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('driver', __('operationsmanagmentgovernment::lang.driver') . ':*') !!}
                                    {!! Form::text('driver', null, [
                                        'class' => 'form-control',
                                        'id' => 'edit_driver',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('plate_number', __('operationsmanagmentgovernment::lang.plate_number') . ':') !!}
                                    {!! Form::text('plate_number', null, ['class' => 'form-control', 'id' => 'edit_plate_number']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label(
                                        'water_droping_location',
                                        __('operationsmanagmentgovernment::lang.water_droping_location') . ':',
                                    ) !!}
                                    {!! Form::text('water_droping_location', null, [
                                        'class' => 'form-control',
                                        'id' => 'edit_water_droping_location',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('weight_type', __('operationsmanagmentgovernment::lang.weight_type') . ':*') !!}
                                    {!! Form::select('weight_type', ['6_tons' => '6 Tons', '18_tons' => '18 Tons', '34_tons' => '34 Tons'], null, [
                                        'class' => 'form-control select2',
                                        'required',
                                        'id' => 'edit_weight_type',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('sample_result', __('operationsmanagmentgovernment::lang.sample_result') . ':') !!}
                                    {!! Form::text('sample_result', null, ['class' => 'form-control', 'id' => 'edit_sample_result']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('date', __('operationsmanagmentgovernment::lang.date') . ':*') !!}
                                    {!! Form::date('date', null, ['class' => 'form-control', 'required', 'id' => 'edit_date']) !!}
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
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            $(document).on('click', '.open-edit-modal', function() {
                var waterWeightId = $(this).data('id');
                var url = '{{ route('operationsmanagmentgovernment.water_weight.edit', ':id') }}'.replace(
                    ':id', waterWeightId);

                $.get(url, function(data) {
                    $('#water_weight_id').val(data.id);
                    $('#edit_company_id').val(data.company_id).trigger('change');
                    $('#edit_project_id').val(data.project_id).trigger('change');
                    $('#editWaterWeightModal').modal('show');
                });
            });

            $('#editWaterWeightForm').submit(function(e) {
                e.preventDefault();
                var waterWeightId = $('#water_weight_id').val();
                var updateUrl = '{{ route('operationsmanagmentgovernment.water_weight.update', ':id') }}'
                    .replace(':id', waterWeightId);

                $.ajax({
                    url: updateUrl,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#editWaterWeightModal').modal('hide');
                        water_weights_table.ajax.reload();
                        toastr.success(response.msg);
                    }
                });
            });
            var water_weights_table = $('#water_weights_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('operationsmanagmentgovernment.water') }}',
                    data: function(d) {
                        d.company_id = $('#company_filterSelect').val();
                        d.driver_id = $('#driver_filterSelect').val();
                        d.weight_type = $('#weight_type_filterSelect').val();
                    }
                },
                columns: [{
                        data: 'company'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'driver'
                    },
                    {
                        data: 'plate_number'
                    },
                    {
                        data: 'weight_type'
                    },
                    {
                        data: 'water_droping_location'
                    },
                    {
                        data: 'sample_result'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#company_filterSelect, #driver_filterSelect, #weight_type_filterSelect').on('change', function() {
                water_weights_table.ajax.reload();
            });

            // Open Edit Modal
            $(document).on('click', '.open-edit-modal', function() {
                var waterWeightId = $(this).data('id');
                var url = '{{ route('operationsmanagmentgovernment.water_weight.edit', ':id') }}'.replace(
                    ':id', waterWeightId);

                $.get(url, function(data) {
                    $('#water_weight_id').val(data.id);
                    $('#edit_company_id').val(data.company_id).trigger('change');
                    $('#edit_project_id').val(data.project_id).trigger('change');
                    $('#edit_driver_id').val(data.driver_id).trigger('change');
                    $('#edit_plate_number').val(data.plate_number);
                    $('#edit_water_droping_location').val(data.water_droping_location);
                    $('#edit_weight_type').val(data.weight_type).trigger('change');
                    $('#edit_sample_result').val(data.sample_result);
                    $('#edit_date').val(data.date);
                    $('#editWaterWeightModal').modal('show');
                });
            });

            // Submit Edit Form
            $('#editWaterWeightForm').submit(function(e) {
                e.preventDefault();
                var waterWeightId = $('#water_weight_id').val();
                var updateUrl = '{{ route('operationsmanagmentgovernment.water_weight.update', ':id') }}'
                    .replace(':id', waterWeightId);

                $.ajax({
                    url: updateUrl,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#editWaterWeightModal').modal('hide');
                        water_weights_table.ajax.reload();
                        toastr.success(response.msg);
                    }
                });
            });
            $(document).on('click', '.delete_water_weight_button', function() {
                var href = $(this).data('href');

                swal({
                    title: "{{ __('messages.are_you_sure') }}",
                    text: "{{ __('messages.confirm_delete') }}",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    water_weights_table.ajax.reload();
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
