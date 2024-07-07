@extends('layouts.app')
@section('title', __('essentials::lang.users_shifts'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.users_shifts')</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal"
                                data-target="#addUserShiftModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="users_shifts_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee_number')</th>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.Identity_proof_id')</th>
                                    <th>@lang('essentials::lang.shift_name')</th>
                                    <th>@lang('essentials::lang.shift_type')</th>
                                    <th>@lang('essentials::lang.start_date')</th>
                                    <th>@lang('essentials::lang.end_date')</th>
                                    <th>@lang('essentials::lang.start_time')</th>
                                    <th>@lang('essentials::lang.end_time')</th>
                                    <th>@lang('essentials::lang.status')</th>
                                    <th>@lang('essentials::lang.actions')</th>
                                </tr>
                            </thead>
                        </table>
                    @endcomponent
                </div>
            </div>
        </div>
    </section>

    <!-- Add User Shift Modal -->
    <div class="modal fade" id="addUserShiftModal" tabindex="-1" role="dialog" aria-labelledby="addUserShiftModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="addUserShiftForm" method="POST" action="{{ route('storeUserShift') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserShiftModalLabel">@lang('essentials::lang.add_user_shift')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">@lang('essentials::lang.select_user')</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                @foreach ($users as $id => $full_name)
                                    <option value="{{ $id }}">{{ $full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="shift_id">@lang('essentials::lang.select_shift')</label>
                            <div class='col-md-12'>
                                <select class="form-control select2" id="shift_id" name="shift_id[]" multiple required>
                                    <option value="" disabled selected>@lang('essentials::lang.select_shift')</option>
                                    @foreach ($shifts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_date">@lang('essentials::lang.start_date')</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">@lang('essentials::lang.end_date')</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Shift Modal -->
    <div class="modal fade" id="editUserShiftModal" tabindex="-1" role="dialog" aria-labelledby="editUserShiftModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editUserShiftForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserShiftModalLabel">@lang('essentials::lang.edit_user_shift')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_user_id">@lang('essentials::lang.select_user')</label>
                            <select class="form-control" id="edit_user_id" name="user_id" required style="height: 40px;">
                                @foreach ($users as $id => $full_name)
                                    <option value="{{ $id }}">{{ $full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_shift_id">@lang('essentials::lang.select_shift')</label>
                            <select class="form-control" id="edit_shift_id" name="shift_id" required style="height: 40px;">
                                @foreach ($shifts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_start_date">@lang('essentials::lang.start_date')</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_end_date">@lang('essentials::lang.end_date')</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var users_shifts_table = $('#users_shifts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users_shifts') }}",
                },
                columns: [{
                        data: 'emp_number',
                        name: 'u.emp_number'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'id_proof_number',
                        name: 'u.id_proof_number'
                    },
                    {
                        data: 'name',
                        name: 'shift.name'
                    },
                    {
                        data: 'type',
                        name: 'shift.type'
                    },
                    {
                        data: 'start_date',
                        name: 'essentials_user_shifts.start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'essentials_user_shifts.end_date'
                    },
                    {
                        data: 'start_time',
                        name: 'shift.start_time'
                    },
                    {
                        data: 'end_time',
                        name: 'shift.end_time'
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, row) {
                            if (data === 1) {
                                return '@lang('essentials::lang.valid')';
                            } else if (data === 0) {
                                return '@lang('essentials::lang.canceled')';
                            } else {
                                return " ";
                            }
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-xs btn-primary edit-btn" data-id="${data}" data-toggle="modal" data-target="#editUserShiftModal">
                                    @lang('messages.edit')
                                </button>
                                <button class="btn btn-xs btn-danger delete_button" data-href="/employee_affairs/users_shifts/${data}">
                                    @lang('messages.delete')
                                </button>
                            `;
                        }
                    },
                ],
                order: [
                    [0, 'desc']
                ]
            });

            $('#addUserShiftForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#addUserShiftModal').modal('hide');
                        users_shifts_table.ajax.reload();
                        toastr.success(response.message);
                    },
                    error: function(xhr) {
                        toastr.error('Error: ' + xhr.responseText);
                    }
                });
            });

            $('#users_shifts_table').on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: `/employee_affairs/users_shifts/${id}/edit`,
                    type: 'GET',
                    success: function(data) {
                        $('#editUserShiftForm').attr('action',
                            `/employee_affairs/users_shifts/${id}`);
                        $('#edit_user_id').val(data.user_id);
                        $('#edit_shift_id').val(data.essentials_shift_id);
                        $('#edit_start_date').val(data.start_date);
                        $('#edit_end_date').val(data.end_date);
                    },
                    error: function(xhr) {
                        toastr.error('Error: ' + xhr.responseText);
                    }
                });
            });

            $('#editUserShiftForm').on('submit', function(e) {
                e.preventDefault();

                var url = $(this).attr('action');
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#editUserShiftModal').modal('hide');
                        users_shifts_table.ajax.reload();
                        toastr.success(response.message);
                    },
                    error: function(xhr) {
                        toastr.error('Error: ' + xhr.responseText);
                    }
                });
            });

            $(document).on('click', 'button.delete_button', function() {
                swal({
                    title: "@lang('messages.are_you_sure')",
                    text: "@lang('messages.confirm_delete')",
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
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    users_shifts_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Error: ' + xhr.responseText);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
