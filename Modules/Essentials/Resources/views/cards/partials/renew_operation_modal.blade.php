<div id="renewOperationModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1100px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">@lang('essentials::lang.renewal_residence')</h4>
            </div>

            {!! Form::open(['url' => route('postOperationRenewData'),'method' => 'post', 'id' => 'renew_operation_form' ]) !!}
                <div class="modal-body">
                    <div class="row">
                        
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
