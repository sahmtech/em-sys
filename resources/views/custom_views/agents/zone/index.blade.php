@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.project_zones'))

@section('content')
    <section class="content-header">
        <h1>@lang('operationsmanagmentgovernment::lang.project_zones')</h1>
    </section>

    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="zones_table">
                            <thead>
                                <tr>
                                    <th>@lang('operationsmanagmentgovernment::lang.zone_name')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.project')</th>
                                    <th>@lang('operationsmanagmentgovernment::lang.contact')</th>
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

            var zones_table = $('#zones_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('operationsmanagmentgovernment.zone') }}',
                    data: function(d) {
                        d.project_id = $('#project_filterSelect').val();
                    }
                },
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'contact'
                    },

                ]
            });


        });
    </script>
@endsection
