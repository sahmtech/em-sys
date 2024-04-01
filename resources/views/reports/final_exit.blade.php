@extends('layouts.app')
@section('title', __('essentials::lang.final_exit'))

@section('content')

    <section class="content-header">

        <h1>@lang('essentials::lang.final_exit')
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
                            <table class="table table-bordered table-striped" id="final_exit">
                                <thead class="bg-green">
                                    <tr>
                                        <th>@lang('followup::lang.name')</th>
                                        <th>@lang('followup::lang.eqama')</th>
                                        <th>@lang('followup::lang.project_name')</th>
                                        <th>@lang('housingmovements::lang.building_name')</th>
                                        <th>@lang('housingmovements::lang.building_address')</th>
                                        <th>@lang('housingmovements::lang.room_number')</th>
                                        <th>@lang('followup::lang.essentials_salary')</th>

                                        <th>@lang('followup::lang.nationality')</th>
                                        <th>@lang('followup::lang.eqama_end_date')</th>
                                        <th>@lang('followup::lang.contract_end_date')</th>
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


                var final_exit;

                function reloadDataTable() {
                    final_exit.ajax.reload();
                }

                final_exit = $('#final_exit').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('final_exit') }}",

                    },

                    columns: [

                    {
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' +
                                '{{ route('htr.show.workers', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },
                    {
                        data: 'id_proof_number'
                    },
                    {
                        data: 'contact_name'
                    },

                    {
                        data: 'building'
                    },
                    {
                        data: 'building_address'
                    },

                    {
                        data: 'room_number'
                    }, {
                        data: 'essentials_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }
                    },


                    {
                        data: 'nationality'
                    },
                    {
                        data: 'residence_permit_expiration'
                    },
                    {
                        data: 'contract_end_date'
                    },                    ],
                });




            });
        </script>
    @endsection
