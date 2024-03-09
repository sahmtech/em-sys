@extends('layouts.app')
@section('title', __('essentials::lang.work_cards_operation'))

@section('content')
 
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('essentials::lang.work_cards_operation')
        </h1>
        <!-- <ol class="breadcrumb">
                                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                    <li class="active">Here</li>
                                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
    @component('components.filters', ['title' => __('report.filters')])


            <div class="col-md-3">
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
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="business_filter">@lang('essentials::lang.business_single'):</label>
                    {!! Form::select(
                        'select_business_id',
                        $companies,
                        null,
                        [
                            'class' => 'form-control select2',
                            'id' => 'select_business_id',
                            'style' => 'height:40px; width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'required',
                            'autofocus',
                        ],
                        
                    ) !!}
                </div>
            </div>
            {{-- <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.major'):</label>
                    {!! Form::select('specializations-select', $specializations, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:40px; width:100%',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div> --}}



            <div class="col-md-3">
                <div class="form-group">
                    <label for="nationalities_filter">@lang('essentials::lang.nationality'):</label>
                    {!! Form::select('nationalities_select', $nationalities, request('nationalities_select'), [
                        'class' => 'form-control select2', 
                        'placeholder' => __('lang_v1.all'),
                        'style' => 'height:40px; width:100%',
                        'id' => 'nationalities_select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('essentials::lang.status'):</label>
                    <select class="form-control select2" name="status_filter" required id="status_filter"
                        style="height:40px; width:100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="active">@lang('sales::lang.active')</option>
                        <option value="inactive">@lang('sales::lang.inactive')</option>
                        <option value="terminated">@lang('sales::lang.terminated')</option>
                        <option value="vecation">@lang('sales::lang.vecation')</option>


                    </select>
                </div>
            </div>

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
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
          

        
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="operation_table">
                        <thead>
                            <tr>
                            <th>
                            <input type="checkbox" class="largerCheckbox" id="chkAll" />
                           </th>
                         
                          
                            <th>@lang('essentials::lang.profile_image')</th>
                                <th>@lang('essentials::lang.employee_number')</th>
                               
                                <th>@lang('essentials::lang.employee_name')</th>
                                <th>@lang('essentials::lang.project')</th>
                                <th>@lang('essentials::lang.Identity_proof_id')</th>
                                <th>@lang('essentials::lang.contry_nationality')</th>
                                <th>@lang('essentials::lang.total_salary')</th>
                                <th>@lang('essentials::lang.admissions_date')</th>
                                <th>@lang('essentials::lang.contract_end_date')</th>

                                <th>@lang('essentials::lang.department')</th>
                                <th>@lang('essentials::lang.profession')</th>
                                <th>@lang('essentials::lang.mobile_number')</th>
                                <th>@lang('business.email')</th>
                                <th>@lang('essentials::lang.status')</th>
                                <th>@lang('messages.view')</th>
                               
                            </tr>
                        </thead>

                        
                        <tfoot>
                            <tr>
                                <td colspan="16">
                                    <div style="display: flex; width: 100%;">

                                        &nbsp;

                                    @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("essentials.view_return_visa"))
                                        <button type="submit" class="btn btn-xs btn-warning" id="return_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.return_visa') }}
                                        </button>
                                    @endif
                                        &nbsp;

                                     @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("essentials.view_final_visa"))
                                        <button type="submit" class="btn btn-xs btn-success" id="final_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.final_visa') }}
                                        </button>
                                    @endif
                                        &nbsp;

                                     @if(auth()->user()->hasRole("Admin#1") || auth()->user()->can("essentials.view_absent_report"))
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
                        </tfoot>



                    </table>
                </div>
          
               
        @endcomponent
<div class="col-md-8 selectedDiv" style="display:none;">
</div>    
@include('essentials::cards.partials.return_visa_modal')
   
@include('essentials::cards.partials.final_visa_modal') 
   
@include('essentials::cards.partials.absent_report_modal') 

@include('essentials::cards.partials.renew_operation_modal')
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
                    url: "{{ route('work_cards_operation') }}",
                    data: function(d) {
                    
                        d.nationality = $('#nationalities_select').val();
                        d.status = $('#status_filter').val();
                        d.business = $('#select_business_id').val();
                        d.proof_numbers = $('#proof_numbers_select').val();
                        d.project = $('#contact-select').val();

                        console.log(d);
                    },
                },


                "columns": [
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
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
                        "data": "emp_number"
                    },

                    {
                        "data": "full_name",
                        "render": function(data, type, row) {
                            if (data) {
                                data = '<a href="/work_cards/operations_show_employee/' + row.id + '">' + data + '</a>';
                            }
                            return data;
                        }
                    },
                    {
                        data:"project"
                    },
            
                    {
                        "data": "id_proof_number"
                    },
                    {
                        "data": "nationality"
                    },
                    {
                        "data": "total_salary"
                    },
                    {
                        "data": "admissions_date"
                    },
                    {
                        "data": "contract_end_date"
                    },

                    {
                        "data": "essentials_department_id"
                    },
                    {
                        "data": "profession",
                        name: 'profession'
                    },
                   
                    {
                        "data": "contact_number"
                    },
                    {
                        "data": "email"
                    },
                    {
                        data: 'status',
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
                    {
                        "data": "view"
                    },
                   
                ],
                "createdRow": function(row, data, dataIndex) {
                    var contractEndDate = data.contract_end_date;
                    console.log(contractEndDate);
                    var currentDate = moment().format("YYYY-MM-DD");

                    if (contractEndDate !== null && contractEndDate !== undefined) {
                        var daysRemaining = moment(contractEndDate).diff(currentDate, 'days');

                        if (daysRemaining <= 0) {
                            $('td', row).eq(9).addClass('text-danger'); 
                        } else if (daysRemaining <= 25) {
                            $('td', row).eq(9).addClass(
                                'text-warning'); 
                        }
                    }
                }

            });

            $('#proof_numbers_select').on('change', function() {
                console.log( 'proof',$('#proof_numbers_select').val());
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
                } 
                
                else {
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
                    success: function (response) {
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
                        error: function (error) {
                            console.error('Error submitting form:', error);
                            
                            toastr.error('An error occurred while submitting the form.', 'Error');
                        },
                });
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
                        success: function (response) {
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
                            error: function (error) {
                                console.error('Error submitting form:', error);
                                
                                toastr.error('An error occurred while submitting the form.', 'Error');
                            },
                    });
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
                        success: function (response) {
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
                            error: function (error) {
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
                                    class:  inputClasses2 + ' input-with-padding', 
                                    style: 'height: 40px',
                                    placeholder: '{{ __('essentials::lang.id') }}',
                                    value: row.employee_id
                                });
                                rowDiv.append(workerIDInput);


                                var nameInput = $('<input>', {
                                    type: 'text',
                                    name: 'full_name[]',
                                    class: inputClasses2 + ' input-with-padding', 
                                    style: 'height: 40px; width:170px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.full_name') }}',
                                    value: row.name,
                                    readonly: true 
                                   
                                });

                                rowDiv.append(nameInput);

                                var numberInput = $('<input>', {
                                    type: 'text',
                                    name: 'number[]',
                                    class: inputClasses2 + ' input-with-padding', 
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.Residency_no') }}',
                                    value: row.number,
                                    readonly: true 
                                   
                                });

                                rowDiv.append(numberInput);
                                
                                var expiration_date = row.expiration_date ? formatDate(row.expiration_date) : '';
                                console.log(expiration_date);
                                var expiration_dateInput = $('<input>', {
                                    type: 'text',
                                    name: 'expiration_date[]',
                                    class: inputClasses2 + ' input-with-padding', 
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
                                    class: 'form-control select2' +  inputClasses2 + ' input-with-padding', 
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
                                    class:  inputClasses2 + ' input-with-padding'+' fees-input',
                                    style: 'height: 40px; width:140px; text-align: center;padding: 0 10px; !important',
                                    placeholder: '{{ __('essentials::lang.fees') }}',
                                    required: true ,
                                    value: row.fees
                                });
                                rowDiv.append(feesInput_);

                                 var passportFeesSelect = $('<select>', {
                                    name: 'passportfees[]',
                                    class: inputClasses2 + ' input-with-padding fees-input',
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
                                        class: inputClasses2 + ' input-with-padding',
                                        style: 'height: 40px; width:160px; text-align: center; padding: 0 10px; !important',
                                        placeholder: '{{ __('essentials::lang.pay_number') }}',
                                        value: row.Payment_number
                                    });

                                    
                                     
                                   pay_numberInput.on('input', function() {
                                            var currentValue = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
                                            if (currentValue.length !== 14) {
                                                // If not exactly 14 digits, show error message
                                                $('#error-message').text('You must enter exactly 14 numbers').show();
                                            } else {
                                                // If exactly 14 digits, hide error message
                                                $('#error-message').hide();
                                            }
                                        });


                                    rowDiv.append(pay_numberInput);


                                $('.modal-body').append(rowDiv);
                                
                              


                                $(document).ready(function() {
                                
                                var defaultDuration = $('select[name="renew_duration[]"]').val();

                                
                                var feesInput = $('select[name="renew_duration[]"]').closest('.row').find('.fees-input');
                                var calculatedFees = calculateFees(defaultDuration);
                                feesInput.val(calculatedFees);

                                
                                var passportFeesSelect = $('select[name="renew_duration[]"]').closest('.row').find('select[name="passportfees[]"]');
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
                                    passportFeesSelect.append(option);
                                });

                                
                                passportFeesSelect.trigger('change');
                            });


                               
                              $(document).on('change', 'select[name="renew_duration[]"]', function() {
                                var selectedDuration = $(this).val();
                                var feesInput = $(this).closest('.row').find('.fees-input');
                                var calculatedFees = calculateFees(selectedDuration);
                                feesInput.val(calculatedFees);

                                var passportFeesSelect = $(this).closest('.row').find('select[name="passportfees[]"]');
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
                                    passportFeesSelect.append(option);
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



            
            $('body').on('submit', '#renew_operation_form', function (e) {
                    e.preventDefault();
                    var urlWithId = $(this).attr('action');
                    $.ajax({
                        url: urlWithId,
                        type: 'POST',
                        data: new FormData(this),
                        contentType: false,
                        processData: false,
                        success: function (response) {
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
                        error: function (error) {
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
