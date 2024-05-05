@extends('layouts.app')
@section('title', __('housingmovements::lang.carsChangeOilReport'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.carsChangeOilReport')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                 
                    <div class="col-md-4">
                        {!! Form::label('carType_label', __('housingmovements::lang.car')) !!}

                        <select class="form-control" id="carSelect" name="carSelect" style="padding: 2px;">
                            <option value="all" selected>@lang('lang_v1.all')</option>
                            @foreach ($cars as $car)
                                <option value="{{ $car->id }}">
                                    {{ $car->plate_number . ' - ' .$car->CarModel->CarType->name_ar . ' - ' . $car->CarModel->name_ar }}</option>
                            @endforeach
                        </select>
                         
                    </div>
               
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carsChangeOil_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">@lang('housingmovements::lang.car')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.plate_number')</th>
                    
                                    <th style="text-align: center;">@lang('housingmovements::lang.current_speedometer')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.next_change_oil')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.invoice_no')</th>
                                    <th style="text-align: center;">@lang('housingmovements::lang.date')</th>
                                   
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
            $('#carSelect').select2();

            carsChangeOil_table = $('#carsChangeOil_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.cars-change-oil-report') }}',
                    data: function(d) {
                        if ($('#carSelect').val()) {
                            d.carSelect = $('#carSelect').val();
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
                        "data": "plate_number"
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
                    }
                ]
            });
         


      
            $('#carSelect,#name').on('change',
                function() {
                    carsChangeOil_table.ajax.reload();
                });
        });
    </script>
@endsection
