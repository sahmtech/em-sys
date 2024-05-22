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
                        {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                        {!! Form::select(
                            'doc_type',
                            [
                                'national_id' => __('essentials::lang.national_id'),
                                'passport' => __('essentials::lang.passport'),
                                'residence_permit' => __('essentials::lang.residence_permit'),
                                'drivers_license' => __('essentials::lang.drivers_license'),
                                'car_registration' => __('essentials::lang.car_registration'),
                                'international_certificate' => __('essentials::lang.international_certificate'),
                                'Iban' => __('essentials::lang.Iban'),
                            ],
                            null,
                            [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'placeholder' => __('essentials::lang.select_type'),
                                'required',
                            ],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':') !!}
                        {!! Form::text('doc_number', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.doc_number'),
                        
                            'style' => 'height:40px',
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':') !!}
                        {!! Form::date('issue_date', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.issue_date'),
                        
                            'style' => 'height:40px',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':') !!}
                        {!! Form::text('issue_place', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.issue_place'),
                        
                            'style' => 'height:40px',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                        {!! Form::select(
                            'status',
                            [
                                'valid' => __('essentials::lang.valid'),
                                'expired' => __('essentials::lang.expired'),
                            ],
                            null,
                            ['class' => 'form-control', 'style' => 'height:40px', 'required'],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                        {!! Form::date('expiration_date', null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
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
