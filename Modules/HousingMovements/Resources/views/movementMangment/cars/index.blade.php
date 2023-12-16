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
                        'url' => action('\Modules\HousingMovements\Http\Controllers\CarController@search'),
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
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">
                                        {{ $driver->id_proof_number . ' - ' . $driver->first_name . ' ' . $driver->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- <input type="text" id="searchWorkerInput" placeholder="Search Worker"
                                style="margin-top: 5px;"> --}}
                        </div>
                    </div>
                    {{-- <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group ">
                                {!! Form::label('search_lable', __('housingmovements::lang.driverName') . '  ') !!}
                                {!! Form::text('search', '', [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.driverName'),
                                    'id' => 'search',
                                ]) !!}

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group ">
                                {!! Form::label('search_lable', __('housingmovements::lang.plate_number') . '  ') !!}
                                {!! Form::text('search_plate_number', '', [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.plate_number'),
                                    'id' => 'search_plate_number',
                                ]) !!}

                            </div>
                        </div>
                    </div> --}}
                    {{-- <div class="col-md-12">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            @lang('housingmovements::lang.search')</button>
                        @if ($after_serch)
                            <a class="btn btn-primary pull-right m-5 "
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@index') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@index') }}">
                                @lang('housingmovements::lang.viewAll')</a>
                        @endif
                    </div> --}}
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
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@create') }}"
                                data-container="#add_car_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cars_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th>@lang('housingmovements::lang.driver')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.car_typeModel')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.plate_number')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.number_seats')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.color')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
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
                    url: '{{ route('cars') }}',
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
                        "data": "plate_number"
                    },
                    {
                        "data": "number_seats"
                    },
                    {
                        "data": "color"
                    },
                    {
                        data: 'action'
                    }
                ]
            });


            $('#carTypeSelect,#driver_select').on('change',
                function() {
                    cars_table.ajax.reload();
                });
            $(document).on('change', '#car_type_id', function() {
                if ($(this).val() !== '') {
                    $.ajax({
                        url: '/housingmovements/carModel-by-carType_id/' + $(this).val(),
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
