@extends('layouts.app')
@section('title', __('essentials::lang.employee_families'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.employee_families')</h1>
</section>
<section class="content">
   

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addEmployeesFamilyModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="employees_families_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.employee' )</th>
                                <th>@lang('essentials::lang.family' )</th>
                                <th>@lang('essentials::lang.gender' )</th>
                                <th>@lang('essentials::lang.age' )</th>
                                <th>@lang('essentials::lang.relative_relation' )</th>
                                <th>@lang('essentials::lang.eqama_number' )</th>
                                <th>@lang('essentials::lang.address')</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addEmployeesFamilyModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeEmployeeFamily' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_family')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('first_name', __('essentials::lang.first_name') . ':*') !!}
                                {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.first_name'), 'required']) !!}
                            </div> 
                            <div class="form-group col-md-6">
                                {!! Form::label('last_name', __('essentials::lang.last_name') . ':*') !!}
                                {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.last_name'), 'required']) !!}
                            </div> 
                            <div class="form-group col-md-6">
                                {!! Form::label('age', __('essentials::lang.age') . ':*') !!}
                                {!! Form::number('age', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.age')]) !!}
                            </div>  
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('gender', __('essentials::lang.gender') . ':*') !!}
                                {!! Form::select('gender', ['male' => __('essentials::lang.male'), 'female' => __('essentials::lang.female')], null, ['class' => 'form-control', 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('relative_relation', __('essentials::lang.relative_relation') . ':*') !!}
                                {!! Form::select('relative_relation', [
                                    'father' => __('essentials::lang.father'), 
                                    'mother' => __('essentials::lang.mother'),
                                    'sibling' => __('essentials::lang.sibling'),
                                    'spouse' => __('essentials::lang.spouse'),
                                    'child' => __('essentials::lang.child'),
                                    'other' => __('essentials::lang.other'),
                                    
                                    ], null, ['class' => 'form-control','required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('eqama_number', __('essentials::lang.eqama_number') . ':*') !!}
                                {!! Form::number('eqama_number', null, ['class' => 'form-control', 'id' => 'eqama_number', 'pattern' => "21\d{8}", 'placeholder' => __('essentials::lang.eqama_number')]) !!}
                                <div id="idProofNumberError" style="color: red;"></div>
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('address', __('essentials::lang.address') . ':*') !!}
                                {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address')]) !!}
                            </div> 
                           
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
    </div>
</section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
  

            employees_families_table  = $('#employees_families_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee_families') }}",
                   
                },
                
                columns: [
                        { data: 'user' },
                        { data: 'family' },
                      
                        { 
                        data: 'gender',
                            render: function (data, type, row) {
                                if (data === 'male') {
                                    return  '@lang('essentials::lang.male')';
                                } else {
                                    return  '@lang('essentials::lang.female')';
                                }
                            }
                        },
                        { data: 'age' },
                        { 
                        data: 'relative_relation',
                            render: function (data, type, row) {
                                if (data === 'father') {
                                    return  '@lang('essentials::lang.father')';
                                } 
                                else if(data === 'mother') {
                                    return  '@lang('essentials::lang.mother')';
                                }
                                else if(data === 'sibling') {
                                    return  '@lang('essentials::lang.sibling')';
                                }
                                else if(data === 'spouse') {
                                    return  '@lang('essentials::lang.spouse')';
                                }
                                else if(data === 'child') {
                                    return  '@lang('essentials::lang.child')';
                                }
                                
                                else {
                                    return  '@lang('essentials::lang.other')';
                                }
                            }
                        },
                       

                        { data: 'eqama_number' },
                        { data: 'address' },
                        { data: 'action' },
                    ],
             });

            $(document).on('click', 'button.delete_employee_families_button', function () {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_employeeFamily,
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
                            success: function (result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    employees_families_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
    
         


            
        });
    
    </script>
    <script>
       
        const eqamaNumberInput = document.getElementById('eqama_number');
        const idProofNumberError = document.getElementById('idProofNumberError');
    
        eqamaNumberInput.addEventListener('input', function() {
            const eqamaNumber = eqamaNumberInput.value;
    
            if (eqamaNumber.length !== 10) {
                idProofNumberError.innerText = 'Eqama number must be 10 digits.';
            } else if (!/^21\d{8}$/.test(eqamaNumber)) {
                idProofNumberError.innerText = 'Eqama number must start with "21" and be followed by 8 digits.';
            } else {
                idProofNumberError.innerText = '';
            }
        });
    
    </script>
@endsection

