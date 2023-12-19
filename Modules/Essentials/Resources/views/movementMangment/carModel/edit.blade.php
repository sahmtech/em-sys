<div class="modal-dialog modal-lg" id="edit_carModels_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('housingmovements::lang.edit_carModel')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\Essentials\Http\Controllers\CarModelController@update', $carModel->id),
                            'method' => 'put',
                            'id' => 'carType_edit_form',
                        ]) !!}


                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('name_ar', __('housingmovements::lang.name_ar') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name_ar', $carModel->name_ar, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.name_ar'),
                                        'id' => 'name_ar',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('name_en', __('housingmovements::lang.name_en') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name_en', $carModel->name_en, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.name_en'),
                                        'id' => 'name_en',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-sm-12">
                                {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control" name="car_type_id" id="car_type_id" style="padding: 2px;">

                                    @foreach ($carTypes as $type)
                                        <option value="{{ $type->id }}"
                                            @if ($carModel->car_type_id == $type->id) selected @endif>
                                            {{ $type->name_ar . ' - ' . $type->name_en }}</option>
                                    @endforeach
                                </select>

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
<script>
    $(document).ready(function() {

        $('#edit_carModels_model').on('shown.bs.modal', function(e) {
            $('#car_type_id').select2({
                dropdownParent: $(
                    '#edit_carModels_model'),
                width: '100%',
            });

        });

    });
</script>
