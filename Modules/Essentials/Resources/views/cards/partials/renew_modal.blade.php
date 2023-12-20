
    <div id="renewModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document" >
            <div class="modal-content" >
        
            <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.renewal_residence')</h4>
            </div>


            {!! Form::open(['url' => action( [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_data']), 'method' => 'post', 'id' => 'renew_form' ]) !!}
                <div class="modal-body">
                <input name="building_htr" id="building_htr" type="hidden" value="300" />
                        
                <div class="row">
                        <div class="form-group col-md-2">
                                    {!! Form::label('proof_number', __('essentials::lang.Residency_no') . ':*') !!}
                                    {!! Form::text('proof_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.Residency_no'), 'required']) !!}
                        </div>


                        <div class="form-group col-md-2">
                            {!! Form::label('expiration_date', __('essentials::lang.Residency_end_date') . ':*') !!}
                            {!! Form::text('expiration_date', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.Residency_end_date'), 'required']) !!}
                        </div>
                    

                        <div class="form-group col-md-2">
                            {!! Form::label('workcard_duration', __('essentials::lang.renew_duration') . ':*') !!}
                            {!! Form::number('workcard_duration', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.renew_duration'), 'required']) !!}
                        </div>
                
                        <div class="form-group col-md-2">
                            {!! Form::label('fees', __('essentials::lang.fees') . ':*') !!}
                            {!! Form::number('fees', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.fees'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-2">
                            {!! Form::label('Payment_number', __('essentials::lang.pay_number') . ':*') !!}
                            {!! Form::number('Payment_number', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.pay_number'), 'required']) !!}
                        </div>

                        <div class="form-group col-md-2">
                            {!! Form::label('fixnumber', __('essentials::lang.fixed') . ':*') !!}
                            {!! Form::number('fixnumber', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.fixed'), 'required']) !!}
                        </div>
                
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
