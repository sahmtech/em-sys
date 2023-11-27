<div class="modal-dialog" role="document">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title text-center" id="exampleModalLabel">
                @lang('sales::lang.order_operation_details')
            </h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                  
                  
               
                    <div>
                        <b>{{ __('sales::lang.operation_order_number') }} :</b></b> {{ $operations->operation_order_no }}<br>
                        <b>{{ __('sales::lang.customer_name') }} :</b></b> {{ $operations->contact->supplier_business_name }}<br>
                        <b>{{ __('sales::lang.contact_email') }} :</b></b> {{ $operations->contact->email }}<br>
                       

                    
                        <b>{{ __('sales::lang.operation_order_type') }} :</b></b> {{ __('sales::lang.' . $operations->operation_order_type ) }}<br>
                        <b>{{ __('sales::lang.Status') }} :</b> {{ __('sales::lang.' . $operations->Status) }}<br>
                        <b>{{ __('sales::lang.Location') }} :</b></b> {{ $operations->Location }}<br>
                       

                      
                        <b>{{ __('sales::lang.Delivery') }} :</b></b> {{ $operations->Delivery }}<br>
                        <b>{{ __('sales::lang.Interview') }} :</b></b>  {{ __('sales::lang.' . $operations->Interview ) }}<br>
                        <b>{{ __('sales::lang.Industry') }} :</b></b> {{ $operations->Industry }}<br>
                    </div>
                
                </div>
            </div>
        </div>


        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">@lang('essentials::lang.close')</button>
        
        </div>
    </div>
</div>
