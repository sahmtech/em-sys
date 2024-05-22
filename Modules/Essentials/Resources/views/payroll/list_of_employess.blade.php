@extends('layouts.app')
@section('title', __('essentials::lang.list_of_emp'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('essentials::lang.list_of_emp')</span>
        </h1>
    </section>


    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="business_filter">@lang('essentials::lang.business_single'):</label>
                            {!! Form::select('select_company_id', $companies, null, [
                                'class' => 'form-control select2',
                                'id' => 'select_company_id',
                                'style' => 'height:40px; width:100%',
                               
                              
                                'multiple'=>'multiple',
                                'autofocus',
                                 'data-placeholder' => __('lang_v1.all')
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                            {!! Form::select('project_name_filter', $contacts_fillter, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;padding:2px;',
                                
                                 'data-placeholder' => __('lang_v1.all')
                            ]) !!}

                        </div>
                    </div>


                     <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('user_type', __('essentials::lang.user_type') . ':') !!}
                                    {!! Form::select('user_type', $user_types, null, [
                                        'class' => 'form-control select2',
                                        'style' => 'width: 100%;',
                                        'id' => 'user_type',
                                      
                                         'placeholder' => __('lang_v1.all'),
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
                         
                           
                            <td >@lang('essentials::lang.employee_number')</td>
                            <td >@lang('followup::lang.name')</td>
                            <td >@lang('followup::lang.user_type')</td>
                              <td >@lang('essentials::lang.company_name')</td>
                            <td >@lang('followup::lang.eqama')</td>
                            <td >@lang('followup::lang.eqama_end_date')</td>

                             <td >@lang('essentials::lang.border_number')</td>
                            <td >@lang('followup::lang.nationality')</td>

                            <td >@lang('followup::lang.bank_code')</td>
                            <td >@lang('essentials::lang.bank_name')</td>
                          
                          
                           <td >@lang('followup::lang.project_name')</td>
                           <td >@lang('essentials::lang.project_assigner')</td>
                           <td >@lang('essentials::lang.salary_voucher')</td>
                           <td >@lang('essentials::lang.actions')</td>
                           


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent


   
    </section>
 @include('essentials::payroll.partials.project_info')
 @include('essentials::payroll.partials.editVoucherModal')
 @include('essentials::payroll.partials.salary_info')


@endsection

@section('javascript')
    <script>
        $(document).ready(function() {

            $('#status_fillter').select2();

            var workers_table = $('#workers_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {

                    url: "{{ route('list_of_employess') }}",

                    data: function(d) {
                        if ($('#project_name_filter').val()) {
                            d.project_name = $('#project_name_filter').val();
                        }

                // var selectedProjects = $('#project_name_filter').val();
                // if (selectedProjects && selectedProjects.length > 0) {
                //     d.project_name = selectedProjects; // Send selected company IDs as an array
                // } else {
                //     d.company = []; // Send an empty array if no company is selected
                // }
                       
                var selectedCompanies = $('#select_company_id').val();
                if (selectedCompanies && selectedCompanies.length > 0) {
                    d.company = selectedCompanies; // Send selected company IDs as an array
                } else {
                    d.company = []; // Send an empty array if no company is selected
                }

                          if ($('#user_type').val()) {
                            d.user_type = $('#user_type').val();
                        }
                       
                    }
                },

                columns: [
                  
                    {
                        "data": "emp_number"
                    },


                    {
                        data: 'worker',
                       
                    },
                      {
                        data: 'user_type',
                        render: function(data, type, row) {

                            if (data === 'employee') {
                                return '@lang('essentials::lang.employee')';
                            } else if (data === 'worker') {
                                return '@lang('essentials::lang.worker')';
                            } else if(data === 'remote_employee') {
                                return '@lang('essentials::lang.remote_employee')';
                            }
                        }
                       
                    },
                      {
                        data: "company_name"
                    },
                    {
                        data: 'id_proof_number'
                    },

                     {
                        data: 'residence_permit_expiration'
                    },
                     {
                        data: 'border_no'
                    }, 
                     {
                        data: 'nationality'
                    },

                    
                    {
                        data: 'bank_code',

                    },
                    
                    {
                        data: 'bank_name',

                    },
                    {
                        data: 'contact_name',

                    },
                    {
                        data: 'project_assigner',

                    },
                      {
                        data: 'salary_voucher',
                      
                         
                    },
                     {
                        data: 'actions',

                    },
                  

                ]
            });
          
            $('#project_name_filter,#user_type,#select_company_id')
                .on('change',
                    function() {
                        workers_table.ajax.reload();
                    });
        });
       
    </script>

<script>
$(document).on('click', '#view_worker_project', function(e) {
    e.preventDefault();
    var workerId = $(this).data('worker-id');
    var url = $(this).data('href');
    console.log(url);

    $.ajax({
        url: url,
        type: 'GET',
        data: { worker_id: workerId },
        success: function(response) {
            var data = response.data;
            console.log(data);
            
            var projectHtml = '<table class="table table-bordered table-striped">';
            projectHtml += '<tr><th>@lang('essentials::lang.project_name')</th><td>' + data.project.name + '</td></tr>';
            projectHtml += '<tr><th>@lang('essentials::lang.contract_start_date')</th><td>' + data.contract_start_date + '</td></tr>';
            projectHtml += '<tr><th>@lang('essentials::lang.contract_end_date')</th><td>' + data.contract_end_date + '</td></tr>';
            projectHtml += '<tr><th>@lang('essentials::lang.contact_location')</th><td>' + data.contact_location + '</td></tr>';
            projectHtml += '<tr><th>@lang('essentials::lang.project_manager')</th><td>' + data.project_manager + '</td></tr>';
            projectHtml += '</table>';
           
            $('#projectModalBody').html(projectHtml);
            $('#projectModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});



$(document).on('click', '#view_salary_info', function(e) {
    e.preventDefault();
    var workerId = $(this).data('worker-id');
    var url = $(this).data('href');
    console.log(url);

    $.ajax({
        url: url,
        type: 'GET',
        data: { worker_id: workerId },
        success: function(response) {
            var data = response.data;
            
            console.log(data);

            var salaryHtml = '<table class="table table-bordered table-striped">';
            salaryHtml += '<tr><th name="user_id" style="display:none"></th><td style="display:none">' + data.user_id + '</td></tr>';
            salaryHtml += '<tr><th name="work_days">@lang('essentials::lang.work_days')</th><td>' + data.work_days + '</td></tr>';
            salaryHtml += '<tr><th name="salary">@lang('essentials::lang.salary')</th><td>' + data.salary + '</td></tr>';
            salaryHtml += '<tr><th name="total">@lang('essentials::lang.total')</th><td>' + data.total + '</td></tr>';
            salaryHtml += '<tr><th name="housing_allowance">@lang('essentials::lang.housing_allowance')</th><td>' + data.housing_allowance + '</td></tr>';
            salaryHtml += '<tr><th name="transportation_allowance">@lang('essentials::lang.transportation_allowance')</th><td>' + data.transportation_allowance + '</td></tr>';
            salaryHtml += '<tr><th name="other_allowance">@lang('essentials::lang.other_allowance')</th><td>' + data.other_allowance + '</td></tr>';
            salaryHtml += '</table>';
            salaryHtml += '<button class="btn btn-sm btn-primary edit-salary" data-worker-id="' + workerId + '">@lang('essentials::lang.edit')</button>';

            $('#salaryModalBody').html(salaryHtml);
            $('#salaryInfoModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});


$(document).on('click', '.edit-salary', function(e) {
    e.preventDefault();
    
    
    $('#salaryModalBody td').each(function() {
        var value = $(this).text(); 
        $(this).html('<input type="text" class="form-control" value="' + value + '">'); 
    });

    
    $(this).replaceWith('<button class="btn btn-sm btn-success save-salary">@lang('essentials::lang.save')</button>');
});

$(document).on('click', '.save-salary', function(e) {
    e.preventDefault();
    
    var updatedData = {};
    $('#salaryModalBody input').each(function() {
          var columnName = $(this).closest('tr').find('th').attr('name');
          var value = $(this).val(); 
        updatedData[columnName] = value; 
        $(this).replaceWith('<td>' + value + '</td>'); 
    });

    
    $.ajax({
        url: '{{route('payrolls.update.salary')}}', 
        type: 'POST', 
        data: updatedData, 
        success: function(response) {
              $('#salaryInfoModal').modal('hide');
               location.reload();
            
            console.log('Data updated successfully:', response);
        },
        error: function(xhr, status, error) {
            
            console.error('Error updating data:', xhr.responseText);
        }
    });
});





$(document).on('click', '.edit-salary-voucher', function(e) {
    e.preventDefault();
    var userId = $(this).data('user-id'); 
    console.log(userId);

    var selectedStatus = $(this).val();

    var modal = $('#editVoucherModal');
    modal.find('#userId').val(userId); 

    
    modal.modal('show');
});



$(document).on('click', '#saveVoucherStatus', function() {
    var userId = $('#editVoucherForm input[name="userId"]').val();
    var newStatus = $('#voucherStatus').val();
 
    
    $.ajax({
        url: '{{route('payrolls.update-voucher-status')}}',
        type: 'POST',
        data: {
            user_id: userId,
            status: newStatus
        },
        success: function(response) {
            
            $('.edit-salary-voucher[data-user-id="' + userId + '"]').text(response.status);
            $('#editVoucherModal').modal('hide');
             workers_table.ajax.reload();
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            
        }
    });
});
</script>



@endsection


