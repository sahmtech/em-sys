@extends('layouts.app')
@section('title', __( 'essentials::lang.import_employees_insurance' ))

@section('content')

<!-- Content Header (Page header) -->


<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('essentials::lang.import_employees_insurance')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif


<div class="row">
    <div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary'])
           

            <div class="add-new-data">
                {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'insurancepostImportEmployee']),
                     'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                    {!! Form::file('employee_insurance_csv', ['accept'=> '.xls']); !!}
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <br>
                                <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                            </div>
                            <div class="col-sm-6">
                                <a href="{{ asset('files/employee_insurance_csv.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>

           

        @endcomponent
    </div>
</div>


    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                <strong>@lang('lang_v1.instruction_line1')</strong><br>
                    @lang('lang_v1.instruction_line2')
                    <br><br>
                <table class="table table-striped">
                    <tr>
                        <th>@lang('lang_v1.col_no')</th>
                        <th>@lang('lang_v1.col_name')</th>
                        <th>@lang('lang_v1.instruction')</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>@lang('essentials::lang.employee_id') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp; </td>
                    </tr>
                   
                    <tr>
                        <td>2</td>
                        <td>@lang('essentials::lang.insurance_class_id') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('essentials::lang.insurance_company_id') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                  
                 
                  
                   
                
                </table>
            @endcomponent
        </div>
    </div>
    
  
   
</section>
<!-- /.content -->

@endsection
@section('javascript')

@endsection