@extends('layouts.app')
@section('title', __('essentials::lang.employee_families'))

@section('content')
    {{-- @include('essentials::layouts.nav_employee_affairs') --}}
    <section class="content-header">
        <h1>@lang('essentials::lang.employee_families')</h1>
    </section>

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => ['updateEmployeeFamily', $family->id], 'method' => 'put', 'id' => 'add_family_form']) !!}


            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.edit_family')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                        {!! Form::select('employee', $users, $family->employee_id, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.select_employee'),
                            'required',
                            'id' => 'employee_select',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('full_name', __('essentials::lang.full_name') . ':*') !!}
                        {!! Form::text('full_name', $family->full_name, [
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
                            $family->gender,
                            ['class' => 'form-control', 'required'],
                        ) !!}
                    </div>
                      <div class="form-group col-md-6">
                                    {!! Form::label('dob', __('essentials::lang.dob') . ':') !!}
                                    {!! Form::date('dob', $family->dob, [
                                        'class' => 'form-control',
                                       
                                        'placeholder' => __('essentials::lang.dob'),
                                    ]) !!}
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
                                'other' => __('essentials::lang.other'),
                            ],
                            $family->relative_relation,
                            ['class' => 'form-control', 'required'],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('eqama_number', __('essentials::lang.eqama_number') . ':*') !!}
                        {!! Form::text('eqama_number', $family->eqama_number, [
                            'class' => 'form-control',
                            'id' => 'eqama_number',
                            'placeholder' => __('essentials::lang.eqama_number'),
                        ]) !!}
                        <div id="idProofNumberError" style="color: red;"></div>
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('address', __('essentials::lang.address') . ':') !!}
                        {!! Form::text('address', $family->address, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.address'),
                        ]) !!}
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
              
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
@endsection
<script>
    $(document).ready(function() {
        $('#employee_select').select2();
    });

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
