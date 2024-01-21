@extends('layouts.app')
@section('title', __('essentials::lang.employee_insurance'))

@section('content')
  
    <section class="content-header">
        <h1>@lang('essentials::lang.employee_insurance')</h1>
    </section>
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addEmployeesInsuranceModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employee_insurance_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.Residency_no')</th>
                                    <th>@lang('essentials::lang.insurance_company')</th>
                                    <th>@lang('essentials::lang.insurance_class')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addEmployeesInsuranceModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'employee_insurance.store']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_Insurance')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                    {!! Form::select('employee', $users, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:36px',
                                        'placeholder' => __('essentials::lang.select_employee'),
                                        'required',
                                        'id' => 'employeeSelect',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('insurance_class', __('essentials::lang.insurance_class') . ':*') !!}
                                     {!! Form::select('insurance_class', $insurance_classes, null, [
                                        'class' => 'form-control',
                                        'style' => 'height:36px',
                                        'placeholder' => __('essentials::lang.insurance_class'),
                                        'required',
                                        'id' => 'classSelect',
                                    ]) !!}
                                  
                                </div>
                                <div id="error-message" class="text-danger"></div>

                                {{--     <div class="form-group col-md-6">
                                    {!! Form::label('insurance_class', __('essentials::lang.insurance_class') . ':*') !!}
                                    {!! Form::select('insurance_class',
                                        ['VIP+'=>'VIP+',
                                        'VIP'=>'VIP',
                                        'A+'=>'A+',
                                        'A'=>'A',
                                        'B+'=>'B+',
                                        'B'=>'B',
                                        'C+'=>'C+',
                                        'C'=>'C',
                                        'CR+'=>'CR+',
                                        'CR'=>'CR',
                                        'VVIP'=>"VVIP",
                                        'A+S'=>'A+S',
                                        'VIP+S'=>'VIP+S',
                                        'C6'=>'C6',
                                        'C4'=>'C4',
                                        'C6S'=>'C6S',
                                        
                                        ],  null,
                                        
                                        ['class' => 'form-control',
                                         'required',
                                        'id' => 'classSelect']) !!}
                                </div>--}}

                            



                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editemployeeInsurance" tabindex="-1" role="dialog">
        </div>
    </section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#addEmployeesInsuranceModal').on('shown.bs.modal', function(e) {
                $('#employeeSelect').select2({
                    dropdownParent: $(
                        '#addEmployeesInsuranceModal'),
                    width: '100%',
                });


            });

            employee_insurance_table = $('#employee_insurance_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee_insurance') }}",

                },

                columns: [
                    {
                        data: 'user'
                    },
                    {
                        data:'proof_number'
                    },
                    {
                        data: 'insurance_company_id'
                    },
                    {
                        data: 'insurance_classes_id'
                    },
                    {
                        data: 'action'
                    },

                ],
            });

            $(document).on('click', 'button.delete_insurance_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_employeeInsurance,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    employee_insurance_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            var employeeSelect = $('#employeeSelect');
            var classSelect = $('#classSelect');

            employeeSelect.on('change', function () {
   
            var selectedEmployee = $(this).val();
               console.log(selectedEmployee);
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '{{ route('classes') }}',
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            employee_id: selectedEmployee
                        },
                        success: function (data) {
                        classSelect.empty();

                        if ('message' in data) {
                            // Handle error message
                            // For example, display it under the select dropdown
                            var errorMessage = data.message;
                            $('#error-message').text(errorMessage).show();
                        } else {
                            // Populate options if no error
                            $.each(data, function (id, name) {
                                classSelect.append($('<option>', {
                                    value: id,
                                    text: name
                                }));
                            });

                            // Hide any previous error messages
                            $('#error-message').hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle AJAX request error here
                        console.error(xhr.responseText);
                    }
                    });

            });
         

           });
    </script>

      

@endsection
