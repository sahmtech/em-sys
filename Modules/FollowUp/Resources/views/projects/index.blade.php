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
                    ]) !!}

                </div>
            </div>
                {{-- <div class="col-md-3">
                    <div class="form-group">
                        <label for="offer_status_filter">@lang('followup::lang.project_status'):</label>
                        <select class="form-control select2" name="offer_status_filter" required id="offer_status_filter" style="width: 100%;">
                            <option value="all">@lang('lang_v1.all')</option>
                            <option value="not_started">@lang('followup::lang.not_started')</option>
                            <option value="under_process">@lang('followup::lang.under_process')</option>
                            <option value="done">@lang('followup::lang.done')</option>

                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="type_filter">@lang('followup::lang.project_type'):</label>
                        <select class="form-control select2" name="type_filter" required id="type_filter" style="width: 100%;">
                            <option value="all">@lang('lang_v1.all')</option>
                            <option value="External">@lang('followup::lang.external')</option>
                            <option value="Internal">@lang('followup::lang.internal')</option>
                         

                        </select>
                    </div>
                </div> --}}
              
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])

      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="projects_table">
                    <thead>
                        <tr>
                            <th>@lang('sales::lang.project_name')</th>
                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.start_date')</th>
                            <th>@lang('sales::lang.end_date')</th>
                            <th>@lang('sales::lang.active_worker_count')</th>
                            <th>@lang('sales::lang.worker_count')</th>
                            <th>@lang('sales::lang.contractDuration')</th>
                            <th>@lang('sales::lang.contract_form')</th>
                            <th>@lang('followup::lang.project_status')</th>
                            <th>@lang('followup::lang.project_type')</th>
                            <th>@lang('sales::lang.action')</th>


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

    $(document).ready(function () {
       
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
        columns: [
            { data: 'contact_name' },
            { data: 'number_of_contract'},
            { data: 'start_date'},
            { data: 'end_date'},
            { data: 'active_worker_count'},
            { data: 'worker_count'},
            { data: 'duration'},
            { 
                data: 'contract_form',
                render: function(data, type, full, meta) {
                    switch (data) {
                        case 'monthly_cost':
                            return '{{ trans("sales::lang.monthly_cost") }}';
                        case 'operating_fees':
                            return '{{ trans("sales::lang.operating_fees") }}';
                      
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
                            return '{{ trans("sales::lang.external") }}';
                        case 'Internal':
                            return '{{ trans("sales::lang.internal") }}';
                      
                        default:
                            return data;
                    }
                }
            },
            { data: 'action' },
            

        ]

    });

    $('#project_name_filter').on('change', function() {
        $('#projects_table').DataTable().ajax.reload();
        });
    });

</script>
@endsection
