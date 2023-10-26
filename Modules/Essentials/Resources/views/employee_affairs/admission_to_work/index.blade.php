@extends('layouts.app')
@section('title', __('essentials::lang.admissions_to_work'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.admissions_to_work')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
      
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('doc_filter_date_range', __('essentials::lang.admissions_date') . ':') !!}
                        {!! Form::text('doc_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                    </div>
                </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    <label for="admissions_status_filter">@lang('essentials::lang.admissions_status'):</label>
                    <select class="form-control select2" name="admissions_status_filter" required id="admissions_status_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="on_date">@lang('essentials::lang.on_date')</option>
                        <option value="delay">@lang('essentials::lang.delay')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="admissions_type_filter">@lang('essentials::lang.admissions_type'):</label>
                    <select class="form-control select2" name="admissions_type_filter" required id="admissions_type_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="first_time">@lang('essentials::lang.first_time')</option>
                        <option value="after_vac">@lang('essentials::lang.after_vac')</option>
                    </select>
                </div>
            </div>

        @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
           
                @slot('tool')
                <div class="box-tools">
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addAdmissionsToWorkModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="admissions_to_work_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.employee' )</th>
                                <th>@lang('essentials::lang.national_id_number' )</th>
                              
                                <th>@lang('essentials::lang.admissions_date')</th>
                                <th>@lang('essentials::lang.admissions_type' )</th>
                                <th>@lang('essentials::lang.admissions_status' )</th>
                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addAdmissionsToWorkModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeAdmissionToWork' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_AdmissionToWork')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                         
                    
                          
                            <div class="form-group col-md-6">
                                {!! Form::label('admissions_type', __('essentials::lang.admissions_type') . ':*') !!}
                                {!! Form::select('admissions_type', [
                                'first_time' => __('essentials::lang.first_time'),
                                'after_vac' => __('essentials::lang.after_vac'),
                          
                                ], null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_type'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('admissions_status', __('essentials::lang.admissions_status') . ':*') !!}
                                {!! Form::select('admissions_status', [
                                'on_date' => __('essentials::lang.on_date'),
                                'delay' => __('essentials::lang.delay'),
                          
                                ], null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_status'), 'required']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('admissions_date', __('essentials::lang.admissions_date') . ':*') !!}
                                {!! Form::date('admissions_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.admissions_date'), 'required']) !!}
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
            var admissions_to_work_table;

            function reloadDataTable() {
                admissions_to_work_table.ajax.reload();
            }

            admissions_to_work_table  = $('#admissions_to_work_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admissionToWork') }}",
                    data: function(d) {
                        
                        if ($('#admissions_type_filter').length) {
                            d.admissions_type = $('#admissions_type_filter').val();
                        }
                        if ($('#admissions_status_filter').length) {
                            d.admissions_status = $('#admissions_status_filter').val();
                        }
                        if ($('#doc_filter_date_range').val()) {
                            var start = $('#doc_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#doc_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                      
                    }
                },
                
                columns: [
                        { data: 'user' },
                        { data: 'id_proof_number' },
                        { data: 'admissions_date' },
                 
                       
                     
                        {
                            data: 'admissions_type',
                            render: function (data, type, row) {
                                if (data === 'first_time') {
                                    return  '@lang('essentials::lang.first_time')';
                                } else {
                                    return  '@lang('essentials::lang.after_vac')';
                                }
                            }
                        },
                        
                        {
                            data: 'admissions_status',
                            render: function (data, type, row) {
                                if (data === 'on_date') {
                                    return  '@lang('essentials::lang.on_date')';
                                } else {
                                    return  '@lang('essentials::lang.delay')';
                                }
                            }
                        },
                        { data: 'action' },
                    ],
             });

             $('#doc_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#doc_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#doc_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#doc_filter_date_range').val('');
                reloadDataTable();
            });
            
          
            $('#admissions_type_filter,#admissions_status_filter, #doc_filter_date_range').on('change', function() {
                reloadDataTable();
            });
            $(document).on('click', 'button.delete_admissionToWork_button', function () {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_admissionToWork,
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
                                    admissions_to_work_table.ajax.reload();
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

