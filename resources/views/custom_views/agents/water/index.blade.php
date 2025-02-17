@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.water_weights'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.water_weights')</h1>
    </section>

    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="water_weights_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.driver')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.plate_number')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.weight_type')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.water_droping_location')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.sample_result')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.date')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.created_by')</th>
                                    <th>@lang('lang_v1.attachments')</th>
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
            $('.select2').select2({
                width: '100%'
            });
            var water_weights_table = $('#water_weights_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('agent_water_reports') }}',
                    data: function(d) {
                        d.company_id = $('#company_filterSelect').val();
                        d.driver_id = $('#driver_filterSelect').val();
                        d.weight_type = $('#weight_type_filterSelect').val();
                    }
                },
                columns: [{
                        data: 'project_id'
                    },
                    {
                        data: 'driver'
                    },
                    {
                        data: 'plate_number'
                    },
                    {
                        data: 'weight_type'
                    },
                    {
                        data: 'water_droping_location'
                    },
                    {
                        data: 'sample_result'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'file',
                        orderable: false,
                        searchable: false
                    },

                ]
            });


        });
    </script>
@endsection
