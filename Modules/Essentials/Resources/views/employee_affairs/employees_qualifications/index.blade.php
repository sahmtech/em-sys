@extends('layouts.app')
@section('title', __('essentials::lang.qualifications'))

@section('content')
@include('essentials::layouts.nav_employee_affairs')
<section class="content-header">
    <h1>@lang('essentials::lang.qualifications')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
      
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('qualification_type_filter', __('essentials::lang.qualification_type') . ':') !!}
                    {!! Form::select('qualification_type_filter', [
                       'bachelors'=>__('essentials::lang.bachelors'),
                        'master' =>__('essentials::lang.master'),
                        'PhD' =>__('essentials::lang.PhD'),
                        
                        'diploma' =>__('essentials::lang.diploma'),
                
                    ], null, ['class' => 'form-control','id'=>'qualification_type_filter',
                     'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
        
        
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('major_filter', __('essentials::lang.major') . ':') !!}
                    {!! Form::select('major_filter',$spacializations, null, ['class' => 'form-control','id'=>'major_filter',
                         'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            
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
                    
                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addQualificationModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endslot
            
            
            <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="qualifications_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.employee' )</th>
                                <th>@lang('essentials::lang.qualification_type' )</th>
                                <th>@lang('essentials::lang.major')</th>
                                <th>@lang('essentials::lang.graduation_year' )</th>
                                <th>@lang('essentials::lang.graduation_institution' )</th>
                                <th>@lang('essentials::lang.graduation_country' )</th>
                                <th>@lang('essentials::lang.degree' )</th>

                                <th>@lang('messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
        <div class="modal fade" id="addQualificationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeQualification' , 'enctype' => 'multipart/form-data']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('essentials::lang.add_qualification')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}
                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                                {!! Form::select('qualification_type', [
                                    'bachelors'=>__('essentials::lang.bachelors'),
                                     'master' =>__('essentials::lang.master'),
                                     'PhD' =>__('essentials::lang.PhD'),
                                     'diploma' =>__('essentials::lang.diploma'),
                             
                                 ], null, ['class' => 'form-control', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                             </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('major', __('essentials::lang.major') . ':*') !!}
                                {!! Form::select('major',$spacializations, null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.major'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                                {!! Form::date('graduation_year', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_year'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                                {!! Form::text('graduation_institution', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_institution'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                                {!! Form::select('graduation_country',$countries, null, ['class' => 'form-control', 'placeholder' =>  __('essentials::lang.select_country'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                {!! Form::number('degree', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.degree'), 'required']) !!}
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
            function reloadDataTable() {
                qualifications_table.ajax.reload();
            }
            var qualifications_table;
            qualifications_table  = $('#qualifications_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('qualifications') }}",
                    data: function(d) {
                        if ($('#qualification_type_filter').length) {
                            d.qualification_type = $('#qualification_type_filter').val();
                        }
                        if ($('#major_filter').length) {
                            d.major = $('#major_filter').val();
                        }
                    }
                },
                
                columns: [
                        { data: 'user' },
                       
                        {
                            data: 'qualification_type',
                            render: function (data, type, row) {
                                if (data === 'bachelors') {
                                    return  '@lang('essentials::lang.bachelors')';
                                } else if (data === 'master') {
                                    return  '@lang('essentials::lang.master')';
                                }else if (data === 'PhD') {
                                    return  '@lang('essentials::lang.PhD')';
                                }else if (data === 'diploma') {
                                    return  '@lang('essentials::lang.diploma')';
                                }else{
                                    return  ' ';
                                }
                            }
                        },
                        { data: 'major'},
                        { data: 'graduation_year' },
                        { data: 'graduation_institution' },
                        { data: 'graduation_country' },
                        { data: 'degree' },
                        { data: 'action' },
                    ],
             });

             
            $(document).on('change', '#qualification_type_filter, #major_filter', function() {
                console.log( $('#qualification_type_filter').val());
                console.log( $('#major_filter').val());
                reloadDataTable();
            });


            $(document).on('click', 'button.delete_qualification_button', function () {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_qualification,
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
                                    qualifications_table.ajax.reload();
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

