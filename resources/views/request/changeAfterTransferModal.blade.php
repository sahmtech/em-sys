<div class="modal fade" id="changeAfterTransferModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            {!! Form::open([
                'url' => route('changeStatusAfterTransfer'),
                'method' => 'post',
                'id' => 'change_after_transfer_form',
                'enctype' => 'multipart/form-data',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('request.change_status')</h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <input type="hidden" name="request_id" id="request_id">
                    <label for="status">@lang('sale.status'):*</label>
                    <select class="form-control select2" name="status" required id="status_after_transfer_dropdown"
                        style="width: 100%;">
                        @foreach ($statuses as $key => $value)
                            <option value="{{ $key }}">@lang($value['name'])</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="form-group col-md-6">
                            {!! Form::label('note', __('followup::lang.note') . ':') !!}
                            {!! Form::textarea('note', null, [
                                'class' => 'form-control',
                                'placeholder' => __('followup::lang.note'),
                                'rows' => 3,
                            ]) !!}
                        </div> --}}

                {{-- <div class="form-group col-md-6">
                            {!! Form::label('attachment', __('request.attachment') . ':') !!}
                            {!! Form::file('attachment', [
                                'class' => 'form-control',
                                'placeholder' => __('request.attachment'),
                            ]) !!}
                        </div> --}}

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary ladda-button" data-style="expand-right">
                    <span class="ladda-label">@lang('messages.update')</span>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
