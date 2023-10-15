@extends('layouts.app')
@section('title', __('essentials::lang.employees_appointments'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.employees_appointments')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
      
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('job_title_filter', __('essentials::lang.job_title') . ':') !!}
                    {!! Form::text('job_title_filter', null, ['class' => 'form-control', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
           
            @if (!empty($business_locations))
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location_filter', __('essentials::lang.location') . ':') !!}
                    {!! Form::select('location_filter', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
        @endif
    
        @if (!empty($departments))
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('department_filter', __('essentials::lang.department') . ':') !!}
                {!! Form::select('department_filter', $departments, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endif
           
           
            

        @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addAppointmentModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="appointments_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.employee' )</th>
                                <th>@lang('essentials::lang.national_id_number' )</th>
                                <th>@lang('essentials::lang.department' )</th>
                                <th>@lang('essentials::lang.location')</th>
                                <th>@lang('essentials::lang.superior' )</th>
                                <th>@lang('essentials::lang.job_title' )</th>
                                <th>@lang('essentials::lang.employee_status' )</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeAppointment' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_Appointment')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('department', __('essentials::lang.department') . ':*') !!}
                                {!! Form::select('department',$departments, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_department'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('location', __('essentials::lang.department') . ':*') !!}
                                {!! Form::select('location',$business_locations, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_location'), 'required']) !!}
                            </div>
                          
                           

                            <div class="form-group col-md-6">
                                {!! Form::label('superior', __('essentials::lang.superior') . ':*') !!}
                                {!! Form::text('superior', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.superior'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('job_title', __('essentials::lang.job_title') . ':*') !!}
                                {!! Form::text('job_title', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.job_title'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('employee_status', __('essentials::lang.employee_status') . ':*') !!}
                                {!! Form::text('employee_status', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.employee_status'), 'required']) !!}
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
            var appointments_table;

            function reloadDataTable() {
                appointments_table.ajax.reload();
            }

            appointments_table  = $('#appointments_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {   
                    url: "{{ route('appointments') }}",
                    data: function(d) {
                        
                        if ($('#job_title_filter').length) {
                            d.job_title = $('#job_title_filter').val();
                        }
                        if ($('#location_filter').length) {
                            d.location = $('#location_filter').val();
                        }
                        if ($('#department_filter').length) {
                            d.department = $('#department_filter').val();
                        }
                       
                      
                    }
                },
                
                columns: [
                        { data: 'user' },
                        { data: 'id_proof_number' },
                        { data: 'department_id' },
                        { data: 'business_location_id' },
                        { data: 'superior' },
                        { data: 'job_title' },
                        { data: 'employee_status'},
                        { data: 'action' },
                    ],
             });

           
            $('#job_title_filter,#location_filter, #department_filter').on('change', function() {
                reloadDataTable();
            });
            $(document).on('click', 'button.delete_appointment_button', function () {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_appointment,
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
                                    appointments_table.ajax.reload();
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
@endsection

