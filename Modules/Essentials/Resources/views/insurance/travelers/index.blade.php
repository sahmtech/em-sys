@extends('layouts.app')
@section('title', __('housingmovements::lang.travelers'))
@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.travelers')</span>
        </h1>
    </section>



    <!-- Main content -->
    <section class="content">
        @include('essentials::layouts.insurance_nav_trevelers')

        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $salesProjects, null, [
                                'class' => 'form-control select2',
                                'id' => 'project_name_filter',
                                'style' => 'width:100%;padding:2px;',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('housingmovements::lang.arrival_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        @component('components.widget', ['class' => 'box-primary'])
            @include('housingmovements::travelers.partials.travelers_list')

            @include('housingmovements::travelers.partials.housing_modal')
        @endcomponent



    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        var product_table;

        function reloadDataTable() {
            product_table.ajax.reload();
        }

        function formatDate(date) {
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();


            day = day < 10 ? '0' + day : day;
            month = month < 10 ? '0' + month : month;

            return day + '/' + month + '/' + year;
        }


        $(document).ready(function() {

            product_table = $('#product_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('travelers') }}',
                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name_filter = $('#project_name_filter').val();
                            console.log(d.project_name_filter);
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
                rowCallback: function(row, data) {
                    var arrivalDate = moment(data.arrival_date, 'YYYY-MM-DD HH:mm:ss');
                    var threeDaysAgo = moment().subtract(3, 'days');

                    if (arrivalDate < moment() && arrivalDate >= threeDaysAgo) {
                        $('td:eq(5)', row).css('background-color', 'rgba(255, 0, 0, 0.2)');
                    } else {
                        $('td:eq(5)', row).css('background-color', '');
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "profile_image",
                        "render": function(data, type, row) {
                            if (data) {

                                var imageUrl = '/uploads/' + data;
                                return '<img src="' + imageUrl +
                                    '" alt="Profile Image" class="img-thumbnail" width="50" height="50" style=" border-radius: 50%;">';
                            } else {
                                return '@lang('essentials::lang.no_image')';
                            }
                        }
                    },
                    {
                        "data": "contact"
                    },
                    {
                        "data": "project"
                    },


                    {
                        "data": "location"
                    },
                    {
                        "data": "medical_examination",
                        "render": function(data, type, row) {
                            var text = data === 1 ? '@lang('housingmovements::lang.medical_examination_done')' : '@lang('housingmovements::lang.medical_examination_not_done')';
                            var color = data === 1 ? 'green' : 'red';
                            return '<span style="color: ' + color + ';">' + text + '</span>';
                        }
                    },
                    {
                        "data": "arrival_date",

                    },
                    {
                        "data": "passport_number"
                    },
                    {
                        "data": "profession"
                    },
                    {
                        "data": "nationality"
                    },
                    {
                        "data": "worker_documents",
                        "render": function(data, type, row) {
                            let links = '';
                            if (row.worker_documents.length === 0 && !row.visa.file) {
                                return '@lang('housingmovements::lang.no_files')';
                            }


                            row.worker_documents.forEach(function(document) {

                                // var baseTranslationKey = 'housingmovements::lang.';
                                // var fullTranslationKey = baseTranslationKey + document.type;

                                // var translationTemplate = `{!! __('${fullTranslationKey}') !!}`;


                                // var translatedType = translationTemplate.replace('${fullTranslationKey}', document.type);
                                // links += `<a href="/uploads/${document.attachment}" target="_blank">${translatedType}</a><br>`;
                                links +=
                                    `<a href="/uploads/${document.attachment}" target="_blank">${document.type}</a><br>`;
                            });

                            if (row.visa && row.visa.file) {
                                let visaLinkText =
                                "{{ __('housingmovements::lang.general_visa') }}";
                                links +=
                                    `<a href="/uploads/${row.visa.file}" target="_blank">${visaLinkText}</a>`;
                            }

                            return links;
                        }
                    }
                ]
            });


            $('#project_name_filter').on('change', function() {
                date_filter = null;

                reloadDataTable();
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

            $('#doc_filter_date_range').on('change', function() {
                date_filter = 1;
                product_table.ajax.reload();
            });

            $('#product_table').on('change', '.tblChk', function() {

                if ($('.tblChk:checked').length == $('.tblChk').length) {
                    $('#chkAll').prop('checked', true);
                } else {
                    $('#chkAll').prop('checked', false);
                }
                getCheckRecords();
            });

            $("#chkAll").change(function() {

                if ($(this).prop('checked')) {
                    $('.tblChk').prop('checked', true);
                } else {
                    $('.tblChk').prop('checked', false);
                }
                getCheckRecords();
            });

            $('#arraived-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {
                    $('#arrivedModal').modal('show');

                    $.ajax({
                        url: '{{ route('getSelectedArrivalsData') }}',
                        type: 'post',
                        data: {
                            selectedRows: selectedRows
                        },
                        success: function(data) {

                            $('.modal-body').find('input').remove();


                            var inputClasses = 'form-group col-md-4 ';


                            $.each(data, function(index, row) {

                                var workerIDInput = $('<input>', {
                                    type: 'hidden',
                                    name: 'worker_id[]',
                                    class: inputClasses + 'mb-2',
                                    placeholder: '{{ __('housingmovements::lang.id') }}',
                                    required: true,
                                    style: 'height: 40px',
                                    value: row.worker_id
                                });

                                var workerNameInput = $('<input>', {
                                    type: 'text',
                                    name: 'worker_name[]',
                                    class: inputClasses + 'mb-2',
                                    placeholder: '{{ __('housingmovements::lang.worker_name') }}',
                                    required: true,
                                    style: 'height: 40px',
                                    value: row.worker_name
                                });

                                var passportNumberInput = $('<input>', {
                                    type: 'text',
                                    name: 'passport_number[]',
                                    class: inputClasses + 'mb-2',
                                    placeholder: '{{ __('housingmovements::lang.passport_number') }}',
                                    required: true,
                                    style: 'height: 40px',
                                    value: row.passport_number
                                });

                                var borderNoInput = $('<input>', {
                                    type: 'number',
                                    name: 'border_no[]',
                                    class: inputClasses + 'mb-2',
                                    style: 'height: 40px',
                                    placeholder: '{{ __('housingmovements::lang.border_no') }}',
                                    required: true
                                });


                                $('.modal-body').append(workerIDInput, workerNameInput,
                                    passportNumberInput, borderNoInput);
                            });
                        }
                    });

                    $('#submitArrived').click(function() {

                        $.ajax({
                            url: $('#arrived_form').attr('action'),
                            type: 'post',
                            data: $('#arrived_form').serialize(),
                            success: function(response) {


                                console.log(response);

                                $('#arrivedModal').modal('hide');
                                reloadDataTable();
                            }
                        });
                    });

                } else {
                    $('input#selected_rows').val('');
                    swal({
                        title: "@lang('lang_v1.no_row_selected')",
                        icon: "warning",
                        button: "OK",
                    });
                }
            });




            $('#edit-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();

                if (selectedRows.length > 0) {
                    $('#bulkEditModal').modal('show');
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });


            $('#applyBulkEdit').on('click', function() {

                $('#bulkEditModal').modal('hide');
            });




        });

        function getCheckRecords() {
            var selectedRows = [];
            $(".selectedDiv").html("");
            $('.tblChk:checked').each(function() {
                if ($(this).prop('checked')) {
                    const rec = "<strong>" + $(this).attr("data-id") + " </strong>";
                    $(".selectedDiv").append(rec);
                    selectedRows.push($(this).attr("data-id"));

                }

            });

            return selectedRows;
        }
    </script>







@endsection
