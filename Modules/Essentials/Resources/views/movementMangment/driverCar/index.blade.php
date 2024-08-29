@extends('layouts.app')
@section('title', __('housingmovements::lang.car_drivers'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.car_drivers')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\Essentials\Http\Controllers\DriverCarController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="row">
                        <div class="col-sm-4">
                            {!! Form::label('carType_label', __('housingmovements::lang.carModel')) !!}

                            <select class="form-control" name="car_type_id" id='carTypeSelect' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($carTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->name_ar . ' - ' . $type->name_en }}</option>
                                @endforeach
                            </select>

                        </div>

                        <div class="col-sm-4" style="margin-top: 0px;">
                            {!! Form::label('driver', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                            <select class="form-control " name="driver" id="driver_select" style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($car_Drivers as $driver)
                                    <option value="{{ $driver->user_id }}">
                                        {{ $driver->user->id_proof_number . ' - ' . $driver->user->first_name . ' ' . $driver->user->last_name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\Essentials\Http\Controllers\DriverCarController@create') }}"
                                data-href="{{ action('Modules\Essentials\Http\Controllers\DriverCarController@create') }}"
                                data-container="#add_car_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carDrivers_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>

                                    <th>@lang('housingmovements::lang.driver')</th>
                                    <th>@lang('housingmovements::lang.car_typeModel')</th>
                                    <th>@lang('housingmovements::lang.counter_number')</th>
                                    <th>@lang('housingmovements::lang.delivery_date')</th>
                                    <th>@lang('housingmovements::lang.plate_number')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $Cars->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_car_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_driver_model" tabindex="-1" role="dialog">
                    </div>
                @endcomponent
            </div>
            @include('essentials::movementMangment.driverCar.delete')

    </section>
    <!-- /.content -->

@endsection

@section('javascript')


    <script type="text/javascript">
        // Your existing JavaScript code for other features
        $(document).ready(function() {


            $(document).on('click', '.delete_user_button', function() {
                const driverCarId = $(this).data('id'); // Get the ID from the button's data attribute
                const deleteUrl = '{{ url('movment/cardrivers-delete') }}/' +
                    driverCarId; // Construct the delete URL

                // Set the form action to the delete URL with the ID
                $('#delete_driver_form').attr('action', deleteUrl);

                // Open the modal
                $('#delete_confirmation_modal').modal('show');
            });


            $('#carTypeSelect').select2();
            $('#driver_select').select2();


            carDrivers_table = $('#carDrivers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.cardrivers') }}',
                    data: function(d) {
                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                            // console.log(d.project_name_filter);
                        }
                        if ($('#driver_select').val()) {
                            d.driver_select = $('#driver_select').val();
                            // console.log(d.project_name_filter);
                        }
                    }
                },
                columns: [
                    // { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": "driver"
                    },
                    {
                        "data": "car_typeModel"
                    },
                    {
                        "data": "counter_number"
                    },
                    {
                        "data": "delivery_date"
                    },
                    {
                        "data": "plate_number"
                    },
                    {
                        data: 'action'
                    }
                ]
            });
            $(document).on('click', 'button.delete_user_button', function() {

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
                            carDrivers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_user_button', function() {

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
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $('#carTypeSelect,#driver_select').on('change',
                function() {
                    carDrivers_table.ajax.reload();
                });

        });
    </script>
@endsection
