@extends('layouts.app')
@section('title', __('housingmovements::lang.carsChangeOil'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.carsChangeOil')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\Essentials\Http\Controllers\CarsChangeOilController@search'),
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
                   
                    </div>
               
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div> --}}

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary"
                                href="{{ action('Modules\Essentials\Http\Controllers\CarsChangeOilController@create') }}">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\Essentials\Http\Controllers\CarsChangeOilController@create') }}"
                                data-href="{{ action('Modules\Essentials\Http\Controllers\CarsChangeOilController@create') }}"
                                data-container="#add_carsChangeOil_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carsChangeOil_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.car')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.current_speedometer')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.next_change_oil')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.invoice_no')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.date')</th>
                                    <th style="text-align: center;">@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <center class="mt-5">
                            {{ $carModles->links() }}
                        </center> --}}
                    </div>


                    <div class="modal fade" id="add_carsChangeOil_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_carsChangeOil_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>


    </section>
    <!-- /.content -->

@endsection
@section('javascript')


    <script type="text/javascript">
        $(document).ready(function() {
            $('#car__type_id').select2();

            carsChangeOil_table = $('#carsChangeOil_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.cars-change-oil') }}',
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
                        "data": "car"
                    },
                    {
                        "data": "current_speedometer"
                    },
                    {
                        "data": "next_change_oil"
                    },
                    {
                        "data": "invoice_no"
                    },
                    {
                        "data": "date"
                    },

                    {
                        data: 'action'
                    }
                ]
            });
            $(document).on('click', 'button.delete_carsChangeOil_button', function() {

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
                            carsChangeOil_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });


            $(document).on('click', 'button.edit_carModel_button', function() {

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
            $('#carTypeSelect,#name').on('change',
                function() {
                    carsModel_table.ajax.reload();
                });
        });
    </script>
@endsection
