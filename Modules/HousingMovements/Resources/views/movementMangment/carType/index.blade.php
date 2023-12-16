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
            {{-- <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\HousingMovements\Http\Controllers\CarTypeController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="col-sm-4">
                        <div class="form-group row">
                            {!! Form::label('search_lable', __('housingmovements::lang.search') . '  ') !!}
                            {!! Form::text('search', '', [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('housingmovements::lang.name_in_ar_en'),
                                'id' => 'search',
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-sm-8" style="padding-right: 3px;">
                        <button class="btn btn-block btn-primary" style="width: max-content;margin-top: 25px;" type="submit">
                            @lang('housingmovements::lang.search')</button>
                        @if ($after_serch)
                            <a class="btn btn-primary pull-right m-5 "
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@index') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@index') }}">
                                @lang('housingmovements::lang.viewAll')</a>
                        @endif
                    </div>
                    {!! Form::close() !!}
                @endcomponent
            </div> --}}
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    {{-- @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot --}}
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}"
                                data-href="{{ action('Modules\HousingMovements\Http\Controllers\CarTypeController@create') }}"
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
                    url: '{{ route('car-types') }}',
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

            $('#name').on('change',
                function() {
                    carTypes_table.ajax.reload();
                });
        });
    </script>
@endsection