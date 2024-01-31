<div class="modal fade" id="editdocModal" tabindex="-1" role="dialog" aria-labelledby="editdocModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open([
                'route' => 'updateDoc',
                'method' => 'post',
                'id' => 'edit_doc_form',
                'enctype' => 'multipart/form-data',
            ]) !!}


            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.edit_doc')</h4>
            </div>

            <div class="modal-body">

                <div class="row">
                    {!! Form::hidden('docId', null, ['id' => 'docId']) !!}
                    <div class="form-group col-md-6">
                        {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                        {!! Form::select(
                            'status',
                            [
                                'valid' => __('essentials::lang.valid'),
                                'expired' => __('essentials::lang.expired'),
                            ],
                            null,
                            ['class' => 'form-control', 'placeholder' => 'Select status'],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                        {!! Form::date('expiration_date', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.expiration_date'),
                        ]) !!}
                    </div>


                </div>
            </div>


            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
