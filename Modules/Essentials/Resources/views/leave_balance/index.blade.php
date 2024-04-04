{{-- @extends('layouts.app')

@section('title', __('essentials::lang.leave_balances'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.leave_balances')</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="leave_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.user')</th>
                            @foreach ($leaveTypes as $type)
                                <th>{{ $type->leave_type }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will fill tbody -->
                    </tbody>
                </table>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            @php
                use Illuminate\Support\Str;
            @endphp

            var columns = [{
                data: 'name',
                name: 'name',
                title: '@lang('essentials::lang.user')'
            }];

            @foreach ($leaveTypes as $type)
                columns.push({
                    data: '{{ Str::slug($type->leave_type, '_') }}',
                    name: '{{ Str::slug($type->leave_type, '_') }}',
                    title: '{{ $type->leave_type }}'
                });
            @endforeach

            $('#leave_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ action([\Modules\Essentials\Http\Controllers\UsersLeaveBalanceController::class, 'index']) }}",
                columns: columns
                // ... other DataTable options ...
            });
        });
    </script>
@endsection --}}
@extends('layouts.app')

@section('title', __('essentials::lang.leave_balances'))

@section('content')
    <section class="content-header">
        <h1>@lang('essentials::lang.leave_balances')</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="form-group">
                {!! Form::label('user_select', __('essentials::lang.name') . ':*') !!}
                {!! Form::select('user_select[]', $users, null, [
                    'class' => 'form-control select2',
                    'multiple',
                    'required',
                    'id' => 'user_select',
                    'style' => 'height: 60px; width: 1000px;',
                ]) !!}
            </div>
            <button id="fetch_leaves" class="btn btn-primary">@lang('essentials::lang.show_Leave_Balances')</button>
            <div id="leave_balances"></div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#user_select').select2();
            $('#fetch_leaves').on('click', function() {
                var selectedUsers = $('#user_select').val();
                $.ajax({
                    url: "{{ action([\Modules\Essentials\Http\Controllers\UsersLeaveBalanceController::class, 'index']) }}",
                    type: 'GET',
                    data: {
                        user_ids: selectedUsers
                    },
                    success: function(response) {
                        $('#leave_balances').empty();

                        if (response.data.length > 0) {
                            var table = $(
                                '<table class="table table-bordered table-striped"></table>'
                            );
                            var thead = $('<thead></thead>');
                            var headerRow = $('<tr></tr>');
                            headerRow.append('<th>Name</th>');

                            response.leaveTypes.forEach(function(leaveType) {
                                headerRow.append('<th>' + leaveType.leave_type +
                                    '</th>');
                            });
                            thead.append(headerRow);
                            table.append(thead);

                            var tbody = $('<tbody></tbody>');
                            response.data.forEach(function(user) {
                                var row = $('<tr></tr>');
                                row.append('<td>' + user.name + '</td>');

                                response.leaveTypes.forEach(function(leaveType) {
                                    var leaveBalance = user.leave_balances.find(
                                        function(balance) {
                                            return balance
                                                .essentials_leave_type_id ===
                                                leaveType.id;
                                        });
                                    var amount = leaveBalance ? leaveBalance
                                        .amount : '-';
                                    row.append('<td>' + amount + '</td>');
                                });
                                tbody.append(row);
                            });
                            table.append(tbody);

                            $('#leave_balances').append(table);
                        } else {
                            $('#leave_balances').append(
                                '<p>No leave balances found for the selected users.</p>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error: ', textStatus, errorThrown);
                        $('#leave_balances').append('<p>Error fetching leave balances.</p>');
                    }


                });
            });


        });
    </script>
@endsection
