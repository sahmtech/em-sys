<div class="modal-dialog" role="offerument">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-center" id="exampleModalLabel">
           @lang('sales::lang.offer_details')
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>@lang('sales::lang.offer_number'):</strong> {{$offer->ref_no ?? ''}}</p>
                <p><strong>@lang('sales::lang.customer_name'):</strong> {{$offer->name ?? ''}}</p>
                <p><strong>@lang('sales::lang.customer_number'):</strong> {{$offer->mobile ?? ''}}</p>
                <p><strong>@lang('sales::lang.date'):</strong> {{$offer->transaction_date ?? ''}}</p>
                
                <p><strong>@lang('sales::lang.total'):</strong> {{$offer->final_total ?? ''}}</p>
            </div>
        </div>
        </div> <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('essentials::lang.close')</button>
    

      </div>
      </div>
     
    </div>
</div>