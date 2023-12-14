@extends('layouts.app')
@section('title', __('housingmovements::lang.carModels'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.carModels')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\HousingMovements\Http\Controllers\CarModelController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="col-md-4">
                        {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}

                        <select class="form-control" id="carTypeSelect" name="carTypeSelect" style="padding: 2px;">
                            <option value="all" selected>@lang('lang_v1.all')</option>
                            @foreach ($carTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name_ar . ' - ' . $type->name_en }}</option>
                            @endforeach
                        </select>
                        {{-- <div class="form-group">
                            {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}
                            {!! Form::select('carType_id', $carTypes->name, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div> --}}
                    </div>
                    {{-- <div class="col-sm-4">
                        <div class="form-group row">
                            {!! Form::label('search_lable', __('housingmovements::lang.search') . '  ') !!}
                            {!! Form::text('search', '', [
                                'class' => 'form-control',
                            
                                'placeholder' => __('housingmovements::lang.name_in_ar_en'),
                                'id' => 'search',
                            ]) !!}

                        </div>
                    </div> --}}
                    {{-- <div class="col-sm-4" style="padding-right: 3px;">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            @lang('housingmovements::lang.search')</button>
                        @if ($after_serch)
                            <a class="btn btn-primary pull-right m-5 "
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarModelController@index') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarModelController@index') }}">
                                @lang('housingmovements::lang.viewAll') </a>
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
                            <a class="btn btn-block btn-primary"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarModelController@create') }}">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarModelController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarModelController@create') }}"
                                data-container="#add_carModels_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carsModel_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_ar')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.name_en')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.carType')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $carModles->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_carModels_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_carModels_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#car__type_id').select2();

            carsModel_table = $('#carsModel_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('car-models') }}',
                    data: function(d) {
                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                            // console.log(d.project_name_filter);
                        }
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
                        "data": "carType"
                    },

                    {
                        data: 'action'
                    }
                ]
            });

            $('#carTypeSelect,#name').on('change',
                function() {
                    carsModel_table.ajax.reload();
                });
        });
    </script>
@endsection
