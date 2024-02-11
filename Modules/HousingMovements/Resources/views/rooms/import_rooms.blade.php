@extends('layouts.app')
@section('title', __( 'housingmovements::lang.import_rooms' ))

@section('content')
<section class="content-header">
    <h1>@lang('housingmovements::lang.import_rooms')
    </h1>
</section>


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
            <div class="row" style="display:none;">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>@lang('housingmovements::lang.select_operation'):</label>
                        <div class="radio">
                            <label>
                                <input type="radio" name="operation" value="add" checked> @lang('housingmovements::lang.add_new_data')
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="operation" value="update"> @lang('housingmovements::lang.update_existing_data')
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="add-new-data">
                {!! Form::open(['url' => action( [\Modules\HousingMovements\Http\Controllers\ImportRoomsController::class, 'sendImportRooms']),
                 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                    {!! Form::file('rooms_csv', ['accept'=> '.xls']); !!}
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('housingmovements.submit_import_rooms'))
                            <div class="col-sm-4">
                                <br>
                                <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                            </div>
                            @endif

                            <div class="col-sm-6">
                                <a href="{{ asset('files/import_rooms_template.xls') }}" class="btn btn-success" download>
                                    <i class="fa fa-download"></i> @lang('lang_v1.download_template_file')</a>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>




            <div class="update-existing-data" style="display: none;">
            {!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeUpdateImportController::class, 'postImportupdateEmployee']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'essentials::lang.file_to_update__import' ) . ':') !!}
                                    {!! Form::file('update_employee_csv', ['accept'=> '.xls' ]); !!}
                                </div>
                            </div>
                            @if(auth()->user()->hasRole('Admin#1') || auth()->user()->can('essentials.import_update_employees'))
                            <div class="col-sm-4">
                                <br>
                                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                            </div>
                            @endif
                            <div class="col-sm-6">
                                <a href="{{ asset('files/import_update_employee_template.xls') }}" class="btn btn-success" download><i class="fa fa-download"></i> @lang('essentials::lang.download_update_template_file')</a>
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
                        <td>@lang('housingmovements::lang.room_number') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td> A102 </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>@lang('housingmovements::lang.htr_building_id') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td> 1  </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>@lang('housingmovements::lang.area') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td> 40  </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>@lang('housingmovements::lang.total_beds') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                        <td> 5  </td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>@lang('housingmovements::lang.contents') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                        <td>@lang('housingmovements::lang.contents_example')  </td>
                    </tr>
                   
                   
                   
                
                </table>
            @endcomponent
        </div>
    </div>
</section>


@endsection
@section('javascript')
<script>
    $(document).ready(function() {
        $('input[name="operation"]').change(function() {
            if (this.value === 'add') {
                $('.add-new-data').show();
                $('.update-existing-data').hide();
            } else if (this.value === 'update') {
                $('.add-new-data').hide();
                $('.update-existing-data').show();
            }
        });
    });
</script>
@endsection