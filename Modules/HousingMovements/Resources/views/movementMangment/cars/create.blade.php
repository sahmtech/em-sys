<div class="modal-dialog modal-lg" id="add_car_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_car')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\HousingMovements\Http\Controllers\CarController@store'),
                            'method' => 'post',
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">
                            <div class="col-sm-6" style="margin-top: 0px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select" style="padding: 2px;">
                                    {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name . ' - ' . $worker->essentials_employee_appointmets->specialization->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- <input type="text" id="searchWorkerInput" placeholder="Search Worker"
                                    style="margin-top: 5px;"> --}}
                            </div>

                            <div class="col-sm-6">
                                {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control" id="car_type_id" name="car_type_id" style="padding: 2px;"
                                    required>
                                    <option value="">@lang('messages.please_select')</option>

                                    @foreach ($carTypes as $carType)
                                        <option value="{{ $carType->id }}">
                                            {{ $carType->name_ar . ' - ' . $carType->name_en }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('carModel', __('housingmovements::lang.carModel') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control" name="car_model_id" id="carModel_id" required
                                        style="padding: 2px;">
                                        <option value="">@lang('messages.please_select')</option>
                                    </select>


                                </div>
                            </div>

                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('plate_number', __('housingmovements::lang.plate_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('plate_number', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.plate_number'),
                                        'id' => 'plate_number',
                                    ]) !!}
                                </div>
                            </div>

                        </div>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('color', __('housingmovements::lang.color') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('color', '', [
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
                                    {!! Form::number('number_seats', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.number_seats'),
                                        'id' => 'number_seats',
                                    ]) !!}
                                </div>
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

                        {!! Form::close() !!}
                    </section>


                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {

        $('#add_car_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });

            $('#car_type_id').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });
        });

    });
</script>
