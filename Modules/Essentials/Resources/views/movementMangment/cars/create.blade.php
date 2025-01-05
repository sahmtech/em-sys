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
                            'url' => action('\Modules\Essentials\Http\Controllers\CarController@store'),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data',
                        
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">


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
                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('carModel', __('housingmovements::lang.carModel') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control" name="car_model_id" id="carModel_id" required
                                        style="padding: 2px;">
                                        <option value="">@lang('messages.please_select')</option>
                                    </select>


                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('plate_registration_type', __('housingmovements::lang.plate_registration_type') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    <select class="form-control" id="plate_registration_type"
                                        name="plate_registration_type" style="padding: 2px;" required>
                                        <option value="">@lang('messages.please_select')</option>

                                        <option value="private_transfer">
                                            {{ __('housingmovements::lang.private_transfer') }}</option>
                                        <option value="private">{{ __('housingmovements::lang.private') }}</option>
                                        <option value="motorcycle">{{ __('housingmovements::lang.motorcycle') }}
                                        </option>


                                    </select>
                                </div>
                            </div>




                        </div>

                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('serial_number', __('housingmovements::lang.serial_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::number('serial_number', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.serial_number'),
                                        'id' => 'serial_number',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('structure_no', __('housingmovements::lang.structure_no') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('structure_no', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.structure_no'),
                                        'id' => 'structure_no',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('manufacturing_year', __('housingmovements::lang.manufacturing_year') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    <select class="form-control" name="manufacturing_year" id="manufacturing_year">
                                        @php
                                            $currentYear = date('Y') + 1; // Add 1 to the current year
                                            for ($year = $currentYear; $year >= 2000; $year--) {
                                                echo '<option value="' . $year . '">' . $year . '</option>';
                                            }
                                        @endphp



                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('vehicle_status', __('housingmovements::lang.vehicle_status') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('vehicle_status', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.vehicle_status'),
                                        'id' => 'vehicle_status',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">


                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('test_end_date', __('housingmovements::lang.test_end_date') . '  ') !!}
                                    {!! Form::date('test_end_date', '', [
                                        'class' => 'form-control',
                                      
                                        'placeholder' => __('housingmovements::lang.test_end_date'),
                                        'id' => 'test_end_date',
                                    ]) !!}
                                </div>
                            </div> --}}
                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('examination_status', __('housingmovements::lang.examination_status') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control" id="examination_status" name="examination_status"
                                        style="padding: 2px;" required>
                                        <option value="">@lang('messages.please_select')</option>
                                        <option value="not_expired">{{ __('housingmovements::lang.not_expired') }}
                                        </option>
                                        <option value="expired">{{ __('housingmovements::lang.expired') }}</option>
                                        <option value="nothing">{{ __('housingmovements::lang.nothing') }}</option>


                                    </select>

                                </div>
                            </div> --}}
                        </div>

                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('number_seats', __('housingmovements::lang.number_seats') . '  ') !!}
                                    {!! Form::number('number_seats', '', [
                                        'class' => 'form-control',
                                    
                                        'placeholder' => __('housingmovements::lang.number_seats'),
                                        'id' => 'number_seats',
                                    ]) !!}
                                </div>
                            </div>
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
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('expiry_date', __('housingmovements::lang.expiry_date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::date('expiry_date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.expiry_date'),
                                        'id' => 'expiry_date',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
                                    {!! Form::file('car_image[]', ['class' => 'form-control', 'accept' => 'image/*', 'multiple' => 'multiple']) !!}


                                </div>
                            </div>
                            {{-- 
                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_company_id', __('housingmovements::lang.insurance_company_id') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    <select class="form-control" id="insurance_company_id" name="insurance_company_id"
                                        style="padding: 2px;" required>
                                        <option value="">@lang('messages.please_select')</option>
                                        
                                        @foreach ($insurance_companies as $insurance_company)
                                        <option value="{{ $insurance_company->id }}">
                                            {{ $insurance_company->name }}</option>
                                    @endforeach

                                    </select>

                                </div>
                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_start_Date', __('housingmovements::lang.insurance_start_Date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date('insurance_start_Date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.insurance_start_Date'),
                                        'id' => 'insurance_start_Date',
                                    ]) !!}

                                </div>
                            </div>
                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_end_date', __('housingmovements::lang.insurance_end_date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date('insurance_end_date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.insurance_end_date'),
                                        'id' => 'insurance_end_date',
                                    ]) !!}
                                </div>
                            </div> --}}

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
            $('#examination_status').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });
            $('#manufacturing_year').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });
        });


    });
</script>
