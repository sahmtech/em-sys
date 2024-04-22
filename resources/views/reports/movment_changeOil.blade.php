@extends('layouts.app')
@section('title', __('essentials::lang.cars_change_oil'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.cars_change_oil')
        </h1>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @component('components.widget', ['class' => 'box-solid'])
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="cars_change_oil">
                                <thead>
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
                    footer: true,
                                    buttons: ['excel', {
                                                extend: 'print',
                                                title: ' ',
                                                text: '<i class="glyphicon glyphicon-print" style="padding: 0px 7px"></i><span>Print</span>',
                                                className: 'btn printclass textSan',
                                                customize: function(win) {
                                                        $(win.document.body).prepend('<div style="display: flex;justify-content: space-between;"><div class="row" style="padding: 5px 25px;"><h3>@lang('lang_v1.emdadatalatta_comp')</h3><h3>@lang('housingmovements::lang.movement_management')</h3><h4>@lang('lang_v1.report') @lang('essentials::lang.cars_change_oil')</h4></div><img src="/uploads/custom_logo.png" class="img-rounded" alt="Logo" style="width: 175px;"> </div>');
        }
        }],
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
