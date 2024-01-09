<div class="modal-dialog modal-lg" id="add_carsChangeOil_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_carsChangeOil')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\Essentials\Http\Controllers\CarsChangeOilController@store'),
                            'method' => 'post',
                            'id' => 'carsChangeOil_add_form',
                        ]) !!}


                        <div class="row">

                            <div class="row">

                                <div class="col-sm-6">
                                    {!! Form::label('carType_label', __('housingmovements::lang.car')) !!}<span style="color: red; font-size:10px"> *</span>

                                    <select class="form-control" id="car_id" name="car_id" style="padding: 2px;"
                                        required>
                                        <option value="">@lang('messages.please_select')</option>

                                        @foreach ($cars as $car)
                                            <option value="{{ $car->id }}">
                                                {{ $car->plate_number . ' - ' . $car->CarModel?->CarType?->name_ar . ' - ' . $car->CarModel?->name_ar . ' - ' . $car->color }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('current_speedometer', __('housingmovements::lang.current_speedometer') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::number('current_speedometer', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.current_speedometer'),
                                            'id' => 'current_speedometer',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('next_change_oil', __('housingmovements::lang.next_change_oil') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::date('next_change_oil', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.next_change_oil'),
                                            'id' => 'next_change_oil',
                                        ]) !!}
                                    </div>
                                </div>



                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('invoice_no', __('housingmovements::lang.invoice_no') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::text('invoice_no', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.invoice_no'),
                                            'id' => 'invoice_no',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('date', __('housingmovements::lang.date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::date('date', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.date'),
                                            'id' => 'date',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 180px;">
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

        $('#add_carsChangeOil_model').on('shown.bs.modal', function(e) {
            $('#car_type_select').select2({
                dropdownParent: $(
                    '#add_carsChangeOil_model'),
                width: '100%',
            });

        });

    });
</script>
