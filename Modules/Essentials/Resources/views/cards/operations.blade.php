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
                    <label for="business_filter">@lang('essentials::lang.business_single'):</label>
                    {!! Form::select(
                        'select_business_id',
                        $businesses,
                        null,
                        [
                            'class' => 'form-control select2',
                            'id' => 'select_business_id',
                            'style' => 'height:36px; width:100%',
                            'placeholder' => __('lang_v1.all'),
                            'required',
                            'autofocus',
                        ],
                        
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="specializations_filter">@lang('essentials::lang.major'):</label>
                    {!! Form::select('specializations-select', $specializations, request('specializations-select'), [
                        'class' => 'form-control select2',
                        'style' => 'height:36px; width:100%',
                        'placeholder' => __('lang_v1.all'),
                        'id' => 'specializations-select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="nationalities_filter">@lang('essentials::lang.nationality'):</label>
                    {!! Form::select('nationalities_select', $nationalities, request('nationalities_select'), [
                        'class' => 'form-control select2', 
                        'placeholder' => __('lang_v1.all'),
                        'style' => 'height:36px; width:100%',
                        'id' => 'nationalities_select',
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('essentials::lang.status'):</label>
                    <select class="form-control select2" name="status_filter" required id="status_filter"
                        style="height:36px; width:100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="active">@lang('sales::lang.active')</option>
                        <option value="inactive">@lang('sales::lang.inactive')</option>
                        <option value="terminated">@lang('sales::lang.terminated')</option>
                        <option value="vecation">@lang('sales::lang.vecation')</option>


                    </select>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
          

        
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="employees">
                        <thead>
                            <tr>
                            <th>
                            <input type="checkbox" class="largerCheckbox" id="chkAll" />
                           </th>
                           <th>#</th>
                          
                            <th>@lang('essentials::lang.profile_image')</th>
                                <th>@lang('essentials::lang.employee_number')</th>
                               
                                <th>@lang('essentials::lang.employee_name')</th>
                             
                                <th>@lang('essentials::lang.Identity_proof_id')</th>
                                <th>@lang('essentials::lang.contry_nationality')</th>
                                <th>@lang('essentials::lang.total_salary')</th>
                                <th>@lang('essentials::lang.admissions_date')</th>
                                <th>@lang('essentials::lang.contract_end_date')</th>

                                <th>@lang('essentials::lang.department')</th>
                                <th>@lang('essentials::lang.profession')</th>
                                <th>@lang('essentials::lang.specialization')</th>
                                <th>@lang('essentials::lang.mobile_number')</th>
                                <th>@lang('business.email')</th>
                                <th>@lang('essentials::lang.status')</th>
                                <th>@lang('messages.view')</th>
                               
                            </tr>
                        </thead>

                        
                        <tfoot>
                            <tr>
                                <td colspan="17">
                                    <div style="display: flex; width: 100%;">

                                        &nbsp;

                                    
                                        <button type="submit" class="btn btn-xs btn-warning" id="return_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.return_visa') }}
                                        </button>

                                        &nbsp;

                                    
                                        <button type="submit" class="btn btn-xs btn-success" id="final_visa_selected">
                                            <i class="fa fa"></i>{{ __('essentials::lang.final_visa') }}
                                        </button>

                                        &nbsp;

                                    
                                        <button type="submit" class="btn btn-xs btn-danger" id="absent_report_selected">
                                            <i class="fa fa-warning"></i>{{ __('essentials::lang.absent_report') }}
                                        </button>

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
    </section>
    <!-- /.content -->
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
            var users_table = $('#employees').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('work_cards_operation') }}",
                    data: function(d) {
                        d.specialization = $('#specializations-select').val();
                        d.nationality = $('#nationalities_select').val();
                        d.status = $('#status_filter').val();
                        d.business = $('#select_business_id').val();

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
                   {data:'id'},
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
                        "data": "full_name"
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
                        "data": "specialization",
                        name: 'specialization'
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
                            $('td', row).eq(7).addClass('text-danger'); 
                        } else if (daysRemaining <= 25) {
                            $('td', row).eq(7).addClass(
                                'text-warning'); 
                        }
                    }
                }

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
                } 
                
                else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
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
                } 
                
                else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
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
                users_table.ajax.reload();
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
                users_table.ajax.reload();
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
                users_table.ajax.reload();
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


            $('#specializations-select, #nationalities_select, #status-select, #select_business_id').change(
                function() {
                    console.log('Specialization selected: ' + $(this).val());
                    console.log('Nationality selected: ' + $('#nationalities_select').val());
                    console.log('Status selected: ' + $('#status_filter').val());
                    console.log('loc selected: ' + $('#select_business_id').val());
                    users_table.ajax.reload();

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



@endsection