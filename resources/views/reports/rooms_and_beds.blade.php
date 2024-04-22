@extends('layouts.app')
@section('title', __('essentials::lang.rooms_and_beds'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.rooms_and_beds')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="rooms_and_beds">
                                <thead>
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
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('housingmovements::lang.housing_move')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.rooms_and_beds')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
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
