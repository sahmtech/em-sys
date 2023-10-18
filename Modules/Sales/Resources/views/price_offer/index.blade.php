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
        @component('components.filters', ['title' => __('report.filters')])
       
        <div class="col-md-3">
            <div class="form-group">
                <label for="offer_type_filter">@lang('sales::lang.offer_type'):</label>
                <select class="form-control select2" name="offer_type_filter" required id="offer_type_filter" style="width: 100%;">
                    <option value="all">@lang('lang_v1.all')</option>
                    <option value="external">@lang('sales::lang.external')</option>
                    <option value="internal">@lang('sales::lang.internal')</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="status_filter">@lang('essentials::lang.status'):</label>
                <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                    <option value="all">@lang('lang_v1.all')</option>
                    <option value="approved">@lang('sales::lang.approved')</option>
                    <option value="transfered">@lang('sales::lang.transfered')</option>
                    <option value="refused">@lang('sales::lang.refused')</option>

                </select>
            </div>
        </div>

        
        
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create'], ['status' => 'quotation'])}}">
                <i class="fa fa-plus"></i> @lang('lang_v1.add_quotation')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sale_table">
                <thead>
                    <tr>
                        
                        <th>@lang('sales::lang.offer_number')</th>
                        <th>@lang('sales::lang.customer_name')</th>
                        <th>@lang('sales::lang.customer_number')</th>
                        <th>@lang('sales::lang.date')</th>
                        <th>@lang('sales::lang.value')</th>
                        <th>@lang('sales::lang.offer_type')</th>
                        <th>@lang('sales::lang.offer_status')</th>
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
            "url": '{{ route('price_offer') }}',
            "data": function ( d ) {
                

               
                d.offer_type = $('#offer_type_filter').val();
                
                d.status = $('#status_filter').val();

            }
        },
       
        columns: [
           
            { data: 'ref_no'},
            { data: 'name'},
            { data: 'mobile'}, 
            { data: 'transaction_date'},
            { data: 'final_total'},
            { data: 'offer_type'},
            { data: 'status'},
           
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#purchase_table'));
        }
    });
    
    $(document).on('change', '#offer_type_filter, #status_filter',  function() {
        sale_table.ajax.reload();
    });

    $(document).on('click', 'a.convert-to-proforma', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(confirm => {
            if (confirm) {
                var url = $(this).attr('href');
                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            sale_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});
</script>
	
@endsection