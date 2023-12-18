@extends('layouts.app')
@section('title', __('essentials::lang.departments'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('essentials::lang.departments')</span>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

            @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addDepartmentModal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
            @endslot
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="departments_table">
                    <thead>
                        <tr>
                            <th>@lang('essentials::lang.id')</th> 
                            <th>@lang('essentials::lang.name')</th>                      
                            <th>@lang('essentials::lang.is_main_dep')</th>
                            <th>@lang('essentials::lang.parent_department_id')</th>  
                            {{-- <th>@lang('essentials::lang.creation_date')</th> --}}
                            <th>@lang('essentials::lang.manager_name')</th>
                            <th>@lang('essentials::lang.delegate_name')</th>
                       
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('storeManager', ':id') }}" method="POST" id="appointmentForm">
                            @csrf
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.add_Appointment')</h4>
                            </div>
            
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                        {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                                    </div>
                    
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession',$professions,null, ['class' => 'form-control profession-select', 'required',
                                           'placeholder' => __('sales::lang.profession')]); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization',$specializations ,null, ['class' => 'form-control specialization-select', 'required',
                                            'placeholder' => __('sales::lang.specialization')]); !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                                        {!! Form::date('start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.start_date'), 'required']) !!}
                                    </div>
                                   
                                   
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="saveAppointmentBtn">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                        </form>
                        <div id="modalContent"></div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="addDelegatingModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('manager_delegating', ':id') }}" method="POST" id="delegatingForm">
                            @csrf
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.manager_delegating')</h4>
                            </div>
            
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                        {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                                    </div>
                               
                                
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession',$professions,null, ['class' => 'form-control profession-select', 'required',
                                           'placeholder' => __('sales::lang.profession')]); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization',$specializations ,null, ['class' => 'form-control specialization-select', 'required',
                                            'placeholder' => __('sales::lang.specialization')]); !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                                        {!! Form::date('start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.start_date'), 'required']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('end_date', __('essentials::lang.end_date') . ':*') !!}
                                        {!! Form::date('end_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.end_date'), 'required']) !!}
                                    </div>
                                   
                                   
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="saveDelegatingBtn">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                        </form>
                        <div id="modalContent"></div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="editDepartment" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('updateDepartment', ':id') }}" method="PUT" id="editForm">
                            @csrf
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">@lang('essentials::lang.edit_department')</h4>
                            </div>
            
                            <div class="modal-body">
                          
                                <div class="col-md-12">
                                    <h4>@lang('essentials::lang.department_Details'):</h4>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                                        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required',  'id' => 'department_name']) !!}
                                    </div>
                
                                    <div class="form-group col-md-6">
                                        {!! Form::label('level', __('essentials::lang.level') . ':*') !!}
                                        
                                        <div class="form-check">
                                            {!! Form::radio('level', 'first_level', true, ['class' => 'form-check-input','id'=>'first_level']) !!}
                                            {!! Form::label('first_level', __('essentials::lang.first_level'), ['class' => 'form-check-label']) !!}
                                        </div>
                                        
                                        <div class="form-check">
                                            {!! Form::radio('level', 'other', false, ['class' => 'form-check-input','id'=>'other']) !!}
                                            {!! Form::label('other_level', __('essentials::lang.other_level'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                   
                                    <div class="form-group col-md-6 parentLevelContainer" style="display: none;">
                                        {!! Form::label('parent_level', __('essentials::lang.parent_level') . ':*') !!}
                                        {!! Form::select('parent_level', $parent_departments, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.parent_level'), 'required', 'id' => 'department_parent_department_id', 'size' => 1]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('is_main', __('essentials::lang.is_main_dep') . ':*') !!}
                                        
                                        <div class="form-check">
                                            {!! Form::radio('is_main', '1', true, ['class' => 'form-check-input','id' => 'is_main']) !!}
                                            {!! Form::label('is_main_dep', __('essentials::lang.is_main_dep'), ['class' => 'form-check-label']) !!}
                                        </div>
                                        
                                        <div class="form-check">
                                            {!! Form::radio('is_main', '0', false, ['class' => 'form-check-input','id' => 'is_not_main']) !!}
                                            {!! Form::label('is_not_main_dep', __('essentials::lang.is_not_main_dep'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    
                               
                                    <div class="form-group col-md-6">
                                        {!! Form::label('address', __('essentials::lang.address') . ':') !!}
                                        {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address'),'id'=>'department_address']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                                        
                                        <div class="form-check">
                                            {!! Form::radio('is_active', '1', true, ['class' => 'form-check-input','id'=>'is_active']) !!}
                                            {!! Form::label('is_active', __('essentials::lang.is_active'), ['class' => 'form-check-label']) !!}
                                        </div>
                                        
                                        <div class="form-check">
                                            {!! Form::radio('is_active', '0', false, ['class' => 'form-check-input','id'=>'is_not_active']) !!}
                                            {!! Form::label('is_unactive', __('essentials::lang.is_unactive'), ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <hr>
                                    <h4>@lang('essentials::lang.manager_Details'):</h4>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('manager', __('essentials::lang.manager') . ':*') !!}
                                        {!! Form::select('manager',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required','id' => 'manager_id']) !!}
                                    </div>
                                
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession',$professions,null, ['class' => 'form-control manager_profession-select', 'required',
                                           'placeholder' => __('sales::lang.profession'),'id' => 'profession_id']); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization',$specializations ,null, ['class' => 'form-control manager_specialization-select', 'required',
                                            'placeholder' => __('sales::lang.specialization'),'id' => 'specialization_id']); !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                                        {!! Form::date('start_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.start_date'), 'required','id'=>'start_from']) !!}
                                    </div>
                                   
                                   
                                </div>
                                <div class="col-md-12">
                                    <hr>
                                    <h4>@lang('essentials::lang.delegate_Details'):</h4>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('delegate', __('essentials::lang.delegate') . ':*') !!}
                                        {!! Form::select('delegate',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required','id'=>'delegate']) !!}
                                    </div>
                               
                                
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession2', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession2',$professions,null, ['class' => 'form-control profession-select', 'required',
                                           'placeholder' => __('sales::lang.profession'),'id' => 'delegate_profession_id']); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization2', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization2',$specializations ,null, ['class' => 'form-control specialization-select', 'required',
                                            'placeholder' => __('sales::lang.specialization'),'id' => 'delegate_specialization_id']); !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('start_date2', __('essentials::lang.start_date') . ':*') !!}
                                        {!! Form::date('start_date2', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.start_date'), 'required','id'=>'delegate_start_from']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('end_date2', __('essentials::lang.end_date') . ':*') !!}
                                        {!! Form::date('end_date2', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.end_date'), 'required','id'=>'delegate_end_at']) !!}
                                    </div>
                                   
                                   
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="saveEditBtn">@lang('messages.save')</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                            </div>
                        </form>
                        <div id="modalContent"></div>
                    </div>
                </div>
            </div>
    @endcomponent

    <div class="modal fade DepartmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => 'storeDepartment']) !!}
        
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('essentials::lang.add_department')</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('name', __('essentials::lang.name') . ':*') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.name'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-6">
                            {!! Form::label('level', __('essentials::lang.level') . ':*') !!}
                            
                            <div class="form-check">
                                {!! Form::radio('level', 'first_level', true, ['class' => 'form-check-input', 'id' => 'first_level']) !!}
                                {!! Form::label('first_level', __('essentials::lang.first_level'), ['class' => 'form-check-label']) !!}
                            </div>
                            
                            <div class="form-check">
                                {!! Form::radio('level', 'other', false, ['class' => 'form-check-input', 'id' => 'other_level']) !!}
                                {!! Form::label('other_level', __('essentials::lang.other_level'), ['class' => 'form-check-label']) !!}
                            </div>
                        </div>
                    
                        <div class="form-group col-md-6 parentLevelContainer" style="display: none;">
                          
                            {!! Form::label('parent_level', __('essentials::lang.parent_level') . ':*') !!}
                            {!! Form::select('parent_level', $parent_departments, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.parent_level'), 'id' => 'department_parent_department_id', 'size' => 1]) !!}  
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('is_main', __('essentials::lang.is_main_dep') . ':*') !!}
                            
                            <div class="form-check">
                                {!! Form::radio('is_main', '1', true, ['class' => 'form-check-input']) !!}
                                {!! Form::label('is_main_dep', __('essentials::lang.is_main_dep'), ['class' => 'form-check-label']) !!}
                            </div>
                            
                            <div class="form-check">
                                {!! Form::radio('is_main', '0', false, ['class' => 'form-check-input']) !!}
                                {!! Form::label('is_not_main_dep', __('essentials::lang.is_not_main_dep'), ['class' => 'form-check-label']) !!}
                            </div>
                        </div>
                        
                
                        <div class="form-group col-md-10">
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


</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    
$(document).ready(function () {
    
    $('.profession-select').on('change', function () {
        var selectedProfession = $(this).val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
      
        var specializationSelect = $(this).closest('form').find('.specialization-select');

        $.ajax({
            url: '{{ route('specializations') }}',
            type: 'POST',
            data: {
                _token: csrfToken,
                profession_id: selectedProfession
            },
            success: function (data) {
                specializationSelect.empty();
                $.each(data, function (id, name) {
                    specializationSelect.append($('<option>', {
                        value: id,
                        text: name
                    }));
                });
            }
        });
    });
    $('.manager_profession-select').on('change', function () {
        var selectedProfession = $(this).val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
      
        var specializationSelect = $(this).closest('form').find('.manager_specialization-select');

        $.ajax({
            url: '{{ route('specializations') }}',
            type: 'POST',
            data: {
                _token: csrfToken,
                profession_id: selectedProfession
            },
            success: function (data) {
                specializationSelect.empty();
                $.each(data, function (id, name) {
                    specializationSelect.append($('<option>', {
                        value: id,
                        text: name
                    }));
                });
            }
        });
    });
    

    var departments_table = $('#departments_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("departments") }}', 
            columns: [
                { data: 'id'},
                { data: 'name'},
              
                { 
                    data: 'is_main',
                    render: function (data, type, row) {
                        if (data === 1) {
                           
                            return  '@lang('essentials::lang.main_dep')';
                        } else {
                           
                            return  '@lang('essentials::lang.subsidiary_dep')';
                        }
                    }
                },
                { data: 'parent_department_id'},
              
                { data: 'manager_name'},
                { data: 'delegatingManager_name'},
              
                 { data: 'action', name: 'action', orderable: false, searchable: false }
           
            
            ]
    });

    $(document).on('click', 'button.delete_department_button', function () {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_department,
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
                                departments_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
    });
    
    $('#departments_table').on('click', '.open-modal', function () {
           
            var rowId = $(this).data('row-id');
            $('#addAppointmentModal').data('row-id', rowId);
            var formAction = "{{ route('storeManager', ':id') }}".replace(':id', rowId);
            $('#appointmentForm').attr('action', formAction);
        });

     
        $('#saveAppointmentBtn').on('click', function () {

            var formData = $('#appointmentForm').serialize();
            $.ajax({
                url: $('#appointmentForm').attr('action'),
                type: 'POST',
                data: formData,
                success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                $('#addAppointmentModal').modal('hide');
                                departments_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                
            });
        
    });

    $('#departments_table').on('click', '.open-modal', function () {
           
           var rowId = $(this).data('row-id');
           $('#addDelegatingModal').data('row-id', rowId);
           var formAction = "{{ route('manager_delegating', ':id') }}".replace(':id', rowId);
           $('#delegatingForm').attr('action', formAction);
       });

    
       $('#saveDelegatingBtn').on('click', function () {

           var formData = $('#delegatingForm').serialize();
           $.ajax({
               url: $('#delegatingForm').attr('action'),
               type: 'POST',
               data: formData,
               success: function (result) {
                           if (result.success == true) {
                               toastr.success(result.msg);
                               $('#addDelegatingModal').modal('hide');
                               departments_table.ajax.reload();
                           } else {
                               toastr.error(result.msg);
                           }
                       }
               
           });
       
   });

   $('#departments_table').on('click', '.open-modal', function () {
           
           var rowId = $(this).data('row-id');
           var infoRoute = $(this).data('info-route');
           $.ajax({
            url: infoRoute,
            type: 'GET',
            success: function (data) {
                console.log('Data:', data);
                $('#department_name').val(data.name);
                $('#department_level').val(data.level);

                if (data.level === 'first_level') {
                    $('#first_level').prop('checked', true);
                    $('.parentLevelContainer').hide();
                } else if (data.level === 'other') {
                    $('#other').prop('checked', true);
                    $('.parentLevelContainer').show();
                }


                
                
                if (data.is_main === '1') {
                        $('#is_main').prop('checked', true);
                    } else if (data.is_main === '0') {
                        $('#is_not_main').prop('checked', true);
                    }
                $('#department_parent_department_id').val(data.parent_department_id);
                $('#department_address').val(data.address);
                if (data.is_active === '1') {
                        $('#is_active').prop('checked', true);
                    } else if (data.is_active === '0') {
                        $('#is_not_active').prop('checked', true);
                    }
              
                    $('#specialization_id').val(data.specialization_id);
                    $('#profession_id').val(data.profession_id);
                    $('#start_from').val(data.manager_start_from);
                    $('#manager_id').val(data.manager);
                
             
                    $('#delegate').val(data.delegate);
                    $('#delegate_profession_id').val(data.delegate_profession_id);
                    $('#delegate_specialization_id').val(data.delegate_specialization_id);
                    $('#delegate_start_from').val(data.delegate_start_from);
                    $('#delegate_end_at').val(data.delegate_end_at);
                
                    
            },
            error: function (error) {
                console.error('Error fetching department information:', error);
            }
        });
           $('#editDepartment').data('row-id', rowId);
           var formAction = "{{ route('updateDepartment', ':id') }}".replace(':id', rowId);
           $('#editForm').attr('action', formAction);
       });

    
       $('#saveEditBtn').on('click', function () {

           var formData = $('#editForm').serialize();
           $.ajax({
               url: $('#editForm').attr('action'),
               type: 'PUt',
               data: formData,
               success: function (result) {
                           if (result.success == true) {
                               toastr.success(result.msg);
                               $('#editDepartment').modal('hide');
                               departments_table.ajax.reload();
                           } else {
                               toastr.error(result.msg);
                           }
                       }
               
           });
       
   });
   $('input[name="level"]').change(function() {
            if (this.value === 'first_level') {
                $('.parentLevelContainer').hide();
            } else {
                $('.parentLevelContainer').show();
            }
    });

 
  
});

</script>
@endsection
