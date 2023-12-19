@extends('layouts.app')
@section('title', __('housingmovements::lang.cars'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.cars')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\Essentials\Http\Controllers\CarController@search'),
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
                                href="{{ action('Modules\Essentials\Http\Controllers\CarController@create') }}"
                                data-href="{{ action('Modules\Essentials\Http\Controllers\CarController@create') }}"
                                data-container="#add_car_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cars_table"
                            style="margin-bottom: 100px;table-layout: fixed !important;">
                            <thead>
                                <tr>
                                    {{-- <th>@lang('housingmovements::lang.driver')</th> --}}
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.car_typeModel')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.plate_number')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.plate_registration_type')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.serial_number')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.structure_no')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.manufacturing_year')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.vehicle_status')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.test_end_date')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.examination_status')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.number_seats')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.color')</th>
                                    <th style="width: 100px !important;">@lang('housingmovements::lang.insurance_status')</th>
                                    <th style="width: 100px !important;">@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $Cars->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_car_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_car_model" tabindex="-1" role="dialog">
                    </div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {


            $('#carTypeSelect').select2();
            $('#car_type_id').select2();
            $('#driver_select').select2();
            $('#carModel_id').select2();
            $('#worker_select').select2();

            cars_table = $('#cars_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.cars') }}',
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
                        "data": "car_typeModel"
                    },
                    {
                        "data": "plate_number"
                    },
                    {
                        "data": "plate_registration_type"
                    },
                    {
                        "data": "serial_number"
                    },
                    {
                        "data": "structure_no"
                    },
                    {
                        "data": "manufacturing_year"
                    },
                    {
                        "data": "vehicle_status"
                    },
                    {
                        "data": "test_end_date"
                    },
                    {
                        "data": "examination_status"
                    },
                    {
                        "data": "number_seats"
                    },
                    {
                        "data": "color"
                    },
                    {
                        "data": "insurance_status"
                    },
                    {
                        data: 'action'
                    }
                ]
            });

            $(document).on('click', 'button.delete_car_button', function() {

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
                            cars_table.ajax.reload();
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
            $('#carTypeSelect,#driver_select').on('change',
                function() {
                    cars_table.ajax.reload();
                });
            $(document).on('change', '#car_type_id', function() {
                if ($(this).val() !== '') {
                    $.ajax({
                        url: '/hrm/carModel-by-carType_id/' + $(this).val(),
                        dataType: 'json',
                        success: function(result) {
                            console.log(result);
                            $('#carModel_id')
                            $('#carModel_id').empty();
                            $.each(result, function(index, carModel) {
                                $('#carModel_id').append('<option value="' + carModel
                                    .id + '">' + carModel.name_ar + ' - ' + carModel
                                    .name_en + '</option>');
                            });

                        },
                    });
                }
            })

            $(document).on('change', '#car_type_id_select', function() {
                if ($(this).val() !== '') {
                    $.ajax({
                        url: '/essentials/carModel-by-carType_id/' + $(this).val(),
                        dataType: 'json',
                        success: function(result) {
                            console.log(result);
                            $('#carModel_id')
                            $('#carModel_id').empty();
                            $.each(result, function(index, carModel) {
                                $('#carModel_id').append('<option value="' + carModel
                                    .id + '">' + carModel.name_ar + ' - ' + carModel
                                    .name_en + '</option>');
                            });

                        },
                    });
                }
            })

        });
    </script>
@endsection
