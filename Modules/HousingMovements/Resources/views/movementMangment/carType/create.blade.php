<div class="modal-dialog modal-lg" id="add_car_type_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_carType')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\HousingMovements\Http\Controllers\CarTypeController@store'),
                            'method' => 'post',
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('name_ar', __('housingmovements::lang.name_ar') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name_ar', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.name_ar'),
                                        'id' => 'name_ar',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('name_en', __('housingmovements::lang.name_en') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::text('name_en', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.name_en'),
                                            'id' => 'name_en',
                                        ]) !!}
                                    </div>
                                </div>




                                <div class="row" style="margin-top: 150px;">
                                    <div class="col-sm-12"
                                        style="display: flex;
                                        justify-content: center;">
                                        <button type="submit"
                                            style="    width: 50%;
                                            border-radius: 28px;"
                                            id="add_car_type"
                                            class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
                                    </div>
                                </div>
                            </div>


                            {!! Form::close() !!}
                    </section>


                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
