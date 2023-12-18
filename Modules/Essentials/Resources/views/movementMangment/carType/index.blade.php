@extends('layouts.app')
@section('title', __('housingmovements::lang.carTypes'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.carTypes')</span>
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
                                href="{{ action('Modules\Essentials\Http\Controllers\CarTypeController@create') }}"
                                data-href="{{ action('Modules\Essentials\Http\Controllers\CarTypeController@create') }}"
                                data-container="#create_account_modal">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carTypes_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_ar')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_en')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $carTypes->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="create_account_modal" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_car_type_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#car__type_id').select2();

            carTypes_table = $('#carTypes_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.car-types') }}',
                    data: function(d) {
                        if ($('#name').val()) {
                            d.name = $('#name').val();
                            // console.log(d.project_name_filter);
                        }
                    }
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": "name_ar"
                    },
                    {
                        "data": "name_en"
                    },
                    {
                        data: 'action'
                    }
                ]
            });
            $(document).on('click', 'button.delete_carType_button', function() {

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
                            carTypes_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_carType_button', function() {

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
            $('#name').on('change',
                function() {
                    carTypes_table.ajax.reload();
                });
        });
    </script>
@endsection
