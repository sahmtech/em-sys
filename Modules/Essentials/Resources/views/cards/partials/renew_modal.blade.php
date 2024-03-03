<div id="renewModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1000px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">@lang('essentials::lang.renewal_residence')</h4>
            </div>

            {!! Form::open(['url' => route('postRenewData'), 'method' => 'post', 'id' => 'renew_form']) !!}
                <div class="modal-body">
                    {{-- <input name="building_htr" id="building_htr" type="hidden" value="300" /> --}}

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
