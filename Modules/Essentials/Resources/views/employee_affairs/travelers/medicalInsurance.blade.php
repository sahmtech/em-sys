@extends('layouts.app')
@section('title', __('housingmovements::lang.medicalInsurance'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.medicalInsurance')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('essentials::employee_affairs.layouts.nav_trevelers')
       

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                  
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="medicalInsurance_table">
                            <thead>
                                <tr>
                                    {{-- <th>
                                        <input type="checkbox" id="select-all">
                                    </th> --}}
                                    <th>#</th>
                                    <th>@lang('housingmovements::lang.worker_name')</th>
                                    <th>@lang('housingmovements::lang.medicalInsurance')</th>
                                 
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>



                        </table>
                      
                    </div>
                 
                @endcomponent
                <!-- Add Insurance Modal -->
                <div class="modal fade" id="addInsuranceModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'employee_insurance.store', 'id'=>"addInsuranceForm"]) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_Insurance')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <input type="hidden" name="employee">
                                <div class="form-group col-md-6">
                        {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                        {!! Form::select('insurance_company', $insurance_companies, null, [
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('essentials::lang.insurance_company'),
                            'required' => 'required',
                            'id' => 'companySelect'
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('insurance_class', __('essentials::lang.insurance_class') . ':*') !!}
                        {!! Form::select('insurance_class', [], null, [  
                            'class' => 'form-control select2',
                            'style' => 'height:40px',
                            'placeholder' => __('essentials::lang.insurance_class'),
                            'required' => 'required',
                            'id' => 'classSelect'
                        ]) !!}
                    </div>
                                <div id="error-message" class="text-danger"></div>

                                

                            



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
                <!-- View Insurance Modal -->
                <div class="modal fade" id="viewInsuranceModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          
                            <div class="modal-header">
                                <h4 class="modal-title">@lang('housingmovements::lang.view_insurance_info')</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>@lang('essentials::lang.Residency_no'): <span class="id-proof-number"></span></p>
                                <p>@lang('essentials::lang.border_no'): <span class="border-number"></span></p>
                                <p>@lang('essentials::lang.insurance_company'): <span class="insurance-company"></span></p>
                                <p>@lang('essentials::lang.insurance_class'): <span class="insurance-class"></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            



        </div>

   
    </section>

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var medicalInsurance_table = $('#medicalInsurance_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('emp_medicalInsurance') }}',
            },
            columns: [
                // {
                //     data: null,
                //     render: function(data, type, row) {
                //         return '<input type="checkbox" class="select-row" data-id="' + row.id + '">';
                //     },
                //     orderable: false,
                //     searchable: false,
                // },
                {
                    data: 'id',
                },
                {
                    data: 'full_name',
                },
                {
                    data: 'has_insurance',
                    render: function(data, type, row) {
                        console.log(data);
                        return data === 1 ? '@lang('housingmovements::lang.has_insurance')' : '@lang('housingmovements::lang.not_yet')';
                    }
                },
                {
                    data: 'action', 
                    name: 'action', 
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#select-all').change(function() {
            $('.select-row').prop('checked', $(this).prop('checked'));
        });

        $('#medicalExamination_table').on('change', '.select-row', function() {
            $('#select-all').prop('checked', $('.select-row:checked').length === medicalExamination_table.rows().count());
        });

        window.addFile = function(workerId) {
            const input = document.createElement('input');
            input.type = 'file';
            input.onchange = e => {
                const file = e.target.files[0];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('workerId', workerId);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('uploadMedicalDocument') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                 
                        $('#medicalExamination_table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert('Error uploading file.');
                    }
                });
            };
            input.click(); 
        };

     
    });
</script>
<script>
    function addInsurance(workerId) {
        $('#addInsuranceModal').modal('show');
        $('#addInsuranceForm').find('input[name="employee"]').val(workerId);
    }
    
    function viewInsurance(workerId) {
        $.ajax({
    url: '/medicalInsurance/get-insurance-info', 
    type: 'GET',
    data: { employee_id: workerId },
    success: function(response) {
        if (response.success) {
            $('#viewInsuranceModal').find('.id-proof-number').text(response.id_proof_number || '');
            $('#viewInsuranceModal').find('.border-number').text(response.border_no || '');
            $('#viewInsuranceModal').find('.insurance-company').text(response.insurance_company || '');
            $('#viewInsuranceModal').find('.insurance-class').text(response.insurance_class || '');
            $('#viewInsuranceModal').modal('show');
        } else {
            alert('No insurance information found.'); // Optionally, handle this more gracefully
        }
    },
    error: function() {
        alert('Failed to retrieve insurance information.'); // Optionally, handle this more gracefully
    }
});

    }

    $('#companySelect').change(function(){
        var companyId = $(this).val(); 
        if (companyId) {
            console.log(companyId);
            $.ajax({
                url: '/medicalInsurance/get-insurance-classes/' + companyId,

                type: 'GET',
                success: function(data) {
                    $('#classSelect').empty(); 
                    $('#classSelect').append('<option value="">' + '{{ __('essentials::lang.insurance_class') }}' + '</option>');

                    $.each(data, function(key, value) {
                        $('#classSelect').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                }
            });
        } else {
            $('#classSelect').empty();
        }
    });

    </script>


@endsection
