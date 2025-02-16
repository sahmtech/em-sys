@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.project_zones'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.project_zones')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="form-group col-md-3">
                        {!! Form::label('project_filter', __('operationsmanagmentgovernment::lang.project')) !!}
                        {!! Form::select('project_filter', $projects, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'project_filterSelect',
                        ]) !!}
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
                                data-target="#addZoneModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="zones_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.zone_name')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.contact')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <!-- Add Zone Modal -->
            <div class="modal fade" id="addZoneModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'operationsmanagmentgovernment.zone.store', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.add_project_zone')</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                {!! Form::label('contact_id', __('operationsmanagmentgovernment::lang.contact') . ':*') !!}
                                {!! Form::select('contact_id', $contacts, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('project_id', __('operationsmanagmentgovernment::lang.project') . ':') !!}
                                {!! Form::select('project_id', $projects, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.select'),
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('name', __('operationsmanagmentgovernment::lang.zone_name') . ':*') !!}
                                {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
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

            <!-- Edit Zone Modal -->
            <div class="modal fade" id="editZoneModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['id' => 'editZoneForm', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.edit_project_zone')</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="zone_id" name="zone_id">
                            <div class="form-group">
                                {!! Form::label('contact_id', __('operationsmanagmentgovernment::lang.contact') . ':*') !!}
                                {!! Form::select('contact_id', $contacts, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'id' => 'edit_contact_id',
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('project_id', __('operationsmanagmentgovernment::lang.project') . ':') !!}
                                {!! Form::select('project_id', $projects, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.select'),
                                    'id' => 'edit_project_id',
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('name', __('operationsmanagmentgovernment::lang.zone_name') . ':*') !!}
                                {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'edit_name', 'required']) !!}
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

            var zones_table = $('#zones_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('operationsmanagmentgovernment.zone') }}',
                    data: function(d) {
                        d.project_id = $('#project_filterSelect').val();
                    }
                },
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'contact'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#project_filterSelect').on('change', function() {
                zones_table.ajax.reload();
            });

            $(document).on('click', '.delete_zone', function() {
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
                            success: function(result) {
                                if (result.success) {
                                    zones_table.ajax.reload();
                                    toastr.success(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.edit_zone', function() {
                var zoneId = $(this).data('id');
                var url = '{{ route('operationsmanagmentgovernment.zone.edit', ':id') }}'.replace(':id',
                    zoneId);

                $.get(url, function(data) {
                    $('#zone_id').val(data.id);
                    $('#edit_contact_id').val(data.contact_id).trigger('change');
                    $('#edit_project_id').val(data.project_id).trigger('change');
                    $('#edit_name').val(data.name);
                    $('#editZoneModal').modal('show');
                });
            });
        });
    </script>
@endsection
