@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.permissions'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.permissions')</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contacts_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('lang_v1.name')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <!-- Permissions Modal -->
        <div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['id' => 'permissionsForm', 'method' => 'PUT']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('operationsmanagmentgovernment::lang.permissions')</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="contact_id" id="contact_id">
                        <div class="form-group">
                            <div id="activities_list"></div> <!-- Activities checkboxes will be inserted here -->
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
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var contacts_table = $('#contacts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('operationsmanagmentgovernment.permissions') }}",
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.open-permissions-modal', function() {
                var contactId = $(this).data('id');
                var url = $(this).data('url');

                $.get(url, function(data) {
                    $('#contact_id').val(contactId);
                    var activitiesHtml = '';

                    // Iterate through all activities and create checkboxes
                    $.each(data.all_activities, function(id, name) {
                        var checked = data.permissions.includes(parseInt(id)) ? 'checked' :
                            '';
                        activitiesHtml += `
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="activities[]" value="${id}" ${checked}> ${name}
                                </label>
                            </div>
                        `;
                    });

                    $('#activities_list').html(activitiesHtml);
                    $('#permissionsModal').modal('show');
                });
            });

            $('#permissionsForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var contactId = $('#contact_id').val();
                var updateUrl = "{{ route('operationsmanagmentgovernment.permissions.update', ':id') }}"
                    .replace(':id', contactId);

                $.ajax({
                    url: updateUrl,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        $('#permissionsModal').modal('hide');
                        contacts_table.ajax.reload();
                        toastr.success(response.msg);
                    }
                });
            });
        });
    </script>
@endsection
