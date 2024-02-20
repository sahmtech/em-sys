@extends('layouts.app')
@section('title', __('sales::lang.templates_settings'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('sales::lang.templates_settings')
            {{-- <small>@lang('user.manage_users')</small> --}}
        </h1>
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('user_type_filter', __('followup::lang.user_type') . ':') !!}

                            <select class="form-control" name="user_type_filter" id='user_type_filter' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($users_fillter as $users_fillter)
                                    <option value="{{ $users_fillter->user_type }}"> @lang('followup::lang.' . $users_fillter->user_type)</option>
                                @endforeach

                            </select>

                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href=" {{ route('templates.create') }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="templates_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('sales::lang.template_name')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        //Roles table
        $(document).ready(function() {

            var users_table = $('#templates_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('templates.index') }}',
                },

                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'action'
                    }
                ]
            });


            $(document).on('click', 'button.delete_template_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_user,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    users_table.ajax.reload();
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
