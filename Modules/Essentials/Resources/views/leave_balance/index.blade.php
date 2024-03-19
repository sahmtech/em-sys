@extends('layouts.app')

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
@endsection
