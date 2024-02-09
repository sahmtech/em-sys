@extends('layouts.app')
@section('title', __('housingmovements::lang.available_shopping'))

@section('content')
    @include('housingmovements::layouts.nav_worker')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.available_shopping')</span>
        </h1>
    </section>


    <section class="content">
        {{-- <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('nationality_filter', __('followup::lang.nationality') . ':') !!}
                            {!! Form::select('nationality_filter', $nationalities, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('essentials::lang.contract_end_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div> --}}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>

                            <th>@lang('housingmovements::lang.building_name')</th>
                            <th>@lang('housingmovements::lang.building_address')</th>
                            <th>@lang('housingmovements::lang.room_number')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>

                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>

                </table>
                <div style="margin-bottom: 10px;">
 
                    @if(auth()->user()->hasRole('Admin#1') ||  auth()->user()->can('housingmovements.add_worker_project'))
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="add-project-selected">
                        @lang('housingmovements::lang.add_worker_project')
                    </button>
                    @endif
                </div>
            </div>
            <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {!! Form::open([
                            'url' => action([\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'addProject']),
                            'method' => 'post',
                            'id' => 'cancle_project_form',
                        ]) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('housingmovements::lang.add_worker_project')</h4>
                        </div>

                        <div class="modal-body">
                            
                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('project', __('housingmovements::lang.project') . ':*') !!}
                                        {!! Form::select('project', $contacts, null, [
                                            'class' => 'form-control select2','required',
                                            'style' => 'width:100%;padding:2px;',
                                            'placeholder' => __('housingmovements::lang.select_project'),
                                        ]) !!}
            
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('adding_date', __('housingmovements::lang.adding_date') . ':') !!}
                                    {!! Form::date('adding_date', null, [
                                        'class' => 'form-control',
                                 
                                        'placeholder' => __('housingmovements::lang.adding_date'),
                                      
                                    ]) !!}
                                </div>

                            <div class="form-group col-md-12">
                                {!! Form::label('notes', __('housingmovements::lang.notes') . ':') !!}
                                {!! Form::textarea('notes', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('housingmovements::lang.notes'),
                                    'rows' => 2,
                                ]) !!}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="submitsBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        @endcomponent

        <div class="modal fade" id="book_worker_model" tabindex="-1" role="dialog"></div>

    </section>


@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var date_filter = null;
            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,
              
                info: false,
                ajax: {
                    url: "{{ route('workers.available_shopping') }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#nationality_filter').val()) {
                            d.nationality = $('#nationality_filter').val();
                        }
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.filter_start_date = start;
                            d.filter_end_date = end;
                            d.date_filter = date_filter;
                        }
                    }
                },
                columns: [
                    
                
                {
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id + '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
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
                        data: 'action'
                    }

                ]
            });

         //   $('#workers_table tbody').on('click', 'tr', function() {
        //    var data = workers_table.row(this).data();
        //    console.log(data);
        //    if (data) {
         //       window.location = '{{ route('htr.show.workers', ['id' => ':id']) }}'.replace(':id', data.id);
        //    }
       // });
            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                date_filter = null;
                reloadDataTable();
            });
            $('#project_name_filter, #nationality_filter').on('change', function() {
                workers_table.ajax.reload();
            });
            $('#doc_filter_date_range').on('change', function() {
                date_filter = 1;
                workers_table.ajax.reload();
            });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#workers_table').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === workers_table.rows()
                    .count());
            });

            $('#add-project-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                    };
                }).get();

                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                $('#changeStatusModal').modal('show');
            });
            $('#submitsBtn').click(function() {
                var formData = new FormData($('#cancle_project_form')[0]);
          
                $.ajax({
                    type: 'POST',
                    url: $('#cancle_project_form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success == true) {
                            toastr.success(result.msg);
                            workers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });

                $('#changeStatusModal').modal('hide');
            });
        });
    </script>
@endsection
