@extends('layouts.app')
@section('title', __( 'internationalrelations::lang.importWorkers' ))

@section('content')

<section class="content-header">
    <h1>@lang('internationalrelations::lang.importWorkers')
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
                {!! Form::open(['url' => action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'postImportWorkers']),
                     'method' => 'post',
                      'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                        <div class="col-sm-8">
                            <div class="form-group">
                                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                {!! Form::file('workers_csv', ['accept'=> '.xls', 'required' => 'required']); !!}
                              </div>
                        </div>
                        <input type="hidden" name="delegation_id" value="{{ $delegation_id }}">
                <input type="hidden" name="agency_id" value="{{ $agency_id }}">
                <input type="hidden" name="transaction_sell_line_id" value="{{ $transaction_sell_line_id }}">

                        <div class="col-sm-4">
                        <br>
                            <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                        </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-sm-4">
                        <a href="{{ asset('files/import_workers_template.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                    </div>
                </div>

                {!! Form::close() !!}
                
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
                        <td>@lang('essentials::lang.employee_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>@lang('essentials::lang.employee_name_example') </td>
                    </tr>
                   
                    <tr>
                        <td>2</td>
                        <td>@lang('essentials::lang.mid_name') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('essentials::lang.last_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>@lang('essentials::lang.age') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                  <tr>
                        <td>5</td>
                        <td>@lang('essentials::lang.Gender') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>@lang('essentials::lang.email') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('essentials::lang.email_example') </td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>@lang('essentials::lang.Birth_date') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('lang_v1.dob_ins') ({{\Carbon::now()->format('Y-m-d')}})</td>
                    </tr>
                   

                    <tr>
                        <td>8</td>
                        <td>@lang('essentials::lang.marital_status') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>{{ trans('essentials::lang.single').':single' }},{{ trans('essentials::lang.married').':married' }}</td>
                    </tr>
                 
                    <tr>
                        <td>9</td>
                        <td>@lang('essentials::lang.blood_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>@lang('essentials::lang.Mobile_number') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                   
            
                 <tr>
                        <td>11</td>
                        <td>@lang('essentials::lang.current_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>12</td>
                        <td>@lang('essentials::lang.permanent_address') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>13</td>
                        <td>@lang('essentials::lang.passport_number') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td>&nbsp;</td>
                    </tr>
                  
                   
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection