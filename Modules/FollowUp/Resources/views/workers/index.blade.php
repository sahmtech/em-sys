@extends('layouts.app')
@section('title', __('followup::lang.workers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.workers')</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                                {!! Form::select('project_name_filter', $ContactsLocation, null, [
                                    'class' => 'form-control',
                                    'style' => 'width:100%;padding:2px;',
                                    'placeholder' => __('lang_v1.all'),
                                ]) !!}

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('nationality_filter', __('followup::lang.nationality') . ':') !!}
                                {!! Form::select('nationality_filter', $nationalities, null, [
                                    'class' => 'form-control',
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
                                    'class' => 'form-control',
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                    @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="workers_table">
                    <thead>
                        <tr>
                            <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>
                          
                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('followup::lang.contract_end_date')</th>



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

            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('workers') }}",
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }
                        if ($('#nationality_filter').val()) {
                            d.nationality = $('#nationality_filter').val();
                        }
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [
                    { data: 'worker' },
                    { data: 'id_proof_number' },
                    { data: 'contact_name' },
                    { data: 'essentials_salary' },
                
                    
                    { data: 'nationality' },
                    { data: 'residence_permit_expiration' },
                    { data: 'contract_end_date' },
                ]
            });
            $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            $('#project_name_filter,#doc_filter_date_range,#nationality_filter').on('change', function() {
                workers_table.ajax.reload();
            });
        });
    </script>
@endsection

