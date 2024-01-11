@extends('layouts.app')
@section('title', __('essentials::lang.employees_appointments'))

@section('content')
    {{-- @include('essentials::layouts.nav_employee_affairs') --}}
    <section class="content-header">
        <h1>@lang('essentials::lang.employees_appointments')</h1>
    </section>

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open([
                'route' => ['updateAppointment', $Appointmet->id],
                'method' => 'put',
                'id' => 'edit_Appointmet_form',
            ]) !!}


            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.edit_Appointme')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                        {!! Form::select('employee', $users, $Appointmet->employee_id, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.select_employee'),
                            'required',
                            'id'=>'employee_select',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('department', __('essentials::lang.department') . ':*') !!}
                        {!! Form::select('department', $departments, $Appointmet->department_id, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.select_department'),
                            'required',
                            'id'=>'department_select',

                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('location', __('essentials::lang.location') . ':*') !!}
                        {!! Form::select('location', $business_locations, $Appointmet->business_location_id, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.select_location'),
                            'required',
                            'id'=>'location_select',

                        ]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                        {!! Form::select('profession', $professions, $Appointmet->profession_id, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('sales::lang.profession'),
                            'id' => 'professionSelect',
                        ]) !!}

                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                        {!! Form::select('specialization', $specializations, $Appointmet->specialization_id, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('sales::lang.specialization'),
                            'id' => 'specializationSelect',
                        ]) !!}
                    </div>



                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

          $('#employee_select').select2();
          $('#department_select').select2();
          $('#location_select').select2();
          $('#professionSelect').select2();
         
            var professionSelect = $('#professionSelect');
            var specializationSelect = $('#specializationSelect');

            professionSelect.on('change', function() {
                var selectedProfession = $(this).val();
                console.log(selectedProfession);
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '{{ route('specializations') }}',
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        profession_id: selectedProfession
                    },
                    success: function(data) {
                        specializationSelect.empty();
                        $.each(data, function(id, name) {
                            specializationSelect.append($('<option>', {
                                value: id,
                                text: name
                            }));
                        });
                    }
                });
            });









        });
    </script>

@endsection
