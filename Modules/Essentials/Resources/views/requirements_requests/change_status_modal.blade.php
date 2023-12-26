<div class="modal fade" id="change_status_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">

	    {!! Form::open(['url' => action( [\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'changeStatus']), 'method' => 'post', 'id' => 'change_status_form' ]) !!}

	    <div class="modal-header">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title">@lang( 'essentials::lang.change_status' )</h4>
	    </div>

	    <div class="modal-body">
	      	<div class="form-group">
	      		<input type="hidden" name="request_id" id="request_id">
                  <input type="hidden" name="quantity_value" id="quantity_value">
	      		<label for="status">@lang( 'sale.status' ):*</label>
                  <select class="form-control select2" name="status" required id="status_dropdown" style="width: 100%;">
                    @foreach($statuses as $key => $value)
                        <option value="{{$key}}">{{trans('essentials::lang.'.$key)}}</option>
                    @endforeach
                </select>
	      	</div>

              <div class="form-group">
                <label for="number">@lang('essentials::lang.quantity'): *</label>
                <input type="number" class="form-control" name="quantity" id="quantity" required>
                <span id="quantity-error" class="text-danger"></span>
              </div>
	      
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="btn btn-primary ladda-button update-offer-status" data-style="expand-right">
	      	<span class="ladda-label">@lang( 'messages.update' )</span>
	      </button>
	      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>

	    {!! Form::close() !!}

	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>