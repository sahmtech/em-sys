@extends('layouts.app')
@section('title', __('essentials::lang.view_final_visa'))

@section('content')




    <!-- Main content -->
    <section class="content">
        @include('essentials::layouts.nav_cards_operations')

        @component('components.widget', ['class' => 'box-primary'])
            @slot('tool')
                <div class="box-tools">
                    @if (auth()->user()->can('essentials.add_final_visa_muqeem'))
                        <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                            data-target="#finalVisaModal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    @endif
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="operation_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.employee_name')</th>
                            <th>@lang('essentials::lang.Identity_proof_id')</th>
                            <th>@lang('essentials::lang.employee_number')</th>
                            <th>@lang('essentials::lang.contry_nationality')</th>
                            <th>@lang('essentials::lang.department')</th>
                            <th>@lang('essentials::lang.profession')</th>
                            <th>@lang('essentials::lang.mobile_number')</th>
                            <th>@lang('essentials::lang.status')</th>
                        </tr>
                    </thead>


                    {{-- <tfoot>
                        <tr>
                            <td colspan="16">
                                <div style="display: flex; width: 100%;">

                                    &nbsp;

                                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_return_visa'))
                                        <button type="submit" class="btn btn-xs btn-warning" id="return_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.return_visa') }}
                                        </button>
                                    @endif
                                    &nbsp;

                                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_final_visa'))
                                        <button type="submit" class="btn btn-xs btn-success" id="final_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.final_visa') }}
                                        </button>
                                    @endif
                                    &nbsp;

                                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_absent_report'))
                                        <button type="submit" class="btn btn-xs btn-danger" id="absent_report_selected">
                                            <i class="fa fa-warning"></i>{{ __('essentials::lang.absent_report') }}
                                        </button>
                                    @endif
                                    &nbsp;


                                    @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.view_renew_residency'))
                                        <button type="submit" class="btn btn-xs btn-primary" id="renew-operation-selected">
                                            <i class="fa fa-warning"></i>{{ __('essentials::lang.renewal_residence') }}
                                        </button>
                                    @endif


                                </div>
                            </td>
                        </tr>
                    </tfoot> --}}



                </table>
            </div>
        @endcomponent
        <div class="col-md-8 selectedDiv" style="display:none;">
        </div>

        {{-- <div class="form-group">
                            {!! Form::label('user_ids', __('essentials::lang.employee') . ':*') !!}
                            {!! Form::select('user_ids[]', $users, null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width: 100%;',
                                'multiple',
                                'id' => 'user_ids',
                            ]) !!}
                        </div> --}}




        <!-- Modal for Return Visa -->
        <div class="modal fade" id="finalVisaModal" tabindex="-1" role="dialog" aria-labelledby="returnVisaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnVisaModalLabel">{{ __('essentials::lang.final_visa') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {!! Form::open([
                        'url' => action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'post_final_exit_visa']),
                        'method' => 'post',
                        'id' => 'bulk_final_edit_form',
                    ]) !!}
                    <div class="modal-body">

                        <div class="form-group">
                            {!! Form::label('user_ids', __('essentials::lang.employee') . ':*') !!}
                            {!! Form::select('user_ids[]', $users, null, [
                                'class' => 'form-control select2',
                                'required',
                                'style' => 'width: 100%;',
                                'multiple',
                                'id' => 'user_ids',
                            ]) !!}
                        </div>

                    </div>



                    <div class="clearfix"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>


                </div>
                {!! Form::close() !!}
            </div>
        </div>

        {{-- 


        @include('essentials::cards.partials.absent_report_modal')

        @include('essentials::cards.partials.renew_operation_modal') --}}
    </section>

@stop
@section('javascript')
    <script type="text/javascript">
        $(document).on('click', '.btn-modal1', function(e) {
            e.preventDefault();
            var userId = $(this).data('row-id');
            var userName = $(this).data('row-name');

            $('#addQualificationModal').modal('show');


            $('#employee').empty();
            $('#employee').append('<option value="' + userId + '">' + userName + '</option>');
        });
    </script>


    <script type="text/javascript">
        $(document).on('click', '.btn-modal2', function(e) {
            e.preventDefault();
            var userId = $(this).data('row-id');
            var userName = $(this).data('row-name');

            $('#add_doc').modal('show');


            $('#employees2').empty();
            $('#employees2').append('<option value="' + userId + '">' + userName + '</option>');
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            var operation_table = $('#operation_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('getEssentailsEmployeeOperation') }}",
                    data: function(d) {
                        d.type = 'final_visa'
                    },
                },
                "columns": [{
                        data: "employee_name",
                        namne: "employee_name",
                    },
                    {
                        data: "id_proof_number",
                        namne: "id_proof_number",
                    },
                    {
                        data: "employee_number",
                        namne: "employee_number",
                    },
                    {
                        data: "nationality",
                        namne: "nationality",
                    },
                    {
                        data: "department",
                        namne: "department",
                    },
                    {
                        data: "profession",
                        namne: "profession",
                    },
                    {
                        data: "mobile_number",
                        namne: "mobile_number",
                    },
                    {
                        data: "status",
                        namne: "status",
                        render: function(data, type, row) {
                            if (data === 'active') {
                                return '@lang('essentials::lang.active')';
                            } else if (data === 'vecation') {
                                return '@lang('essentials::lang.vecation')';
                            } else if (data === 'inactive') {
                                return '@lang('essentials::lang.inactive')';
                            } else if (data === 'terminated') {
                                return '@lang('essentials::lang.terminated')';
                            } else {
                                return ' ';
                            }
                        }
                    },

                ],
            });

            $('#proof_numbers_select').on('change', function() {
                console.log('proof', $('#proof_numbers_select').val());
                operation_table.ajax.reload();
            });
            $('#contact-select').on('change', function() {

                operation_table.ajax.reload();
            });

            $('#employees').on('change', '.tblChk', function() {

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


            $('#return_visa_selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {

                    $('#returnVisaModal').modal('show');

                    $('#bulk_edit_form').find('input[name="worker_id[]"]').remove();

                    $.each(selectedRows, function(index, workerId) {
                        var workerIdInput = $('<input>', {
                            type: 'hidden',
                            name: 'worker_id[]',
                            value: workerId
                        });


                        $('#bulk_edit_form').append(workerIdInput);
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });


            $('#bulk_edit_form').submit(function(e) {

                e.preventDefault();

                var formData = $(this).serializeArray();
                console.log(formData);
                console.log($(this).attr('action'));
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            console.log(response);
                            toastr.success(response.msg);
                            operation_table.ajax.reload();
                            $('#returnVisaModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);

                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });




            $('#final_visa_selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {

                    $('#finalVisaModal').modal('show');

                    $('#bulk_final_edit_form').find('input[name="worker_id[]"]').remove();

                    $.each(selectedRows, function(index, workerId) {
                        var workerIdInput = $('<input>', {
                            type: 'hidden',
                            name: 'worker_id[]',
                            value: workerId
                        });


                        $('#bulk_final_edit_form').append(workerIdInput);
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });

            $('#bulk_final_edit_form').submit(function(e) {

                e.preventDefault();


                var formData = $(this).serializeArray();
                console.log(formData);
                console.log($(this).attr('action'));
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            console.log(response);
                            toastr.success(response.msg);
                            operation_table.ajax.reload();
                            $('#finalVisaModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);

                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });



            $('#absent_report_selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {

                    $('#absentreportModal').modal('show');

                    $('#bulk_absent_edit_form').find('input[name="worker_id[]"]').remove();

                    $.each(selectedRows, function(index, workerId) {
                        var workerIdInput = $('<input>', {
                            type: 'hidden',
                            name: 'worker_id[]',
                            value: workerId
                        });


                        $('#bulk_absent_edit_form').append(workerIdInput);
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });

            $('#bulk_absent_edit_form').submit(function(e) {

                e.preventDefault();
                var formData = $(this).serializeArray();
                console.log(formData);
                console.log($(this).attr('action'));
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            console.log(response);
                            toastr.success(response.msg);
                            operation_table.ajax.reload();
                            $('#absentreportModal').modal('hide');
                        } else {
                            toastr.error(response.msg);
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);

                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });






            function calculateFees(selectedDuration) {
                switch (selectedDuration) {
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

            $('#renew-operation-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = getCheckRecords();
                console.log(selectedRows);

                if (selectedRows.length > 0) {
                    $('#renewOperationModal').modal('show');

                    $.ajax({
                        url: '{{ route('getOperationSelectedworkcardData') }}',
                        type: 'post',
                        data: {
                            selectedRows: selectedRows
                        },

                        success: function(response) {
                            var data = response.data;

                            var durationOptions = response.durationOptions;

                            $('.modal-body').empty();

                            var inputClasses = 'form-group';
                            var inputClasses2 = 'form-group col-md-3';


                            var labelsRow = $('<div>', {
                                class: 'row'
                            });

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:170px; text-align: center; padding: 0 10px;',
                                text: '{{ __('essentials::lang.full_name') }}'
                            }));


                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding: 0 10px;',
                                text: '{{ __('essentials::lang.Residency_no') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding: 0 10px;',
                                text: '{{ __('essentials::lang.Residency_end_date') }}'
                            }));

                            labelsRow.append($('<label>', {
                                class: inputClasses + 'col-md-2',
                                style: 'height: 40px; width:140px; text-align: center; padding: 0 10px;',
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
                                style: 'height: 40px; width:140px; text-align: center;padding: 0 10px;',
                                text: '{{ __('essentials::lang.pay_number') }}'
                            }));



                            $('.modal-body').append(labelsRow);




                            $.each(data, function(index, row) {

                                var rowDiv = $('<div>', {
                                    class: 'row'
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


                                var nameInput = $('<input>', {
                                    type: 'text',
                                    name: 'full_name[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:170px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.full_name') }}',
                                    value: row.name,
                                    readonly: true

                                });

                                rowDiv.append(nameInput);

                                var numberInput = $('<input>', {
                                    type: 'text',
                                    name: 'number[]',
                                    class: inputClasses2 +
                                        ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
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
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.expiration_date') }}',
                                    value: expiration_date
                                });

                                rowDiv.append(expiration_dateInput);

                                function formatDate(dateString) {
                                    var date = new Date(dateString);
                                    var day = date.getDate();
                                    var month = date.getMonth() + 1;
                                    var year = date.getFullYear();


                                    if (day < 10) {
                                        day = '0' + day;
                                    }
                                    if (month < 10) {
                                        month = '0' + month;
                                    }

                                    return year + '-' + month + '-' + day;
                                }

                                var renew_DurationInput_ = $('<select>', {
                                    id: 'renew_operation_durationId_' + index,
                                    name: 'renew_duration[]',
                                    class: 'form-control select2' +
                                        inputClasses2 + ' input-with-padding',
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
                                    required: true
                                });

                                Object.keys(durationOptions).forEach(function(value) {
                                    var option = $('<option>', {
                                        value: value,
                                        text: durationOptions[value]
                                    });

                                    renew_DurationInput_.append(option);
                                });




                                renew_DurationInput_.val(3);

                                rowDiv.append(renew_DurationInput_);




                                var feesInput_ = $('<input>', {
                                    type: 'text',
                                    name: 'fees[]',
                                    class: inputClasses2 +
                                        ' input-with-padding' + ' fees-input',
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.fees') }}',
                                    required: true,
                                    value: row.fees
                                });
                                rowDiv.append(feesInput_);

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
                                    style: 'height: 40px; width:160px; text-align: center; padding: 0 10px; !important',
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


                                $('.modal-body').append(rowDiv);




                                $(document).ready(function() {

                                    var defaultDuration = $(
                                            'select[name="renew_duration[]"]')
                                        .val();


                                    var feesInput = $(
                                            'select[name="renew_duration[]"]')
                                        .closest('.row').find('.fees-input');
                                    var calculatedFees = calculateFees(
                                        defaultDuration);
                                    feesInput.val(calculatedFees);


                                    var passportFeesSelect = $(
                                            'select[name="renew_duration[]"]')
                                        .closest('.row').find(
                                            'select[name="passportfees[]"]');
                                    passportFeesSelect.empty();
                                    var feesOptions = {};
                                    if (defaultDuration === '3') {
                                        feesOptions = {
                                            163: '163',
                                            288: '288',
                                            413: '413',
                                        };
                                    } else if (defaultDuration === '6') {
                                        feesOptions = {
                                            326: '326',
                                            825: '825',
                                        };
                                    } else if (defaultDuration === '9') {
                                        feesOptions = {
                                            488: '488',
                                        };
                                    } else if (defaultDuration === '12') {
                                        feesOptions = {
                                            650: '650',
                                            1150: '1150',
                                        };
                                    }

                                    $.each(feesOptions, function(value, text) {
                                        var option = $('<option>', {
                                            value: value,
                                            text: text
                                        });
                                        passportFeesSelect.append(
                                            option);
                                    });


                                    passportFeesSelect.trigger('change');
                                });



                                $(document).on('change',
                                    'select[name="renew_duration[]"]',
                                    function() {
                                        var selectedDuration = $(this).val();
                                        var feesInput = $(this).closest('.row')
                                            .find('.fees-input');
                                        var calculatedFees = calculateFees(
                                            selectedDuration);
                                        feesInput.val(calculatedFees);

                                        var passportFeesSelect = $(this).closest(
                                            '.row').find(
                                            'select[name="passportfees[]"]');
                                        passportFeesSelect.empty();

                                        var feesOptions = {};
                                        if (selectedDuration === '3') {
                                            feesOptions = {
                                                163: '163',
                                                288: '288',
                                                413: '413',
                                            };
                                        } else if (selectedDuration === '6') {
                                            feesOptions = {
                                                326: '326',
                                                825: '825',
                                            };
                                        } else if (selectedDuration === '9') {
                                            feesOptions = {
                                                488: '488',
                                            };
                                        } else if (selectedDuration === '12') {
                                            feesOptions = {
                                                650: '650',
                                                1150: '1150',
                                            };
                                        }

                                        $.each(feesOptions, function(value, text) {
                                            var option = $('<option>', {
                                                value: value,
                                                text: text
                                            });
                                            passportFeesSelect.append(
                                                option);
                                        });


                                        passportFeesSelect.trigger('change');
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

                }
            });




            $('body').on('submit', '#renew_operation_form', function(e) {
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

                            operation_table.ajax.reload();
                            toastr.success(response.msg);
                            $('#renewOperationModal').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.msg);
                            $('#renewOperationModal').modal('hide');
                            console.log(response);
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting form:', error);
                        toastr.error('An error occurred while submitting the form.', 'Error');
                    },
                });
            });



            $('#nationalities_select, #status-select, #select_business_id').change(
                function() {

                    console.log('Nationality selected: ' + $('#nationalities_select').val());
                    console.log('Status selected: ' + $('#status_filter').val());
                    console.log('loc selected: ' + $('#select_business_id').val());
                    operation_table.ajax.reload();

                });


            function getCheckRecords()

            {
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



@endsection
