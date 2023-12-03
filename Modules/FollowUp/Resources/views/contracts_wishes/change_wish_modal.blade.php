<div class="modal fade"  id="change_status_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
            <div class="modal-content">

                    {!! Form::open(['url' => action([\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish']), 'method' => 'post', 'id' => 'change_status_form'  ,'enctype' => 'multipart/form-data']) !!}
                    

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang( 'essentials::lang.change_status' )</h4>
                        </div>

                       
                        <div class="modal-body">
                                <div class="row">
                                        <div class="form-group  col-md-6">
                                                                <input type="hidden" name="employee_id" id="employee_id">

                                                    
                                                    
                                                                        {!! Form::label('offer_status_filter', __('followup::lang.wish') . ':') !!}
                                                                        {!! Form::select('wish',
                                                                            $wishes, null,
                                                                            ['class' => 'form-control',
                                                                            'id'=>'status_dropdown',
                                                                            'style' => ' height:40px;width:100%',
                                                                            'placeholder' => __('lang_v1.all')]); !!}
                                                                
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="form-group  col-md-8">
                                                <button type="button" id="viewWishFile" class="btn btn-primary" style="display: none;">
                                                                            @lang('essentials::lang.view_wish_file')
                                                </button>
                                        </div>

                                        <div class="form-group col-md-8" id="noWishFile" style="display: none;">
                                                                        <button type="button"  class="btn btn-primary">
                                                                        @lang('essentials::lang.no_wish_file_to_show')
                                                                        </button>
                                                                            <div class="clearfix"></div>
                                                                            </br>

                                                                                                {!! Form::label('file', __('essentials::lang.wish_file_select') . ':*') !!}
                                                                                                {!! Form::file('file', null, [
                                                                                                    'class' => 'form-control',
                                                                                                    'placeholder' => __('essentials::lang.wish_file'),
                                                                                                
                                                                                                    'style'=>'height:40px',
                                                                                                ]) !!}
                                        </div>
                                                                                            
                                                              
                                </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary ladda-button update-offer-status" data-style="expand-right">
                                <span class="ladda-label">@lang('messages.update')</span>
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>

                    
                    {!! Form::close() !!}
            </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>


