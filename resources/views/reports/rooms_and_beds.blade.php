@extends('layouts.app')
@section('title', __('essentials::lang.rooms_and_beds'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.rooms_and_beds')
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
                            <table class="table table-bordered table-striped" id="rooms_and_beds">
                                <thead class="bg-green">
                                    <tr>

                                        <th>@lang('housingmovements::lang.room_number')</th>
                                        <th>@lang('housingmovements::lang.htr_building')</th>
                                        <th>@lang('housingmovements::lang.area')</th>
                                        <th>@lang('housingmovements::lang.total_beds')</th>
                                        <th>@lang('housingmovements::lang.available_beds')</th>
                                        <th>@lang('housingmovements::lang.contents')</th>
                                        <th></th>

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


                var rooms_and_beds;

                function reloadDataTable() {
                    rooms_and_beds.ajax.reload();
                }

                rooms_and_beds = $('#rooms_and_beds').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('rooms_and_beds') }}",

                    },

                    columns: [

                        {
                            data: 'room_number'
                        },
                        {
                            data: 'htr_building_id'
                        },
                        {
                            data: 'area'
                        },
                        {
                            data: 'total_beds'
                        },
                        {
                            data: 'beds_count'
                        },
                        {
                            data: 'contents'
                        },
                    ],
                });




            });
        </script>
    @endsection
