<div class="modal-dialog modal-lg" id="show_car_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"> @lang('housingmovements::lang.car_info')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                    

                        <div class="row">
                            <div class="col-sm-6">
                                {!! Form::label('carType_label', __('housingmovements::lang.carType')) !!} :
                                @foreach ($carTypes as $carType)
                                    @if ($carModel->car_type_id == $carType->id)
                                        {!! Form::label('carType_label', $carType->name_ar . ' - ' . $carType->name_en) !!}
                                    @endif
                                @endforeach

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('carModel', __('housingmovements::lang.carModel') . '  ') !!} :
                                    @foreach ($carModels as $Model)
                                        @if ($carModel->id == $Model->id)
                                            {!! Form::label('carType_label', $Model->name_ar . ' - ' . $Model->name_en) !!}
                                        @endif
                                    @endforeach


                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6" style="margin-top: 5px;">
                                <div class="form-group">
                                    {!! Form::label('plate_number', __('housingmovements::lang.plate_number') . ' :  ') !!}
                                    {!! Form::label('plate_number', $car->plate_number . '  ') !!}
                                    {{-- {!! Form::text('plate_number', $car->plate_number, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.plate_number'),
                                        'id' => 'plate_number',
                                    ]) !!} --}}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('plate_registration_type', __('housingmovements::lang.plate_registration_type') . ' : ') !!}
                                    @if ($car->plate_registration_type == 'private_transfer')
                                        {!! Form::label('plate_registration_type', __('housingmovements::lang.private_transfer') . '  ') !!}
                                    @endif
                                    @if ($car->plate_registration_type == 'private')
                                        {!! Form::label('plate_registration_type', __('housingmovements::lang.private') . '  ') !!}
                                    @endif
                                    @if ($car->plate_registration_type == 'motorcycle')
                                        {!! Form::label('plate_registration_type', __('housingmovements::lang.motorcycle') . '  ') !!}
                                    @endif
                                </div>
                            </div>
                        </div>


                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('serial_number', __('housingmovements::lang.serial_number') . ' :  ') !!}
                                    {!! Form::label('serial_number', $car->serial_number . '  ') !!}
                                    {{-- {!! Form::number('serial_number', $car->serial_number, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.serial_number'),
                                        'id' => 'serial_number',
                                    ]) !!} --}}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('structure_no', __('housingmovements::lang.structure_no') . ' : ') !!}
                                    {!! Form::label('structure_no', $car->structure_no . '  ') !!}
                                    {{-- {!! Form::text('structure_no', $car->structure_no, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.structure_no'),
                                        'id' => 'structure_no',
                                    ]) !!} --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('manufacturing_year', __('housingmovements::lang.manufacturing_year') . ' : ') !!}

                                    {{-- <select class="form-control" name="manufacturing_year" id="manufacturing_year"> --}}
                                    @php
                                        $currentYear = date('Y');
                                        $carbonDate = \Carbon\Carbon::createFromFormat(
                                            'Y-m-d',
                                            $car->manufacturing_year,
                                        );
                                        $year_stored = $carbonDate->year;
                                        // for ($year = $currentYear; $year >= 1900; $year--) {
                                        //     if ($year_stored == $year) {
                                        //         echo '<option value="' .
                                        //             $year .
                                        //             '" selected>' .
                                        //             $year .
                                        //             '</option>';
                                        //     } else {
                                        //         echo '<option value="' . $year . '">' . $year . '</option>';
                                        //     }
                                        // }
                                    @endphp

                                    {!! Form::label('manufacturing_year', $year_stored . '  ') !!}

                                    {{-- </select> --}}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('vehicle_status', __('housingmovements::lang.vehicle_status') . ' : ') !!}
                                    {!! Form::label('vehicle_status', $car->vehicle_status . '  ') !!}
                                    {{-- {!! Form::text('vehicle_status', $car->vehicle_status, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.vehicle_status'),
                                        'id' => 'vehicle_status',
                                    ]) !!} --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">


                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('test_end_date', __('housingmovements::lang.test_end_date') . '  ') !!}
                                    {!! Form::date('test_end_date', $car->test_end_date, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.test_end_date'),
                                        'id' => 'test_end_date',
                                    ]) !!}
                                </div>
                            </div> --}}
                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('examination_status', __('housingmovements::lang.examination_status') . '  ') !!}
                                    <select class="form-control" id="examination_status" name="examination_status"
                                        style="padding: 2px;" required>
                                        <option value="">@lang('messages.please_select')</option>
                                        <option value="not_expired" @if ($car->examination_status == 'not_expired') selected @endif>
                                            {{ __('housingmovements::lang.not_expired') }}
                                        </option>
                                        <option value="expired" @if ($car->examination_status == 'expired') selected @endif>
                                            {{ __('housingmovements::lang.expired') }}</option>
                                        <option value="nothing" @if ($car->examination_status == 'nothing') selected @endif>
                                            {{ __('housingmovements::lang.nothing') }}</option>


                                    </select>

                                </div>
                            </div> --}}
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('color', __('housingmovements::lang.color') . ' : ') !!}
                                    {!! Form::label('color', $car->color . '  ') !!}
                                    {{-- {!! Form::text('color', $car->color, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.color'),
                                        'id' => 'color',
                                    ]) !!} --}}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('number_seats', __('housingmovements::lang.number_seats') . ' : ') !!}
                                    {!! Form::label('number_seats', $car->number_seats . '  ') !!}
                                    {{-- {!! Form::number('number_seats', $car->number_seats, [
                                        'class' => 'form-control',
                                    
                                        'placeholder' => __('housingmovements::lang.number_seats'),
                                        'id' => 'number_seats',
                                    ]) !!} --}}
                                </div>
                            </div>
                        </div>



                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('expiry_date', __('housingmovements::lang.expiry_date') . ' : ') !!}
                                    {!! Form::label('expiry_date', $car->expiry_date . '  ') !!}
                                    {{-- {!! Form::date('expiry_date', $car->expiry_date, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.expiry_date'),
                                        'id' => 'expiry_date',
                                    ]) !!} --}}
                                </div>
                            </div>
                            <div class="col-sm-12">

                                @if ($carImage)
                                    @foreach ($carImage as $image)
                                        <div class="col-sm-3">
                                            <img src="{{ url('car_images/' . $image->car_image) }}"
                                                style="    width: 150px;">
                                        </div>
                                    @endforeach

                                @endif
                            </div>
                            <div class="row" style="margin-top: 220px;">
                                <div class="col-sm-12"
                                    >
                                    <button type="button" class="btn btn-primary no-print" aria-label="Print"
                                        onclick="$(this).closest('div.modal-content').printThis();">
                                        <i class="fa fa-print"></i> @lang('messages.print')
                                    </button>
                                    <button type="button" class="btn btn-default no-print"
                                        data-dismiss="modal">@lang('messages.close')
                                </div>
                            </div>
                        </div>

                </div>


                {{-- {!! Form::close() !!} --}}
                </section>


            </div>

        </div>
    </div>

</div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {


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

            $('#manufacturing_year').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });
        });

    });
</script>
