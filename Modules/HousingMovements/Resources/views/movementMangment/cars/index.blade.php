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
                            {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}

                            <select class="form-control" name="car_type_id" style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($carTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->name_ar . ' - ' . $type->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group ">
                                {!! Form::label('search_lable', __('طراز السيارة') . '  ') !!}
                                {!! Form::text('search_carModle', '', [
                                    'class' => 'form-control',
                                    'placeholder' => __('طراز السيارة'),
                                    'id' => 'search_carModle',
                                ]) !!}

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group ">
                                {!! Form::label('search_lable', __('اسم السائق   ') . '  ') !!}
                                {!! Form::text('search', '', [
                                    'class' => 'form-control',
                                    'placeholder' => __('اسم السائق'),
                                    'id' => 'search',
                                ]) !!}

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group ">
                                {!! Form::label('search_lable', __('رقم اللوحة') . '  ') !!}
                                {!! Form::text('search_plate_number', '', [
                                    'class' => 'form-control',
                                    'placeholder' => __('رقم اللوحة'),
                                    'id' => 'search_plate_number',
                                ]) !!}

                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            بحث</button>
                        @if ($after_serch)
                            <a class="btn btn-primary pull-right m-5 "
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@index') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@index') }}">
                                عرض الكل</a>
                        @endif
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
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@create') }}"
                                data-container="#add_car_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rooms_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th>@lang('housingmovements::lang.driver')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.car_typeModel')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.plate_number')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.color')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                @foreach ($Cars as $row)
                                    <tr>
                                        <td>

                                            {{ $row->User->id_proof_number . ' - ' . $row->User->first_name . ' ' . $row->User->last_name }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $row->CarModel->CarType->name_ar . ' - ' . $row->CarModel->name_ar }}

                                        </td>
                                        <td style="text-align: center;">
                                            {{ $row->plate_number }}

                                        </td>
                                        <td style="text-align: center;">
                                            {{ $row->color }}

                                        </td>


                                        <td style="text-align: center;">
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button"
                                                    style="background-color: transparent;
                                                font-size: x-large;
                                                padding: 0px 20px;"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item btn-modal" style="margin: 2px;"
                                                        title="@lang('messages.edit')"
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@edit', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@edit', $row->id) }}"
                                                        data-container="#edit_car_model">

                                                        <i class="fas fa-edit cursor-pointer"
                                                            style="padding: 2px;color:rgb(8, 158, 16);"></i>
                                                        @lang('messages.edit') </a>

                                                    <a class="dropdown-item" style="margin: 2px;" {{-- title="{{ $row->active ? @lang('accounting::lang.active') : @lang('accounting::lang.inactive') }}" --}}
                                                        href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@destroy', $row->id) }}"
                                                        data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarController@destroy', $row->id) }}"
                                                        {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>

                                                        <i class="fa fa-trash cursor-pointer"
                                                            style="padding: 2px;color:red;"></i>
                                                        @lang('messages.delete')

                                                    </a>
                                                </div>
                                            </div>




                                        </td>




                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                        <center class="mt-5">
                            {{ $Cars->links() }}
                        </center>
                    </div>


                    <div class="modal fade" id="add_car_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_car_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('.select2').select2({
                placeholder: 'Select a worker',
                allowClear: true,
                tags: true
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
