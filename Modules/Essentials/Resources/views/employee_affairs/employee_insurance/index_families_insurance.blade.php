@extends('layouts.app')
@section('title', __('essentials::lang.families_insurance'))

@section('content')
@include('essentials::layouts.nav_employees_insurance')
    <section class="content-header">
        <h1>@lang('essentials::lang.families_insurance')</h1>
    </section>
    <section class="content">
  

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])

                @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.add_families_insurances'))
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addFamilyInsuranceModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                @endif


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="family_insurance_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.family')</th>
                                    <th>@lang('essentials::lang.employee_assignto_family')</th>
                                    <th>@lang('essentials::lang.Birth_date')</th>
                                    <th>@lang('essentials::lang.fixed')</th>
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
           
          

            
        </div>
    </section>
@endsection


@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#addFamilyInsuranceModal').on('shown.bs.modal', function(e) {
                $('#employeeSelect').select2({
                    dropdownParent: $(
                        '#addFamilyInsuranceModal'),
                    width: '100%',
                });


            });

            family_insurance_table = $('#family_insurance_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('families_insurance') }}",

                },

                columns: [
                    {
                        data: 'family'
                    },
                    {
                        data: 'user'
                    },
                    {
                        data: 'dob'
                    },
                    {
                        data: 'fixnumber'
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
                                    family_insurance_table.ajax.reload();
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
                        console.log(data);

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
                            

                            
                            $('#error-message').hide();
                        }
                    },
                    error: function (xhr, status, error) {
                    
                        console.error(xhr.responseText);
                    }
                    });

            });
         

           });
    </script>

      

@endsection