@extends('layouts.app')
@section('title', __('essentials::lang.building'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.building')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="building">
                                <thead>
                                    <tr>

                                        <th>@lang('housingmovements::lang.id')</th>
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
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('housingmovements::lang.housing_move')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.building')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
                    ajax: {
                        url: "{{ route('building') }}",

                                     },

                    columns: [

                        {
                            data: 'id'
                        },
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
