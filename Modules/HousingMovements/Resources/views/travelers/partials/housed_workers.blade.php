@extends('layouts.app')
@section('title', __('housingmovements::lang.housed'))
@section('content')
@include('housingmovements::layouts.nav_trevelers')

<section class="content-header">
    <h1>
        <span>@lang('housingmovements::lang.housed')</span>
    </h1>
</section>



<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('project_name_filter', __('followup::lang.project_name') . ':') !!}
                    {!! Form::select('project_name_filter', $salesProjects, null, [
                        'class' => 'form-control select2',
                        'id'=>'project_name_filter',
                        'style' => 'width:100%;padding:2px;',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>

            <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doc_filter_date_range', __('housingmovements::lang.arrival_date') . ':') !!}
                            {!! Form::text('doc_filter_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control ',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
              
              
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary'])

    @php 
    $colspan = 5;
   
@endphp
<div class="col-md-8 selectedDiv" style="display:none;">
</div>
<table class="table table-bordered table-striped ajax_view hide-footer" id="product_table">
    <thead>
        <tr>
             <th>
                <input type="checkbox" class="largerCheckbox" id="chkAll" />
              </th>
          
                    <th>@lang('housingmovements::lang.worker_name')</th>  
                    <th>@lang('housingmovements::lang.project')</th> 
                    <th>@lang('housingmovements::lang.location')</th> 
                    <th>@lang('housingmovements::lang.arrival_date')</th> 
                    <th>@lang('housingmovements::lang.passport_number')</th>          
                    <th>@lang('housingmovements::lang.profession')</th>
                    <th>@lang('housingmovements::lang.nationality')</th>
                    <th>@lang('messages.action')</th>
           
        </tr>
    </thead>
    

    
    <tfoot>
        <tr>
        <td colspan="5">
            <div style="display: flex; width: 100%;">
    
                
                    &nbsp;
                            {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'bulkEdit']), 'method' => 'post', 'id' => 'bulk_edit_form' ]) !!}
                            {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); !!}
                            <button type="submit" class="btn btn-xs btn-warning" id="edit-selected"> <i class="fa fa-home"></i>{{__('housingmovements::lang.housed')}}</button>
                            {!! Form::close() !!}
                
              
               
                </div>
            </td>
        </tr>
    </tfoot>
</table>

            @include('housingmovements::travelers.partials.housing_modal')
       
    @endcomponent



</section>
<!-- /.content -->

@endsection