@extends('layouts.app')
@section('title', __('internationalrelations::lang.proposed_labor'))

@section('content')
    @include('internationalrelations::layouts.nav_proposed_labor')

    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.accepted_workers')
        </h1>

    </section>


    <section class="content">

        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="professions_filter">@lang('essentials::lang.professions'):</label>
                    {!! Form::select('professions-select', $professions, request('professions-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'professions-select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.specializations'):</label>
                    {!! Form::select('specializations-select', $specializations, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div>



            <div class="col-md-3">
                <div class="form-group">
                    <label for="agency_filter">@lang('internationalrelations::lang.agency_name'):</label>
                    {!! Form::select('agency_filter', $agencys, request('agency_filter'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'agency_filter',
                    ]) !!}
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">

                <table class="table table-bordered table-striped ajax_view hide-footer" id="employees">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>@lang('internationalrelations::lang.worker_number')</th>
                            <th>@lang('internationalrelations::lang.worker_name')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.profession')</th>
                            <th>@lang('essentials::lang.specialization')</th>
                            <th>@lang('sales::lang.offer_price')</th>
                            <th>@lang('internationalrelations::lang.accepte_from_worker')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
                <div style="margin-bottom: 10px;">
                    <button type="button" class="btn btn-success btn-sm custom-btn" id="change-status-selected">
                        @lang('internationalrelations::lang.send_offer_price')
                    </button>
                    <button type="button" class="btn btn-warning btn-sm custom-btn" id="accepted-selected">
                        @lang('internationalrelations::lang.accepte_from_worker')
                    </button>

                </div>

            </div>

            <div class="modal fade" id="uploadFilesModal" tabindex="-1" role="dialog" aria-labelledby="uploadFilesModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadFilesModalLabel">Upload Files for Selected Rows</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form id="uploadFilesForm">

                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                                <br>
                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('internationalrelations::lang.add_price_offer_for_selected_workers') . ':') !!}
                                    <br>
                                    
                                    {!! Form::file('file', ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
                                </div>



                                {{ csrf_field() }}
                            </form>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-primary" id="submitFilesBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="uploadAcceptanceFilesModal" tabindex="-1" role="dialog" aria-labelledby="uploadAcceptanceFilesModalLabel" aria-hidden="true">

                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadAcceptanceFilesModalLabel">Upload Files for Selected Rows</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <form id="uploadAcceptanceFilesForm">

                                <input type="hidden" name="selectedRowsData" id="selectedRowsData2" />
                               
                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('internationalrelations::lang.add_acceptance_offer_for_selected_workers') . ':') !!}
                                    <br>
                                    {!! Form::file('file', ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
                                </div>


                                {{ csrf_field() }}
                            </form>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-primary"
                                id="submitAcceptanceFilesBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent



    </section>

@stop
@section('javascript')



    <script type="text/javascript">
        $(document).ready(function() {
            var users_table = $('#employees').DataTable({

                processing: true,
                serverSide: true,
                info: false,

                ajax: {
                    url: "{{ route('accepted_workers') }}",
                    data: function(d) {
                        d.specialization = $('#specializations-select').val();
                        d.profession = $('#professions-select').val();
                        d.agency = $('#agency_filter').val();

                    },
                },

                "columns": [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return '<input type="checkbox" class="select-row" data-id="' + row.id +
                                '" data-full_name="' + row.full_name +
                                '" data-is_price_offer_sent="' + row.is_price_offer_sent +
                                '" data-is_accepted_by_worker="' + row.is_accepted_by_worker + '">';
                        },
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: "id",

                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "agency_id"
                    },


                    {
                        "data": "contact_number"
                    },
                    {
                        "data": "nationality_id"
                    },

                    {
                        "data": "profession_id",

                    },
                    {
                        "data": "specialization_id",

                    },
                    {
                        "data": "is_price_offer_sent",
                        "render": function(data, type, row) {
                            var isPriceOfferSent = row.is_price_offer_sent || 0;
                            var text = isPriceOfferSent == 1 ?
                                '{{ __('lang_v1.send') }}' :
                                '{{ __('lang_v1.not_sent_yet') }}';

                            var color = isPriceOfferSent == 1 ?
                                'green' :
                                'red';

                            return '<span style="color: ' + color + ';">' + text + '</span>';
                        }
                    },

                    {
                        "data": "is_accepted_by_worker",
                        "render": function(data, type, row) {
                            var text = row.is_accepted_by_worker == 1 ?
                                '{{ __('lang_v1.accepted') }}' :
                                '{{ __('lang_v1.not_yet') }}';

                            var color = row.is_accepted_by_worker == 1 ?
                                'green' :
                                'red';

                            return '<span style="color: ' + color + ';">' + text + '</span>';
                        }
                    },
                    {
                        "data": "action"
                    }
                ],

            });

            $('#specializations-select, #professions-select, #agency_filter').change(
                function() {
                    users_table.ajax.reload();

                });

            $('#select-all').change(function() {
                $('.select-row').prop('checked', $(this).prop('checked'));
            });

            $('#employees').on('change', '.select-row', function() {
                $('#select-all').prop('checked', $('.select-row:checked').length === users_table.rows()
                    .count());
            });
            $('#change-status-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                        full_name: $(this).data('full_name'),
                        is_price_offer_sent: $(this).data('is_price_offer_sent')
                    };
                }).get();


                $('#selectedRowsData').val(JSON.stringify(selectedRows));
                var modalTitle = '{{ __('sales::lang.offer_price') }}';


                $('#uploadFilesModalLabel').text(modalTitle);

                $('#fileInputsContainer').empty();
                var selectFileLabel = '{{ __('sales::lang.uploade_file_for') }}';

               

                $('#uploadFilesModal').modal('show');
            });

            $('#submitFilesBtn').click(function() {
                var formData = new FormData($('#uploadFilesForm')[0]);


                $.ajax({
                    type: 'POST',
                    url: '{{ route('send_offer_price') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });


                $('#uploadFilesModal').modal('hide');
            });

            $('#accepted-selected').click(function() {
                var selectedRows = $('.select-row:checked').map(function() {
                    return {
                        id: $(this).data('id'),
                        full_name: $(this).data('full_name'),
                        is_accepted_by_worker: $(this).data('is_accepted_by_worker')
                    };
                }).get();
                $('#selectedRowsData2').val(JSON.stringify(selectedRows));
                var modalTitle = '{{ __('internationalrelations::lang.accepte_from_worker') }}';


                $('#uploadAcceptanceFilesModalLabel').text(modalTitle);

                $('#acceptanceFileInputsContainer').empty();
                var selectFileLabel = '{{ __('sales::lang.uploade_file_for') }}';

               

                $('#uploadAcceptanceFilesModal').modal('show');
            });

            $('#submitAcceptanceFilesBtn').click(function() {
                var formData = new FormData($('#uploadAcceptanceFilesForm')[0]);


                $.ajax({
                    type: 'POST',
                    url: '{{ route('accepted_by_worker') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(error) {

                    }
                });


                $('#uploadAcceptanceFilesModal').modal('hide');
            });
        });
    </script>


@endsection
