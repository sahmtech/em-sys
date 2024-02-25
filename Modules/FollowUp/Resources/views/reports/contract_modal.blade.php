<div class="modal fade"  id="contract_modal" tabindex="-1" role="dialog" aria-labelledby="contract_modal">
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title text-center" id="exampleModalLabel">
                @lang('followup::lang.contract_details')
            </h4>
           
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                  
                    <div>
                         <b>{{ __('sales::lang.contract_number') }} :</b>
                         <span id="contract_number"></span><br>

                         <b>{{ __('sales::lang.start_date') }} :</b>
                         <span id="start_date"></span><br>
                       

                         <b>{{ __('sales::lang.end_date') }} :</b>
                         <span id="end_date"></span><br>

                         <b>{{ __('followup::lang.contract_file') }} :</b>
                        <button id="contract_file_button" class="btn btn-primary" style="display: none;" onclick="viewContract()">
                            {{ __('followup::lang.view_contract') }}
                        </button>
                        <span id="no_contract_message" style="display: none;">{{ __('followup::lang.no_contract_available') }}</span>
                            
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
</div>
