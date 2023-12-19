@extends('layouts.app')
@section('title', __('followup::lang.projects'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.projects')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts2, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                                'id' => 'project_name_filter',
                            ]) !!}

                        </div>
                    </div>
 
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="projects_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width: 100px !important;">@lang('sales::lang.contact_name')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.contact_location_name')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.contract_number')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.start_date')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.end_date')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.active_worker_count')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.worker_count')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.contractDuration')</th>
                            {{-- <th>@lang('sales::lang.contract_form')</th> --}}
                            <th style="width: 100px !important;">@lang('followup::lang.project_status')</th>
                            <th style="width: 100px !important;">@lang('followup::lang.project_type')</th>
                            <th style="width: 100px !important;">@lang('sales::lang.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#project_name_filter_select').select2();
            $('#projects_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('projects2') }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }


                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'contact_name'
                    },
                    {
                        data: 'contact_location_name'
                    },
                    {
                        data: 'number_of_contract'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'active_worker_count'
                    },
                    {
                        data: 'worker_count'
                    },
                    {
                        data: 'duration'
                    },
                    // {
                    //     data: 'contract_form',
                    //     render: function(data, type, full, meta) {
                    //         switch (data) {
                    //             case 'monthly_cost':
                    //                 return '{{ trans('sales::lang.monthly_cost') }}';
                    //             case 'operating_fees':
                    //                 return '{{ trans('sales::lang.operating_fees') }}';

                    //             default:
                    //                 return data;
                    //         }
                    //     }
                    // },

                    {
                        data: 'status',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'Done':
                                    return '{{ trans('sales::lang.Done') }}';
                                case 'Under_process':
                                    return '{{ trans('sales::lang.Under_process') }}';


                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'type',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'External':
                                    return '{{ trans('sales::lang.external') }}';
                                case 'Internal':
                                    return '{{ trans('sales::lang.internal') }}';

                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'action'
                    },


                ]

            });

            $('#project_name_filter').on('change', function() {
                $('#projects_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
