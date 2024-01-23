@extends('layouts.app')
@section('title', __('essentials::lang.employee_families'))

@section('content')
  
    <section class="content-header">
        <h1>@lang('essentials::lang.employee_families')</h1>
    </section>
    <section class="content">


        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">

                            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                                data-target="#addEmployeesFamilyModal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot


                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employees_families_table">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.employee')</th>
                                    <th>@lang('essentials::lang.family')</th>
                                    <th>@lang('essentials::lang.gender')</th>
                                   
                                   
                                    <th>@lang('essentials::lang.relative_relation')</th>
                                    <th>@lang('essentials::lang.eqama_number')</th>
                                    <th>@lang('essentials::lang.address')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="modal fade" id="addEmployeesFamilyModal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => 'storeEmployeeFamily', 'enctype' => 'multipart/form-data']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_family')</h4>
                        </div>


                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                    {!! Form::select('employee', $users, null, [
                                        'class' => 'form-control',
                                        'id' => 'employees_select',
                                        'placeholder' => __('essentials::lang.select_employee'),
                                        'required',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('full_name', __('essentials::lang.full_name') . ':*') !!}
                                    {!! Form::text('full_name', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.full_name'),
                                        'required',
                                    ]) !!}
                                </div>
                               
                              

                                <div class="form-group col-md-6">
                                    {!! Form::label('gender', __('essentials::lang.gender') . ':*') !!}
                                    {!! Form::select(
                                        'gender',
                                        ['male' => __('essentials::lang.male'), 'female' => __('essentials::lang.female')],
                                        null,
                                        ['class' => 'form-control', 'required'],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('relative_relation', __('essentials::lang.relative_relation') . ':*') !!}
                                    {!! Form::select(
                                        'relative_relation',
                                        [
                                            'father' => __('essentials::lang.father'),
                                            'mother' => __('essentials::lang.mother'),
                                            'sibling' => __('essentials::lang.sibling'),
                                            'spouse' => __('essentials::lang.spouse'),
                                            'child' => __('essentials::lang.child'),
                                            'wife' => __('essentials::lang.wife'),
                                            'other' => __('essentials::lang.other'),
                                        ],
                                        null,
                                        ['class' => 'form-control', 'required'],
                                    ) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('eqama_number', __('essentials::lang.eqama_number') . ':*') !!}
                                    {!! Form::text('eqama_number', '2', [
                                        'class' => 'form-control',
                                        'id' => 'eqama_number',
                                        'placeholder' => __('essentials::lang.eqama_number'),
                                    ]) !!}
                                    <div id="idProofNumberError" style="color: red;"></div>
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('address', __('essentials::lang.address') . ':') !!}
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

            $('#addEmployeesFamilyModal').on('shown.bs.modal', function(e) {
                $('#employees_select').select2({
                    dropdownParent: $(
                        '#addEmployeesFamilyModal'),
                    width: '100%',
                });


            });
            employees_families_table = $('#employees_families_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee_families') }}",

                },

                columns: [{
                        data: 'user'
                    },
                    {
                        data: 'family'
                    },

                    
                    {
                        data: 'gender',
                        render: function(data, type, row) {
                            if (data === 'male') {
                                return '@lang('lang_v1.male')';
                            } else if (data === 'female') {
                                return '@lang('lang_v1.female')';

                            } else {
                                return '@lang('lang_v1.others')';
                            }
                        }
                    },
                   
                  
                    {
                        data: 'relative_relation',
                        render: function(data, type, row) {
                            if (data === 'father') {
                                return '@lang('essentials::lang.father')';
                            } else if (data === 'mother') {
                                return '@lang('essentials::lang.mother')';
                            } else if (data === 'sibling') {
                                return '@lang('essentials::lang.sibling')';
                            } else if (data === 'spouse') {
                                return '@lang('essentials::lang.spouse')';
                            } else if (data === 'child') {
                                return '@lang('essentials::lang.child')';
                            }
                            else if (data === 'wife') {
                                return '@lang('essentials::lang.wife')';
                            } else {
                                return '@lang('essentials::lang.other')';
                            }
                        }
                    },


                    {
                        data: 'eqama_number'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: 'action'
                    },
                ],
            });

            $(document).on('click', 'button.delete_employee_families_button', function() {
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
                            success: function(result) {
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
        document.addEventListener('DOMContentLoaded', function() {
            var eqamaNumberInput = document.getElementById('eqama_number');
            var idProofNumberError = document.getElementById('idProofNumberError');

            eqamaNumberInput.addEventListener('input', function() {
                var inputValue = eqamaNumberInput.value;
                if (/^2\d{0,9}$/.test(inputValue)) {
                    idProofNumberError.textContent = '';
                } else {
                    idProofNumberError.textContent = 'رقم الإقامة يجب أن يبدأ ب 2 ويحتوي فقط 10 خانات';

                    var validInput = inputValue.match(/^2\d{0,9}/);
                    eqamaNumberInput.value = validInput ? validInput[0] : '2';
                }

                if (idProofNumberError.textContent === '') {
                    idProofNumberError.textContent = '';
                }
            });
        });
    </script>






@endsection
