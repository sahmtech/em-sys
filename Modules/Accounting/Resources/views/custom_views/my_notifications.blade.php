@extends('layouts.app')
@section('title', __('lang_v1.all_notifications'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('lang_v1.all_notifications')</span>
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
                            <th class="table-td-width-100px">@lang('lang_v1.notification_sender')</th>
                            <th class="table-td-width-100px">@lang('lang_v1.notification_title')</th>
                            <th class="table-td-width-200px">@lang('lang_v1.notification_msg')</th>
                            <th class="table-td-width-100px">@lang('lang_v1.notification_read_at')</th>
                            <th class="table-td-width-100px">@lang('lang_v1.notification_created_at')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->
    <div class="modal fade" id="notification_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h3 id="notification_modal_title" class="modal-title"></h3>
                </div>
    
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 style=" white-space: pre-wrap;" id="notification_modal_msg"></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#notifications_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('getMyNotification') }}",
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'sender'
                    },
                    
                    {
                        data: 'title'
                    },
                    {
                        data: 'msg'
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
        $('#notifications_table tbody').on('click', 'tr', function () {
            alert("asdasd");
            var data = table.row(this).data();

            // Set the values in the modal
            $('#notification_modal_title').text(data.title);
            $('#notification_modal_msg').text(data.msg);

            // Show the modal
            $('#notification_modal').modal('show');
        });
    </script>
@endsection
