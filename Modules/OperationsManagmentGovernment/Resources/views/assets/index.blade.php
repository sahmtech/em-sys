@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.asset_assessment'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.asset_assessment')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="form-group col-md-3">
                        {!! Form::label('zone_filter', __('operationsmanagmentgovernment::lang.zone')) !!}
                        {!! Form::select('zone_filter', $zones, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'id' => 'zone_filterSelect',
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
                                data-target="#addAssetModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="asset_assessment_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.contact')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.zone')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.asset')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.quantity')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            <!-- Add Asset Modal -->
            <div class="modal fade" id="addAssetModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['route' => 'operationsmanagmentgovernment.asset_assessment.store', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.add_asset')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('contact_id', __('operationsmanagmentgovernment::lang.contact') . ':*') !!}
                                    {!! Form::select('contact_id', $contacts, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'contact_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('project_id', __('operationsmanagmentgovernment::lang.project') . ':*') !!}
                                    {!! Form::select('project_id', [], null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'project_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('zone_id', __('operationsmanagmentgovernment::lang.zone') . ':*') !!}
                                    {!! Form::select('zone_id', [], null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'zone_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('asset', __('operationsmanagmentgovernment::lang.asset') . ':*') !!}
                                    {!! Form::text('asset', null, ['class' => 'form-control', 'required']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('quantity', __('operationsmanagmentgovernment::lang.quantity') . ':*') !!}
                                    {!! Form::number('quantity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
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

            <!-- Edit Asset Modal -->
            <div class="modal fade" id="editAssetModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open(['id' => 'editAssetForm', 'method' => 'POST']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.edit_asset')</h4>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" id="asset_id" name="asset_id">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_contact_id', __('operationsmanagmentgovernment::lang.contact') . ':*') !!}
                                    {!! Form::select('edit_contact_id', $contacts, null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'edit_contact_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_project_id', __('operationsmanagmentgovernment::lang.project') . ':*') !!}
                                    {!! Form::select('edit_project_id', [], null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'edit_project_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_zone_id', __('operationsmanagmentgovernment::lang.zone') . ':*') !!}
                                    {!! Form::select('edit_zone_id', [], null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.select'),
                                        'required',
                                        'id' => 'edit_zone_select',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_asset', __('operationsmanagmentgovernment::lang.asset') . ':*') !!}
                                    {!! Form::text('edit_asset', null, ['class' => 'form-control', 'required']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('edit_quantity', __('operationsmanagmentgovernment::lang.quantity') . ':*') !!}
                                    {!! Form::number('edit_quantity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
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

            var asset_assessment_table = $('#asset_assessment_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('operationsmanagmentgovernment.asset_assessment') }}",
                columns: [{
                        data: 'contact'
                    },
                    {
                        data: 'project'
                    }, {
                        data: 'zone'
                    },
                    {
                        data: 'asset'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Function to fetch projects based on contact selection
            function fetchProjects(contactId, projectSelectId, selectedProjectId = null, zoneSelectId = null,
                selectedZoneId = null) {
                if (contactId) {
                    $.ajax({
                        url: "{{ url('operationsmanagmentgovernment/getProjectsFromContact') }}/" +
                            contactId,
                        type: "GET",
                        success: function(response) {
                            $(projectSelectId).empty().append(
                                '<option value="">{{ __('messages.select') }}</option>');

                            $.each(response, function(id, name) {
                                $(projectSelectId).append('<option value="' + id + '">' + name +
                                    '</option>');
                            });

                            if (selectedProjectId) {
                                $(projectSelectId).val(selectedProjectId).trigger('change');

                                setTimeout(function() {
                                    fetchZones(selectedProjectId, zoneSelectId, selectedZoneId);
                                }, 500);
                            }
                        }
                    });
                }
            }

            // Function to fetch zones based on project selection
            function fetchZones(projectId, zoneSelectId, selectedZoneId = null) {
                if (projectId) {
                    $.ajax({
                        url: "{{ url('operationsmanagmentgovernment/getZonesFromProjects') }}/" + projectId,
                        type: "GET",
                        success: function(response) {
                            $(zoneSelectId).empty().append(
                                '<option value="">{{ __('messages.select') }}</option>');

                            $.each(response, function(id, name) {
                                $(zoneSelectId).append('<option value="' + id + '">' + name +
                                    '</option>');
                            });

                            if (selectedZoneId) {
                                $(zoneSelectId).val(selectedZoneId).trigger('change');
                            }
                        }
                    });
                }
            }

            // Fetch Projects when Contact is Selected (Add Modal)
            $('#contact_select').on('change', function() {
                var contactId = $(this).val();
                fetchProjects(contactId, '#project_select');
            });

            // Fetch Zones when Project is Selected (Add Modal)
            $('#project_select').on('change', function() {
                var projectId = $(this).val();
                fetchZones(projectId, '#zone_select');
            });

            // Open Edit Modal and Fetch Data
            $(document).on('click', '.open-edit-modal', function() {
                var assetId = $(this).data('id');
                var url = '{{ route('operationsmanagmentgovernment.asset_assessment.edit', ':id') }}'
                    .replace(':id', assetId);

                $.get(url, function(data) {
                    $('#asset_id').val(data.id);
                    $('#edit_asset').val(data.asset);
                    $('#edit_quantity').val(data.quantity);

                    // Select contact and fetch projects
                    $('#edit_contact_select').val(data.contact_id).trigger('change');

                    // Wait for Contact to be selected before fetching projects
                    setTimeout(function() {
                        fetchProjects(data.contact_id, '#edit_project_select', data
                            .project_id, '#edit_zone_select', data.zone_id);
                    }, 500);

                    $('#editAssetModal').modal('show');
                }).fail(function(xhr) {
                    console.error("Error fetching asset details:", xhr);
                });
            });

            // Fetch Projects when Contact is Changed in Edit Modal
            $('#edit_contact_select').on('change', function() {
                var contactId = $(this).val();
                fetchProjects(contactId, '#edit_project_select');
            });

            // Fetch Zones when Project is Changed in Edit Modal
            $('#edit_project_select').on('change', function() {
                var projectId = $(this).val();
                fetchZones(projectId, '#edit_zone_select');
            });

            // Submit Edit Form
            $('#editAssetForm').submit(function(e) {
                e.preventDefault();
                var assetId = $('#asset_id').val();
                var updateUrl =
                    '{{ route('operationsmanagmentgovernment.asset_assessment.update', ':id') }}'.replace(
                        ':id', assetId);

                $.ajax({
                    url: updateUrl,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#editAssetModal').modal('hide');
                        asset_assessment_table.ajax.reload();
                        toastr.success(response.msg);
                    },
                    error: function(xhr) {
                        console.error("Error updating asset:", xhr);
                    }
                });
            });

            // Delete Asset
            $(document).on('click', '.delete-asset-button', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).data('href');

                swal({
                    title: "{{ __('messages.are_you_sure') }}",
                    text: "{{ __('messages.confirm_delete') }}",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: deleteUrl,
                            method: 'DELETE',
                            success: function(response) {
                                asset_assessment_table.ajax.reload();
                                toastr.success(response.msg);
                            },
                            error: function(xhr) {
                                console.error("Error deleting asset:", xhr);
                            }
                        });
                    }
                });
            });

            // Reset dropdowns when closing Add Modal
            $('#addAssetModal').on('hidden.bs.modal', function() {
                $('#contact_select, #project_select, #zone_select').val(null).trigger('change');
            });
        });
    </script>
@endsection
