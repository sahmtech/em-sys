<!-- Modal for changing arrival date -->
<div class="modal fade" id="changeArrivalDateModal" tabindex="-1" role="dialog" aria-labelledby="changeArrivalDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeArrivalDateModalLabel">{{ __('internationalrelations::lang.change_arrival_date') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
             
                <form id="changeArrivalDateForm">
                 
                    <div class="form-group">
                        <label for="arrivalDate">{{ __('internationalrelations::lang.arrival_date') }}</label>
                        <input type="date" class="form-control" id="arrivalDate" name="arrival_date">
                    </div>
                  
                    <input type="hidden" id="workerId" name="worker_id">
                </form>
            </div>
            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"  id="saveArrivalDate">@lang('messages.update')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
