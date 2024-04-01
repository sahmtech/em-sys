@extends('layouts.app')
@section('title', __('essentials::lang.cars_change_oil'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.cars_change_oil')
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
                            <table class="table table-bordered table-striped" id="cars_change_oil">
                                <thead class="bg-green">
                                    <tr>

                                        <th style="text-align: center;">@lang('housingmovements::lang.car')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.current_speedometer')</th>
                                        <th style="text-align: center;">@lang('housingmovements::lang.next_change_oil')</th>
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


                var cars_change_oil;

                function reloadDataTable() {
                    cars_change_oil.ajax.reload();
                }

                cars_change_oil = $('#cars_change_oil').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('cars_change_oil') }}",

                    },

                    columns: [{
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
                        }
                    ],
                });




            });
        </script>
    @endsection
