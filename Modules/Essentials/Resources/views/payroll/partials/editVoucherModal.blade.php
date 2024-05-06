<div class="modal fade" id="editVoucherModal" tabindex="-1" role="dialog" aria-labelledby="editVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVoucherModalLabel">@lang('essentials::lang.change_salary_voucher')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editVoucherForm">
                    <div class="form-group">
                         <input type="hidden" name="userId" id="userId"> 
                        <label for="voucherStatus">@lang('essentials::lang.salary_voucher_status')</label>
                        <select class="form-control" id="voucherStatus">
                            
                            <option value="1">@lang('essentials::lang.yes')</option>
                            <option value="0">@lang('essentials::lang.no')</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
              
                <button type="button" class="btn btn-primary" id="saveVoucherStatus">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>
