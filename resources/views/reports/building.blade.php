@extends('layouts.app')
@section('title', __('essentials::lang.building'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.building')
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
                            <table class="table table-bordered table-striped" id="building">
                                <thead class="bg-green">
                                    <tr>

                                        <th>@lang('housingmovements::lang.building_name')</th>
                                        <th>@lang('housingmovements::lang.address')</th>
                                        <th>@lang('housingmovements::lang.building_end_date')</th>
                                        <th>@lang('housingmovements::lang.city')</th>
                                        <th>@lang('housingmovements::lang.building_guard')</th>
                                        <th>@lang('housingmovements::lang.building_supervisor')</th>
                                        <th>@lang('housingmovements::lang.building_cleaner')</th>

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


                var building;

                function reloadDataTable() {
                    building.ajax.reload();
                }

                building = $('#building').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('building') }}",

                                     },

                    columns: [

                       
                        {
                            data: 'name'
                        },
                        {
                            data: 'address'
                        },
                        {
                            data: 'building_contract_end_date'
                        },
                        {
                            data: 'city_id'
                        },
                        {
                            data: 'guard_id'
                        },
                        {
                            data: 'supervisor_id'
                        },
                        {
                            data: 'cleaner_id'
                        },
                    ],
                });




            });
        </script>
    @endsection
