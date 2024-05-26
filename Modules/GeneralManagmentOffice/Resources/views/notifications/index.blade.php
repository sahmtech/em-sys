@extends('layouts.app')
@section('title', __('generalmanagement::lang.notifications'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('generalmanagement::lang.view_notifications')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('employee_name_filter', __('sales::lang.contact_name') . ':') !!}
                            {!! Form::select('employee_name_filter', $contacts2, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'employee_name_filter',
                            ]) !!}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="notifications_table"
                    style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th class="table-td-width-25px">#</th>
                            <th class="table-td-width-100">@lang('generalmanagement::lang.notification_title')</th>
                            <th class="table-td-width-200px">@lang('generalmanagement::lang.notification_msg')</th>
                            <th class="table-td-width-100px">@lang('generalmanagement::lang.notification_recievers')</th>
                            <th class="table-td-width-100px">@lang('lang_v1.notification_read_at')</th>
                            <th class="table-td-width-100px">@lang('lang_v1.notification_created_at')</th>
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
            $('#notifications_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('office.notifications.index') }}",
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'msg'
                    },
                    {
                        data: 'to'
                    },
                    {
                        data: 'read_at'
                    },
                    {
                        data: 'created_at'
                    },
                ]

            });
        });
    </script>
@endsection
