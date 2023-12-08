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
               
                    {!! Form::open(['url' => action([\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getarrived']),
                         'method' => 'post', 'id' => 'arraived_form' ]) !!}
                    {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                    
                    {!! Form::submit(__('housingmovements::lang.arrived'),
                         array('class' => 'btn btn-xs btn-success', 'id' => 'arraived-selected')) !!}
                    {!! Form::close() !!}
              

                
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
