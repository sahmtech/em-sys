<!-- Add this modal markup to your Blade file -->
<div class="modal fade" id="editQualificationModal" tabindex="-1" role="dialog" aria-labelledby="editQualificationModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        {!! Form::open(['route' => ['updateQualification', 'qualificationId'], 'enctype' => 'multipart/form-data']) !!}

            <div class="modal-header">
                <h5 class="modal-title" id="editQualificationModalLabel">@lang('messages.edit')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <input type="hidden" id="qualificationIdInput" name="qualificationId">

                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                {!! Form::select('employee',$users, null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.select_employee'), 'required']) !!}

                            </div>
                        
                            <div class="form-group col-md-6">
                                {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                                {!! Form::select('qualification_type', [
                                    'bachelors'=>__('essentials::lang.bachelors'),
                                     'master' =>__('essentials::lang.master'),
                                     'PhD' =>__('essentials::lang.PhD'),
                                     'diploma' =>__('essentials::lang.diploma'),
                             
                                 ], null, ['class' => 'form-control',
                                  'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')]); !!}
                             </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('major', __('essentials::lang.major') . ':*') !!}
                                {!! Form::select('major',$spacializations, null, ['class' => 'form-control','style'=>'height:40px',
                                     'placeholder' =>  __('essentials::lang.major'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                                {!! Form::date('graduation_year', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_year'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-7">
                                {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                                {!! Form::text('graduation_institution', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.graduation_institution'), 'required']) !!}
                            </div>
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                                {!! Form::select('graduation_country',$countries, null, ['class' => 'form-control','style'=>'height:40px',
                                     'placeholder' =>  __('essentials::lang.select_country'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                {!! Form::number('degree', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.degree'), 'required', 'step' => 'any']) !!}
                            </div>

                            
                        </div>
                    </div>
        

            <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
