@extends('layouts.app')
@section('title', __('essentials::lang.work_cards'))
@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.create_work_cards')</span>
        </h1>
    </section>

    <head>
        <style>
            .input-with-padding {
                padding-right: 15px !important;
            }

            .renew-row {
                margin-bottom: 10px;
            }
        </style>
    </head>


    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="offer_type_filter">@lang('essentials::lang.project'):</label>
                    {!! Form::select('contact-select', $sales_projects, null, [
                        'class' => 'form-control select2',
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                    
                        'id' => 'contact-select',
                    ]) !!}
                </div>
            </div>

            {{-- <div class="col-md-3">
                <div class="form-group">
                    <label for="offer_type_filter">@lang('essentials::lang.proof_numbers'):</label>
                    {!! Form::select('proof_numbers_select', $proof_numbers->pluck('full_name', 'id'), null, [
                        'class' => 'form-control select2',
                        'multiple' => 'multiple',
                        'style' => 'height:40px',
                    
                        'name' => 'proof_numbers_select[]',
                        'id' => 'proof_numbers_select',
                    ]) !!}
                </div>
            </div> --}}
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="#" data-toggle="modal" data-target="#createWorkCardModal">
                        <i class="fa fa-plus"></i> @lang('essentials::lang.create_work_cards')</a>
                </div>
            @endslot
            @php
                $colspan = 14;
            @endphp
            <div class="col-md-8 selectedDiv" style="display:none;"> </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="card_table">
                    <thead>



                        <tr>
                            <th><input type="checkbox" class="largerCheckbox" id="chkAll" /></th>
                            <th>@lang('essentials::lang.card_no')</th>
                            <th>@lang('essentials::lang.company_name')</th>

                            <th>@lang('essentials::lang.employee_name')</th>
                            <th>@lang('essentials::lang.nationality')</th>
                            <th>@lang('essentials::lang.proof_number_card')</th>
                            <th>@lang('essentials::lang.Residency_end_date')</th>
                            <th>@lang('essentials::lang.project')</th>
                            <th>@lang('essentials::lang.responsible_client')</th>

                            <th>@lang('essentials::lang.workcard_duration')</th>
                            <th>@lang('essentials::lang.work_card_fees')</th>

                            <th>@lang('essentials::lang.passport_fees')</th>
                            <th>@lang('essentials::lang.other_fees')</th>
                            <th>@lang('essentials::lang.pay_number')</th>
                            <th>@lang('essentials::lang.fixed')</th>



                        </tr>
                    </thead>


                    <tfoot>
                        <tr>
                            <td colspan="5">
                                @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_renew_residency'))
                                    <div style="display: flex; width: 100%;">
                                        {!! Form::open([
                                            'url' => action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'postRenewData']),
                                            'method' => 'post',
                                            'id' => 'renew_form',
                                        ]) !!}

                                        {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']) !!}

                                        @include('essentials::cards.partials.renew_modal')

                                        {!! Form::submit(__('essentials::lang.renew'), ['class' => 'btn btn-xs btn-success', 'id' => 'renew-selected']) !!}

                                        {!! Form::close() !!}

                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endcomponent



    </section>

    @include('essentials::cards.create_workcard_modal')
@endsection


@section('javascript')
    <script>
        $(document).ready(function() {
            $('#Payment_number').on('input', function() {
                var payNumber = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
                if (payNumber.length !== 14) {
                    // If not exactly 14 numbers, show error message
                    $('#error-message').text('يجب أن يتكون رقم السداد من 14 رقم تماما').show();
                } else {
                    // If exactly 14 numbers, hide error message
                    $('#error-message').hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Function to calculate fees based on selected duration
            function calculateFees(selectedValue) {
                switch (selectedValue) {
                    case '3':
                        return 2425;
                    case '6':
                        return 4850;
                    case '9':
                        return 7275;
                    case '12':
                        return 9700;
                    default:
                        return 0;
                }
            }

            // Event listener for workcard_duration_input change
            $('#workcard_duration_input').change(function() {
                var selectedDuration = $(this).val();
                var feesOptions = [];
                switch (selectedDuration) {
                    case '3':
                        feesOptions = ['163', '288', '413'];
                        break;
                    case '6':
                        feesOptions = ['326', '825'];
                        break;
                    case '9':
                        feesOptions = ['488'];
                        break;
                    case '12':
                        feesOptions = ['650', '1150'];
                        break;
                    default:
                        feesOptions = [];
                }
                // Clear previous options
                $('#fees_input').empty();
                // Populate fees dropdown with new options
                $.each(feesOptions, function(index, value) {
                    $('#fees_input').append($('<option>', {
                        value: value,
                        text: value
                    }));
                });

                // Calculate and populate work_card_fees field
                var calculatedFees = calculateFees(selectedDuration);
                $('#work_card_fees').val(calculatedFees);
            });
        });
    </script>


    <script type="text/javascript">
        var translations = {
            months: @json(__('essentials::lang.months')),
            management: @json(__('essentials::lang.management'))


        };



        $(document).ready(function() {

            $('#createWorkCardModal').find('.select2').select2({
                dropdownParent: $('#createWorkCardModal')
            });

            var card_table = $('#card_table').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('cards') }}",
                    data: function(d) {
                        d.project = $('#contact-select').val();

                        d.proof_numbers = $('#proof_numbers_select').val();
                        console.log(d);


                    },

                },

                rowCallback: function(row, data) {

                    if (data.expiration_date) {
                        var expiration_date = moment(data.expiration_date, 'YYYY-MM-DD HH:mm:ss');

                        var threeDaysAgo = moment().subtract(3, 'days');

                        if (expiration_date < moment()) {
                            $('td:eq(6)', row).css('background-color', 'rgba(255, 0, 0, 0.2)');
                            console.log(expiration_date);
                        } else {
                            $('td:eq()', row).css('background-color', '');
                        }
                    }

                },
                columns: [

                    {
                        data: 'id',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' +
                                data + '" />';
                        }
                    },
                    {
                        data: 'card_no',
                        name: 'card_no'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },


                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'nationality',
                        name: 'nationality'
                    },
                    {
                        data: 'proof_number',
                        name: 'proof_number'
                    },
                    {
                        data: 'r_expiration_date',
                        name: 'r_expiration_date'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'responsible_client',
                        name: 'responsible_client'
                    },
                    {
                        data: 'workcard_duration',
                        name: 'workcard_duration',
                        render: function(data) {
                            if (data != null) {
                                return data + " " + translations['months'];
                            } else {
                                return " ";
                            }

                        }
                    },
                    {
                        data: "work_card_fees",
                        name: "work_card_fees"
                    },
                    {
                        data: 'passport_fees',
                        name: 'passport_fees'
                    },
                    {
                        data: 'other_fees',
                        name: 'other_fees'
                    },

                    {
                        data: 'Payment_number',
                        name: 'Payment_number'
                    },
                    {
                        data: 'fixnumber',
                        name: 'fixnumber'
                    },




                ]

            });





            $('body').on('submit', '#workCardForm', function(e) {
                e.preventDefault();
                var urlWithId = $(this).attr('action');
                $.ajax({
                    url: urlWithId,
                    type: 'POST',
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            console.log(response);
                            // Assuming buildings_table is defined elsewhere
                            card_table.ajax.reload();
                            toastr.success(response.msg);
                            $('#createWorkCardModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            $('#createWorkCardModal').modal('hide');
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);
                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });


            $('#contact-select').on('change', function() {


                card_table.ajax.reload();
            });

            $('#proof_numbers_select').on('change', function() {
                console.log($('#proof_numbers_select').val());
                card_table.ajax.reload();
            });

            $('#card_table').on('change', '.tblChk', function() {

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

            function calculateFees(selectedValue) {
                switch (selectedValue) {
                    case '3':
                        return 2425;
                    case '6':
                        return 4850;
                    case '9':
                        return 7275;
                    case '12':
                        return 9700;
                    default:
                        return 0;
                }
            }


            $('#renew-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {
                    $('#renewModal').modal('show');

                    $.ajax({
                        url: '{{ route('getSelectedworkcardData') }}',
                        type: 'post',
                        data: {
                            selectedRows: selectedRows
                        },

                        success: function(response) {
                            var data = response.data;

                            var durationOptions = response.durationOptions;
                            console.log(durationOptions);

                            $('.modal-body').empty();

                            var inputClasses = 'form-group';
                            var inputClasses2 = 'form-group col-md-3';


                            var labelsRow = $('<div>', {
                                class: 'row'
                            });




                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.Residency_no') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.Residency_end_date') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.choose_renew_duration') }}'
                            }));


                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.work_card_fees') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.passport_fees') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 20px;',
                                text: '{{ __('essentials::lang.pay_number') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding-left: 20px; padding-right: 0;',
                                text: '{{ __('essentials::lang.fixed') }}'
                            }));

                            $('.modal-body').append(labelsRow);




                            $.each(data, function(index, row) {

                                var rowDiv = $('<div>', {
                                    class: 'row renew-row'
                                });




                                var rowIDInput = $('<input>', {
                                    type: 'hidden',
                                    name: 'id[]',
                                    class: inputClasses + 'col-md-2',
                                    style: 'height: 40px',
                                    placeholder: '{{ __('essentials::lang.id') }}',

                                    value: row.id
                                });
                                rowDiv.append(rowIDInput);


                                var workerIDInput = $('<input>', {
                                    type: 'hidden',
                                    name: 'employee_id[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px',
                                    placeholder: '{{ __('essentials::lang.id') }}',
                                    value: row.employee_id
                                });
                                rowDiv.append(workerIDInput);


                                var numberInput = $('<input>', {
                                    type: 'text',
                                    name: 'number[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center;padding-right: 20px; padding-right: 20px; !important',
                                    placeholder: '{{ __('essentials::lang.Residency_no') }}',
                                    value: row.number,
                                    readonly: true

                                });

                                rowDiv.append(numberInput);

                                var expiration_date = row.expiration_date ? formatDate(
                                    row.expiration_date) : '';
                                console.log(expiration_date);
                                var expiration_dateInput = $('<input>', {
                                    type: 'text',
                                    name: 'expiration_date[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center;padding-right: 20px; padding-right:20px; !important',
                                    placeholder: '{{ __('essentials::lang.expiration_date') }}',
                                    value: expiration_date
                                });

                                rowDiv.append(expiration_dateInput);

                                function formatDate(dateString) {
                                    var date = new Date(dateString);
                                    var day = date.getDate();
                                    var month = date.getMonth() + 1;
                                    var year = date.getFullYear();

                                    // Pad day and month with leading zeros if needed
                                    if (day < 10) {
                                        day = '0' + day;
                                    }
                                    if (month < 10) {
                                        month = '0' + month;
                                    }

                                    return year + '-' + month + '-' + day;
                                }

                                var renewDurationInput = $('<select>', {
                                    id: 'renew_durationId_' + index,
                                    name: 'renew_duration[]',
                                    class: 'form-control select2' +
                                        inputClasses2 + ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center;padding-right: 20px; padding-right:20px; !important',
                                    required: true
                                });

                                Object.keys(durationOptions).forEach(function(value) {
                                    var option = $('<option>', {
                                        value: value,
                                        text: durationOptions[value]
                                    });

                                    renewDurationInput.append(option);
                                });

                                renewDurationInput.val(row.workcard_duration);
                                rowDiv.append(renewDurationInput);



                                var feesInput = $('<input>', {
                                    type: 'text',
                                    name: 'fees[]',
                                    class: inputClasses2 +
                                        ' input-with-padding' + 'fees-input',
                                    style: 'height: 40px; width:140px; text-align: center;padding-right: 20px; padding-right:20px; !important',
                                    placeholder: '{{ __('essentials::lang.work_card_fees') }}',
                                    required: true,
                                    value: row.work_card_fees
                                });

                                rowDiv.append(feesInput);


                                var passportFeesSelect = $('<select>', {
                                    name: 'passportfees[]',
                                    class: inputClasses2 +
                                        ' input-with-padding fees-input',
                                    style: 'height: 40px; width:140px; text-align: center;padding-right: 20px; padding-right:20px; !important',
                                    placeholder: '{{ __('essentials::lang.passport_fees') }}',
                                    required: true
                                });


                                var option = $('<option>', {
                                    value: row.passport_fees,
                                    text: row.passport_fees
                                });
                                passportFeesSelect.append(option);
                                rowDiv.append(passportFeesSelect);


                                var pay_numberInput = $('<input>', {
                                    type: 'number',
                                    name: 'Payment_number[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center; padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.pay_number') }}',
                                    value: row.Payment_number
                                });


                                pay_numberInput.on('input', function() {
                                    var currentValue = $(this).val().replace(
                                        /\D/g, ''
                                        ); // Remove non-numeric characters
                                    if (currentValue.length !== 14) {
                                        // If not exactly 14 digits, show error message
                                        $('#error-message').text(
                                            'You must enter exactly 14 numbers'
                                            ).show();
                                    } else {
                                        // If exactly 14 digits, hide error message
                                        $('#error-message').hide();
                                    }
                                });

                                rowDiv.append(pay_numberInput);


                                var fixnumberInput = $('<input>', {
                                    type: 'text',
                                    name: 'fixnumber[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center; padding-right:20px; !important',
                                    placeholder: '{{ __('essentials::lang.fixed') }}',

                                    value: row.fixnumber,
                                    disabled: true
                                });

                                rowDiv.append(fixnumberInput);


                                $('.modal-body').append(rowDiv);

                                renewDurationInput.select2({
                                    dropdownParent: $('#renewModal'),
                                });
                                passportFeesSelect.select2({
                                    dropdownParent: $('#renewModal'),
                                });

                                $('#renew_durationId_' + index).val(row
                                    .workcard_duration).trigger('change');


                                $('#renew_durationId_' + index).on('change',
                            function() {
                                    console.log("Change event triggered");
                                    var selectedValue = $(this).val();
                                    console.log("Selected value:",
                                        selectedValue);
                                    var feesInput = $(this).closest('.row')
                                        .find('input[name="fees[]"]');
                                    console.log("Fees input found:", feesInput);
                                    var fees = calculateFees(selectedValue);
                                    console.log("Calculated fees:", fees);
                                    feesInput.val(fees);
                                });


                                $('#renew_durationId_' + index).on('change',
                            function() {
                                    var selectedValue = $(this).val();
                                    var passportFeesSelect = $(this).closest(
                                        '.row').find(
                                        'select[name="passportfees[]"]');
                                    passportFeesSelect
                                .empty(); // Clear previous options

                                    if (selectedValue === '3') {
                                        var feesOptions = {
                                            163: '163',
                                            288: '288',
                                            413: '413',

                                        };
                                        $.each(feesOptions, function(value,
                                            text) {
                                            var option = $('<option>', {
                                                value: value,
                                                text: text
                                            });
                                            passportFeesSelect.append(
                                                option);
                                        });
                                    } else if (selectedValue === '6') {
                                        var feesOptions = {
                                            326: '326',
                                            825: '825',
                                        };
                                        $.each(feesOptions, function(value,
                                            text) {
                                            var option = $('<option>', {
                                                value: value,
                                                text: text
                                            });
                                            passportFeesSelect.append(
                                                option);
                                        });
                                    } else if (selectedValue === '9') {
                                        var feesOptions = {
                                            488: '488',

                                        };
                                        $.each(feesOptions, function(value,
                                            text) {
                                            var option = $('<option>', {
                                                value: value,
                                                text: text
                                            });
                                            passportFeesSelect.append(
                                                option);
                                        });
                                    } else if (selectedValue === '12') {
                                        var feesOptions = {
                                            650: '650',
                                            1150: '1150',

                                        };
                                        $.each(feesOptions, function(value,
                                            text) {
                                            var option = $('<option>', {
                                                value: value,
                                                text: text
                                            });
                                            passportFeesSelect.append(
                                                option);
                                        });
                                    }
                                });




                            });
                        },
                        error: function(error) {
                            console.error('Error submitting form:', error);

                            toastr.error(
                                'An error occurred while submitting the form.',
                                'Error');
                        },
                    });

                    $(document).ready(function() {
                        $('#renewModal').on('hidden.bs.modal', function() {
                            location.reload();
                        });
                    });




                    $('body').on('submit', '#renew_form', function(e) {
                        e.preventDefault();
                        var urlWithId = $(this).attr('action');
                        $.ajax({
                            url: urlWithId,
                            type: 'POST',
                            data: new FormData(this),
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                console.log(response);
                                if (response.success) {
                                    console.log(response);
                                    // Assuming buildings_table is defined elsewhere
                                    card_table.ajax.reload();
                                    toastr.success(response.msg);
                                    $('#renewModal').modal('hide');
                                } else {
                                    toastr.error(response.msg);
                                    $('#renewModal').modal('hide');
                                    console.log(response);
                                }
                            },
                            error: function(error) {
                                console.error('Error submitting form:', error);
                                toastr.error(
                                    'An error occurred while submitting the form.',
                                    'Error');
                            },
                        });
                    });


                    // $('#renewModal form').click(function() {
                    //     console.log($('#renew_form').attr('action'));
                    //     $.ajax({
                    //         url: $('#renew_form').attr('action'),
                    //         type: 'post',
                    //         data: $('#renew_form').serialize(),
                    //         success: function(response) {
                    //             if (response.success) {
                    //                 console.log(response);
                    //                 toastr.success(response.msg, 'Success');

                    //                 $('#renew_form').modal('hide');
                    //                 reloadDataTable();

                    //             } else {
                    //                 toastr.error(response.msg);
                    //                 console.log(response);
                    //             }
                    //         },
                    //         error: function(error) {
                    //             console.error('Error submitting form:', error);

                    //             toastr.error(
                    //                 'An error occurred while submitting the form.',
                    //                 'Error');
                    //         },
                    //     });
                    // });



                } else {
                    $('input#selected_rows').val('');
                    swal({
                        title: "@lang('lang_v1.no_row_selected')",
                        icon: "warning",
                        button: "OK",
                    });
                }
            });

            $('#renewModal').on('hidden.bs.modal', function(e) {

                $('.modal-body').find('input, select').val('');
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
        });
    </script>




    <script>
        function getData(employeeId) {
            getResponsibleData(employeeId);
            getResidencyData(employeeId);
        }
    </script>



    <script>
        function getResponsibleData(employeeId) {

            $.ajax({
                url: "{{ route('get_responsible_data') }}",
                type: 'GET',
                data: {
                    employeeId: employeeId
                },
                success: function(data) {
                    console.log(data);


                    $('#responsible_users').empty();
                    $('#responsible_users').append($('<option>', {
                        value: data.all_responsible_users.id,
                        text: data.all_responsible_users.name
                    }));

                    if (data.responsible_client.length > 0) {
                        $('#responsible_client').empty();
                        $.each(data.responsible_client, function(index, item) {
                            $('#responsible_client').append($('<option>', {
                                value: item.id,
                                text: item.name,
                                selected: true
                            }));
                        });


                        $('#responsible_client').select2();
                    } else if (data.responsible_client.length == 0 && data.all_responsible_users.length == 0) {
                        $('#responsible_client').empty();
                        $('#responsible_client').append($('<option>', {
                            value: null,
                            text: translations['management'],
                            selected: true
                        }));

                        $('#responsible_users').empty();
                        $('#responsible_users').append($('<option>', {
                            value: null,
                            text: translations['management'],
                            selected: true
                        }));
                    } else {
                        $('#responsible_client').empty();
                    }



                    $('#business').empty();
                    $('#business').append($('<option>', {
                        value: data.company.id,
                        text: data.company.name
                    }));


                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>


    <script>
        function getResidencyData(employeeId) {

            $.ajax({
                url: '{{ route('getResidencyData') }}',
                type: 'GET',
                data: {
                    employee_id: employeeId
                },
                success: function(response) {

                    $('#Residency_no').val(response.residency_no);
                    $('#border_no').val(response.border_no);
                    $('#Residency_end_date').val(response.residency_end_date);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            $('#employees').on('change', function() {
                var employeeId = $(this).val();
                $('#employee_id').val(employeeId);
                console.log(employeeId);
            });
        });


        $(document).ready(function() {
            $('#all_responsible_users').on('change', function() {
                var employee = $(this).val();
                $('#responsible_user_id').val(employee);
                console.log(employee);
            });
        });
    </script>


@endsection
