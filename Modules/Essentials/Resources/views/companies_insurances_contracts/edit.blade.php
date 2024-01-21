
<div class="modal-dialog" role="document">
                    <div class="modal-content">

            {!! Form::open(['route' => ['insurance_companies_contracts.update', $company_insurance->id], 'method' => 'post']) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.edit_companies_insurance_contracts')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                            {!! Form::hidden('company_id', $comp_id, ['id' => 'modal_company_id']) !!}
                    
                              <input class="hidden" name="modal_company_id" id="modal_company_id"/>
                            <div class="form-group col-md-6">
                            {!! Form::label('insurance_company', __('essentials::lang.insurance_company') . ':*') !!}
                            {!! Form::select('insurance_company', $insurance_companies, $company_insurance->insur_id?? null ,
                                 ['class' => 'form-control','style'=>'height:40px',
                                 'placeholder' => __('essentials::lang.insurance_company'),  'required']) !!}

                           
                        </div>
                       

                        <div class="form-group col-md-6">
                            {!! Form::label('insurance_start_date', __('essentials::lang.insurance_start_date') . ':') !!}
                            {!! Form::date('insurance_start_date',  $company_insurance->insurance_start_date ?? null , ['class' => 'form-control', 'style'=>'height:40px','placeholder' => __('essentials::lang.insurance_start_date'), ]) !!}
                        </div>

                  
                        <div class="form-group col-md-6 pull-left">
                            {!! Form::label('insurance_end_date', __('essentials::lang.insurance_end_date') . ':') !!}
                            {!! Form::date('insurance_end_date',   $company_insurance->insurance_end_date ?? null , ['class' => 'form-control','style'=>'height:40px', 'placeholder' => __('essentials::lang.insurance_end_date'), ]) !!}
                        </div>



                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
          