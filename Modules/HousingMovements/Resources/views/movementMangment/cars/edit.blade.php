<div class="modal-dialog modal-lg" id="edit_car_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('housingmovements::lang.edit_car')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\HousingMovements\Http\Controllers\CarController@update', $car->id),
                            'method' => 'put',
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">
                            <div class="col-sm-6" style="margin-top: 0px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control select2" name="user_id" id="worker_select"
                                    style="padding: 2px;">
                                    {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}"
                                            @if ($car->user_id == $worker->id) selected @endif>
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name . ' - ' . $worker->essentials_employee_appointmets->specialization->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-sm-6">
                                {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control select2" id="car_type_id_select" name="car_type_id"
                                    style="padding: 2px;" required>
                                    <option value="">@lang('messages.please_select')</option>

                                    @foreach ($carTypes as $carType)
                                        <option value="{{ $carType->id }}"
                                            @if ($carModel->car_type_id == $carType->id) selected @endif>
                                            {{ $carType->name_ar . ' - ' . $carType->name_en }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('carModel', __('housingmovements::lang.carModel') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control" name="car_model_id" id="carModel_id" required
                                        style="padding: 2px;">
                                        <option value="">@lang('messages.please_select')</option>
                                        @foreach ($carModels as $Model)
                                            <option value="{{ $Model->id }}"
                                                @if ($carModel->id == $Model->id) selected @endif>
                                                {{ $Model->name_ar . ' - ' . $Model->name_en }}</option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>

                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('plate_number', __('housingmovements::lang.plate_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('plate_number', $car->plate_number, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.plate_number'),
                                        'id' => 'plate_number',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('color', __('housingmovements::lang.color') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('color', $car->color, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.color'),
                                        'id' => 'color',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('number_seats', __('housingmovements::lang.number_seats') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::number('number_seats', $car->number_seats, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.number_seats'),
                                        'id' => 'number_seats',
                                    ]) !!}
                                </div>
                            </div>








                            <div class="row" style="margin-top: 220px;">
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
        $('#holidays').select2();


        $('#edit_car_model').on('shown.bs.modal', function(e) {
            $('#worker_select').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });

            $('#car_type_id_select').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });

            //     $('#carModel_id').select2({
            //         dropdownParent: $(
            //             '#add_car_model'),
            //         width: '100%',
            //     });
        });

    });
</script>
