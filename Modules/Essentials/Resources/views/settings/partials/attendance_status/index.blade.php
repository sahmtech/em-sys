@extends('layouts.app')
@section('title', __('essentials::lang.attendance_status'))

@section('content')
    @include('essentials::layouts.nav_hrm_setting')

    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.attendance_status')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary" data-toggle="modal"
                        data-target="#addattendanceStatusModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot


            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="attendance_statuses_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('essentials::lang.status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
    <div class="modal fade" id="edit_attendance_status" tabindex="-1" role="dialog">

        @endcomponent





        <div class="modal fade" id="addattendanceStatusModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeAttendanceStatus']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_attendance_status')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('name', __('essentials::lang.status') . ':*') !!}
                                {!! Form::text('name', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('essentials::lang.status'),
                                    'required',
                                ]) !!}
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

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        // attendance_statuses table
        $(document).ready(function() {
            var attendance_statuses_table = $('#attendance_statuses_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('attendanceStatus') }}',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', 'button.delete_country_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_country,
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
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    attendance_statuses_table.ajax.reload();
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
