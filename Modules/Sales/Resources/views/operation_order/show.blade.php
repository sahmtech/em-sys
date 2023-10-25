<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> @lang('sales::lang.order_operation_details') 
    </h4>
</div>
<div class="modal-body">

        <div class="row">
            <div class="col-xs-12">
                <p class="pull-right"><b>@lang('messages.date'):</b> </p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 col-sm-4">
            <b>{{ __('sales::lang.operation_order_number') }} :</b></b> {{ $operations->operation_order_no }}<br>
            <b>{{ __('sales::lang.customer_name') }} :</b></b> {{ $operations->contact_name }}<br>
            <b>{{ __('sales::lang.contact_email') }} :</b></b> {{ $operations->email }}<br>
            </div>

            <div class="col-sm-2 col-sm-4">
            <b>{{ __('sales::lang.operation_order_type') }} :</b></b> {{ $operations->operation_order_type }}<br>
            <b>{{ __('sales::lang.Status') }} :</b></b> {{ $operations->Status }}<br>
            <b>{{ __('sales::lang.Location') }} :</b></b> {{ $operations->Location }}<br>
            </div>

            <div class="col-md-2 col-md-4">
            <b>{{ __('sales::lang.Delivery') }} :</b></b> {{ $operations->Delivery }}<br>
            <b>{{ __('sales::lang.Interview') }} :</b></b> {{ $operations->Interview }}<br>
            <b>{{ __('sales::lang.Industry') }} :</b></b> {{ $operations->Industry }}<br>
            </div>
        </div>

        <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.products') }}:</h4>
      </div>

      <div class="col-sm-12 col-xs-12">
        <div class="table-responsive">
          @include('sale_pos.partials.sale_operation_details')
        </div>
      </div>
    </div>

</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');
    __currency_convert_recursively(element);
  });
  </script>