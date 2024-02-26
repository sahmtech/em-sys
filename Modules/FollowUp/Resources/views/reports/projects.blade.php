@extends('layouts.app')
@section('title', __('followup::lang.reports.projectsReports'))

@section('content')


    <!-- Main content -->
    <section class="content">
        <div class="modal-header">
            <h2>
                <span>@lang('followup::lang.reports.projectsReports')</span>
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts_fillter, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
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
                            <th class="table-td-width-100px">@lang('sales::lang.contact_name')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.contact_location_name')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.contract_number')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.start_date')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.end_date')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.active_worker_count')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.worker_count')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.contractDuration')</th>
                            <th class="table-td-width-100px">@lang('sales::lang.contract_form')</th>
                            <th class="table-td-width-100px">@lang('followup::lang.project_status')</th>
                            <th class="table-td-width-100px">@lang('followup::lang.project_type')</th>



                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>
    <!-- /.content -->
 
@include('followup::reports.contract_modal')
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            var project_table = $('#projects_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects']) }}",
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
                        data: 'contact_location_name',
                        render: function(data, type, full, meta) {
                            return '<a href="#" class="open-contract-modal" data-id="' + full.id + '">' + data + '</a>';
                        }
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
                    {
                        data: 'contract_form',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'monthly_cost':
                                    return '{{ trans('sales::lang.monthly_cost') }}';
                                case 'operating_fees':
                                    return '{{ trans('sales::lang.operating_fees') }}';

                                default:
                                    return data;
                            }
                        }
                    },

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



                ]
            });

            $('#projects_table').on('click', '.open-contract-modal', function(e) {
                e.preventDefault();
                var projectId = $(this).data('id');
               var Url = '{{ route('fetch_contract_details') }}'
                $.ajax({
                    url:Url,
                    type: 'GET',
                    data: { project_id: projectId },
                     dataType: 'json',
                    success: function(response) {
                        var data = response.data;
                        console.log(data.salesContract);
                    
                        $('#contract_number').text(data.salesContract.number_of_contract);
                        $('#start_date').text(data.salesContract.start_date);
                        $('#end_date').text(data.salesContract.end_date);
                        if (data.salesContract.contract_file_path) {
                            $('#contract_file_button').show();
                            $('#contract_file_button').attr('onclick', 'viewContract("' + data.salesContract.contract_file_path + '")');
                            $('#no_contract_message').hide();
                        } else {
                            $('#contract_file_button').hide();
                            $('#no_contract_message').show();
                        }
                        $('#contract_modal').modal('show');
                    },
                    error: function(error) {
                        console.error('Error fetching contract details:', error);
                    }
                });
                
               
                $('#contract_modal').modal('show');
            });

            $('#project_name_filter,#type_filter').on('change', function() {
                project_table.ajax.reload();
              
            });
        });
    </script>
@endsection
