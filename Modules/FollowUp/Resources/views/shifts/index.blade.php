@extends('layouts.app')
@section('title', __('housingmovements::lang.shifts'))

@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.shifts')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\FollowUp\Http\Controllers\ShiftController@create') }}"
                                data-href="{{ action('Modules\FollowUp\Http\Controllers\ShiftController@create') }}"
                                data-container="#add_shits_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="shifts_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                <tr>
                                    <th style="text-align: center;">@lang('lang_v1.name')</th>
                                    {{-- <th style="text-align: center;">@lang('essentials::lang.shift_type')</th> --}}
                                    <th style="text-align: center;">@lang('restaurant.start_time')</th>
                                    <th style="text-align: center;">@lang('restaurant.end_time')</th>
                                    <th style="text-align: center;">@lang('essentials::lang.holiday')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.project_name')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>

                            </thead>

                        </table>

                        {{-- <center class="mt-5">
                            {{ $shifts->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_shits_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_shits_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection


@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#holidays').select2();
            shifts_table = $('#shifts_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('shifts') }}',
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },

                    {
                        "data": "name"
                    },
                    {
                        "data": "start_time"
                    },
                    {
                        "data": "end_time"
                    },
                    {
                        "data": "holiday"
                    },
                     {
                        "data": "project_name"
                    },
                    {
                        data: 'action'
                    }
                ]
            });

            $(document).on('click', 'button.delete_shift_button', function() {

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
                            shifts_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_car_button', function() {

                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);

                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });

        });
    </script>
@endsection
