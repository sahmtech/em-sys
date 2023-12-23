@extends('layouts.app')
@section('title', __( 'lang_v1.quotation'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.list_quotations')
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
@include('sales::layouts.nav_offer_prices')

        {{-- @component('components.filters', ['title' => __('report.filters')])
       
    
        <div class="col-md-3">
            <div class="form-group">
                <label for="status_filter">@lang('essentials::lang.status'):</label>
                <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                    <option value="all">@lang('lang_v1.all')</option>
                    <option value="approved">@lang('sales::lang.approved')</option>
                    <option value="transfared">@lang('sales::lang.transfared')</option>
                    <option value="cancelled">@lang('sales::lang.cancelled')</option>
                    <option value="under_study">@lang('sales::lang.under_study')</option>


                </select>
            </div>
        </div>

      

    @endcomponent --}}
    @component('components.widget', ['class' => 'box-primary'])
     
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sale_table">
                <thead>
                    <tr>
                        
                       
                        <th>@lang('sales::lang.offer_number')</th>
                        <th>@lang('sales::lang.customer_name')</th>
                        <th>@lang('sales::lang.customer_number')</th>
                        <th>@lang('sales::lang.date')</th>
                        <th>@lang('sales::lang.value')</th>
                     
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
 
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
   
    
  
    
    sale_table = $('#sale_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: {
            "url": '{{ route('accepted_offer_prices') }}',
            "data": function ( d ) {
                

                d.status = $('#status_filter').val();

            }
        },
      
        columns: [
        
            { data: 'ref_no'},
            { data: 'supplier_business_name'},
            { data: 'mobile'}, 
            { data: 'transaction_date'},
            { data: 'final_total'},
          
         
            {data: 'action'}

            

           
        ],
     
    });
    
   

  

});
</script>
	
@endsection