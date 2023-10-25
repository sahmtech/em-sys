@extends('layouts.app')
@section('title', __('sales::lang.sales'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('sales::lang.all_sales_operations')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])

<div class="col-md-3">
    <div class="form-group">
        <label for="offer_type_filter">@lang('sales::lang.contract'):</label>
        {!! Form::select('contract_id', $contracts->pluck('contract_number', 'contract_number'), null, [
            'class' => 'form-control',
            'style' => 'height:36px',
            'placeholder' => __('lang_v1.all'),
            'required',
            'id' => 'contract-select'
        ]) !!}
    </div>
</div>
       <div class="col-md-3">
           <div class="form-group">
               <label for="status_filter">@lang('sales::lang.operation_order_type'):</label>
               <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                   <option value="all">@lang('lang_v1.all')</option>
                   <option value="external">@lang('sales::lang.external')</option>
                   <option value="internal">@lang('sales::lang.internal')</option>

               </select>
           </div>
       </div>

     

@endcomponent

@component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('sales::lang.create_sale_operation')</a>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="operation_table">
                <thead>
                    <tr>
                   
                        <th>@lang('sales::lang.operation_order_number')</th>
                        <th>@lang('sales::lang.customer_name')</th>
                        <th>@lang('sales::lang.contract_number')</th>
                        <th>@lang('sales::lang.operation_order_type')</th>
                        <th>@lang('sales::lang.Status')</th>
                        <th>@lang('sales::lang.show_operation')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent



</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
    var customers_table = $('#operation_table').DataTable({
        ajax:'', 
        processing: true,
        serverSide: true,
        
        
       
        columns: [
          
         
            { data: 'operation_order_no', name: 'operation_order_no' },
            { data: 'contact_name', name: 'contact_name' },
            { data: 'contract_number', name: 'contract_number' },
            { data: 'operation_order_type', name: 'operation_order_type' },
            { data: 'Status', name: 'Status' },
            {data: 'show_operation' ,name:'show_operation'},
            { data: 'action', name: 'action', orderable: false, searchable: false },
           
        ]
    });
     // Add an event listener to trigger filtering when your filters change
 
     $('#contract-select, #status_filter').change(function () {
        customers_table.ajax.reload();
    });
});

</script>





@endsection