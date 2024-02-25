<div class="modal fade" id="attachments-modal" tabindex="-1" role="dialog" aria-labelledby="attachments-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title" id="attachments-modal-label">@lang('followup::lang.add_attachments')</h4>
              
            </div>
             <form id="file-upload-form" action="{{ route('upload_attachments') }}" method="POST" enctype="multipart/form-data">
                    @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- File upload form -->
               
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
                                    {!! Form::number('doc_number', null, [
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
                                    {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                                    {!! Form::date('expiration_date', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.expiration_date'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                                    {!! Form::file('file', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('essentials::lang.file'),
                                    
                                        'style' => 'height:40px',
                                    ]) !!}
                                </div>
                 
              
               
               
            </div>
            <div class="clearfix"></div>
             <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

  </form>
        </div>
    </div>
</div>