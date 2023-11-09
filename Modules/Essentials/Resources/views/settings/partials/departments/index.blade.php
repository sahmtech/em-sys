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
                            <th>@lang('essentials::lang.name')</th>                      
                            <th>@lang('essentials::lang.is_main_dep')</th>
                            <th>@lang('essentials::lang.parent_department_id')</th>  
                            <th>@lang('essentials::lang.creation_date')</th>
                            <th>@lang('essentials::lang.manager_name')</th>
                            <th>@lang('essentials::lang.delegatingManager_name')</th>
                            <th>@lang('essentials::lang.details')</th>
                            <th>@lang('essentials::lang.is_active')</th>
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
                               
                                    <div class="form-group col-md-6">
                                        {!! Form::label('location', __('essentials::lang.location') . ':*') !!}
                                        {!! Form::select('location',$business_locations, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_location'), 'required']) !!}
                                    </div>
                                
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession',$professions,null, ['class' => 'form-control', 'required',
                                           'placeholder' => __('sales::lang.profession'),'id' => 'professionSelect']); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization',$specializations ,null, ['class' => 'form-control', 'required',
                                            'placeholder' => __('sales::lang.specialization'),'id' => 'specializationSelect']); !!}
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
                               
                                    <div class="form-group col-md-6">
                                        {!! Form::label('location', __('essentials::lang.location') . ':*') !!}
                                        {!! Form::select('location',$business_locations, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_location'), 'required']) !!}
                                    </div>
                                
                                    <div class="form-group  col-md-6">
                                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                                        {!! Form::select('profession',$professions,null, ['class' => 'form-control', 'required',
                                           'placeholder' => __('sales::lang.profession'),'id' => 'professionSelect']); !!}
                                           
                                      </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                                        {!! Form::select('specialization',$specializations ,null, ['class' => 'form-control', 'required',
                                            'placeholder' => __('sales::lang.specialization'),'id' => 'specializationSelect']); !!}
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
                        {!! Form::select('level', ['1' => __('essentials::lang.first_level'), '2' => __('essentials::lang.other_level')], null, ['class' => 'form-control']) !!}
                    </div>
                   
                    <div class="form-group col-md-6">
                        {!! Form::label('parent_level', __('essentials::lang.parent_level') . ':*') !!}
                        {!! Form::select('parent_level', $parent_departments, null, ['class' => 'form-control select2', 'placeholder' => __('essentials::lang.parent_level'), 'required']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('is_main', __('essentials::lang.is_main_dep') . ':*') !!}
                        {!! Form::select('is_main', ['1' => __('essentials::lang.is_main_dep'), '0' => __('essentials::lang.is_not_main_dep')], null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('creation_date', __('essentials::lang.creation_date') . ':') !!}
                        {!! Form::date('creation_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.creation_date')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('address', __('essentials::lang.address') . ':') !!}
                        {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.address')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('details', __('essentials::lang.details') . ':') !!}
                        {!! Form::textarea('details', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.contry_details'), 'rows' => 2]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('is_active', __('essentials::lang.is_active') . ':*') !!}
                        {!! Form::select('is_active', ['1' => __('essentials::lang.is_active'), '0' => __('essentials::lang.is_unactive')], null, ['class' => 'form-control']) !!}
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
    var departments_table = $('#departments_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("departments") }}', 
            columns: [
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
                { data: 'creation_date'},
                { data: 'manager_name'},
                { data: 'delegatingManager_name'},

                
                { data: 'details' },
                { 
                    data: 'is_active',
                    render: function (data, type, row) {
                        if (data === 1) {
        
                         
                            return  '@lang('essentials::lang.dep_is_active')';
                        } else {
                         
                            return  '@lang('essentials::lang.is_unactive')';
                        }
                    }
                },
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

    

});

</script>
@endsection
