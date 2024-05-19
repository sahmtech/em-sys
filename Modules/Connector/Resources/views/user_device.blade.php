@extends('layouts.app')
@section('title', __('connector::lang.user_device'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('connector::lang.user_device')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="employees_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-100px">@lang('followup::lang.emp_name')</th>
                            <th class="table-td-width-60px">@lang('followup::lang.emp_id_proof_number')</th>
                            <th class="table-td-width-100px">@lang('connector::lang.device_name')</th>
                            <th class="table-td-width-100px">@lang('connector::lang.device_number')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#employees_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('user_device') }}",
                },
                columns: [{
                        data: 'full_name'
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'device_name'
                    },
                    {
                        data: 'device_number'
                    },
                ]

            });
        });
    </script>
@endsection
