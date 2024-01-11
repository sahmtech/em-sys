@extends('layouts.app')
@section('title', __('housingmovements::lang.reserved_shopping'))

@section('content')
    @include('housingmovements::layouts.nav_worker')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.reserved_shopping')</span>
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
                <table class="table table-bordered table-striped" id="workers_table" style=" table-layout: fixed !important;">
                    <thead>
                        <tr>
                            {{-- <th>@lang('followup::lang.name')</th>
                            <th>@lang('followup::lang.eqama')</th>
                            <th>@lang('followup::lang.project_name')</th>
                            <th>@lang('followup::lang.essentials_salary')</th>

                            <th>@lang('followup::lang.nationality')</th>
                            <th>@lang('followup::lang.eqama_end_date')</th>
                            <th>@lang('followup::lang.contract_end_date')</th> --}}

                            <td style="width: 100px !important;">@lang('followup::lang.name')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.eqama')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.project_name')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.nationality')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.eqama_end_date')</td>
                            <td style="width: 100px !important;">@lang('essentials::lang.mobile_number')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.Basic_salary')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.total_salary')</td>
                            <td style="width: 100px !important;">@lang('followup::lang.gender')</td>
                            <td style="width: 100px !important;">@lang('essentials::lang.travel_categorie')</td>
                            <td style="width: 100px !important;">@lang('housingmovements::lang.created_by')</td>
                            <th style="width: 100px !important;">@lang('messages.action')</th>
                            


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent



    </section>


@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var date_filter = null;
            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('workers.reserved_shopping') }}",
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
                columns: [{
                        data: 'worker',
                        render: function(data, type, row) {
                            var link = '<a href="' +
                                '{{ route('htr.show.workers', ['id' => ':id']) }}'
                                .replace(':id', row.id) + '">' + data + '</a>';
                            return link;
                        }
                    },
                    {
                        data: 'residence_permit'
                    },
                    {
                        data: 'contact_name'
                    },
                    {
                        data: 'nationality'
                    },
                    {
                        data: 'residence_permit_expiration'
                    },
                    {
                        "data": "contact_number"
                    },

                    {

                        data: 'essentials_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }

                    },
                    {
                        data: 'total_salary',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }
                    },

                    {
                        data: 'gender'
                    },
                    {
                        data: 'categorie_id',

                    }, 
                    {
                        data: 'created_by',

                    }, 
                    {
                        data: 'action',

                    },
                ]
            });


            $(document).on('click', 'button.delete_book_worker_button', function() {

                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            workers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });


            });
            $('#workers_table tbody').on('click', 'tr', function() {
            var data = workers_table.row(this).data();
            console.log(data);
            if (data) {
                window.location = '{{ route('htr.show.workers', ['id' => ':id']) }}'.replace(':id', data.id);
            }
        });

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
        });
    </script>
@endsection
