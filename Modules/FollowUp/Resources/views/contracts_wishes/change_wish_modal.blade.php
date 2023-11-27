<div class="modal fade"  id="change_status_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">

      {!! Form::open(['url' => action([\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish']), 'method' => 'post', 'id' => 'change_status_form' ]) !!}
      <input type="hidden" name="employee_id" id="employee_id" value="">

	    <div class="modal-header">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title">@lang( 'essentials::lang.change_status' )</h4>
	    </div>

        <div class="modal-body">
        <div class="form-group">
            <label for="modal-wish">@lang('followup::lang.wish')</label>
            <select class="form-control" id="modal-wish" name="wish" style="height:40px">
                @foreach($wishes as $wishId => $wishReason)
                    <option value="{{ $wishId }}">{{ $wishReason }}</option>
                @endforeach
            </select>
        </div>
        
        </div>


        <div class="modal-footer">
            <button type="submit" class="btn btn-primary ladda-button update-offer-status" data-style="expand-right">
                <span class="ladda-label">@lang('messages.update')</span>
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

	
        {!! Form::close() !!}
	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>