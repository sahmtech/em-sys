<div class="modal fade" id="editdocModal" tabindex="-1" role="dialog" aria-labelledby="editdocModal">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
    {!! Form::open(['route' => ['updateDoc','docId'], 'method' => 'post', 'id' => 'edit_doc_form' , 'enctype' => 'multipart/form-data']) !!}


            <div class="modal-header">
                
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang( 'essentials::lang.edit_doc' )</h4>
            </div>
        
            <div class="modal-body">
        
                <div class="row">
                <input type="hidden" id="docIdInput" name="docId">
                    <div class="form-group col-md-6">
                        {!! Form::label('employee', __( 'essentials::lang.employee' ) . ':*') !!}
                        {!! Form::select('employee', $users,null, ['class' => 'form-control select2','style'=>'height:36px; width:100%',
                             'placeholder' => __('essentials::lang.select_employee')]) !!}
                    </div>
            
                    <div class="form-group col-md-6">
                        {!! Form::label('doc_type', __('essentials::lang.doc_type') . ':*') !!}
                        {!! Form::select('doc_type', [
                        
                            'national_id'=>__('essentials::lang.national_id'),
                            'passport'=>__('essentials::lang.passport'),
                            'residence_permit'=>__('essentials::lang.residence_permit'),
                            'drivers_license'=>__('essentials::lang.drivers_license'),
                            'car_registration'=>__('essentials::lang.car_registration'),
                            'international_certificate'=>__('essentials::lang.international_certificate'),
                        ], null, ['class' => 'form-control']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('doc_number', __('essentials::lang.doc_number') . ':') !!}
                        {!! Form::number('doc_number',null, ['class' => 'form-control',
                             'placeholder' => __('essentials::lang.doc_number')]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('issue_date', __('essentials::lang.issue_date') . ':') !!}
                        {!! Form::date('issue_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_date')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('issue_place', __('essentials::lang.issue_place') . ':') !!}
                        {!! Form::text('issue_place', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.issue_place')]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('status', __('essentials::lang.status') . ':*') !!}
                        {!! Form::select('status', [
                        'valid' => __('essentials::lang.valid'),
                        'expired' => __('essentials::lang.expired'),
                    
                    ],  null, ['class' => 'form-control', 'placeholder' => 'Select status']) !!}
                </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                        {!! Form::date('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.expiration_date')]) !!}
                    </div>
                
                                <!-- Add a file input here -->
                    <div class="form-group col-md-6">
                        {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                        {!! Form::file('docfile', ['class' => 'form-control', 'placeholder' => __('essentials::lang.file')]) !!}
                    </div>
                    <!-- Existing file container -->
                    <div class="form-group col-md-6 file-container">
                        <!-- File content will be dynamically added here -->
                    </div>
                </div>
            </div>

        
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
 </div>
