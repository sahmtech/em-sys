@extends('layouts.app')
@section('title', __('essentials::lang.car_maintenances'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.car_maintenances')
        </h1>
<head>
<style>
    .bg-green {
        background-color: #28a745; 
        color: #ffffff; 
    }
</style>
</head>
        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="car_maintenances">
                                <thead class="bg-green">
                                    <tr>
                                        <th style="text-align: center;">@lang('housingmovements::lang.car')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.current_speedometer')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.maintenance_type')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.maintenance_description')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.invoice_no')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.date')</th>
                                    
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endcomponent

                </div>
            </div>

        </section>
    @endsection

    @section('javascript')
        <script type="text/javascript">
            $(document).ready(function() {


                var car_maintenances;

                function reloadDataTable() {
                    car_maintenances.ajax.reload();
                }

                car_maintenances = $('#car_maintenances').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('car_maintenances') }}",

                    },

                    columns: [{
                        "data": "car"
                    },
                    {
                        "data": "current_speedometer"
                    },
                    {
                        "data": "maintenance_type"
                    },
                    {
                        "data": "maintenance_description"
                    },
                    {
                        "data": "invoice_no"
                    },
                    {
                        "data": "date"
                    }
                    ],
                });




            });
        </script>
    @endsection
