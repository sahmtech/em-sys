<div class="modal-dialog modal-lg" id="edit_driver_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.edit_driver')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\HousingMovements\Http\Controllers\DriverCarController@update', $driver->id),
                            'enctype' => 'multipart/form-data',
                            'method' => 'put',
                            'id' => 'carType_add_form',
                        ]) !!}

                        <div style="display: flex; justify-content: flex-start; align-items: center; ">
                            <div style="width: 400px; height: 200px; border-radius: 3px; overflow: hidden;">
                                <img src="{{ url('uploads/' . $driver->car_image) }}" alt="Image"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="row" style="margin-top: 8px">
                            <div class="col-sm-12" style="margin-top: 0px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select" style="padding: 2px;">
                                    {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}"
                                            @if ($worker->id == $driver->user_id) selected @endif>
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name . ' - ' . $worker->essentialsEmployeeAppointmets->specialization->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- <input type="text" id="searchWorkerInput" placeholder="Search Worker"
                                    style="margin-top: 5px;"> --}}
                            </div>

                            <div class="col-sm-12" style="margin-top: 5px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.car')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control" id="car__id" name="car_id" style="padding: 2px;"
                                    required>
                                    <option value="">@lang('messages.please_select')</option>

                                    @foreach ($cars as $car)
                                        <option value="{{ $car->id }}"
                                            @if ($car->id == $driver->car_id) selected @endif>
                                            {{ $car->plate_number . ' - ' . $car->CarModel?->CarType?->name_ar . ' - ' . $car->CarModel?->name_ar . ' - ' . $car->color }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="row" style="margin-top: 5px;">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('counter_number', __('housingmovements::lang.counter_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::number('counter_number', $driver->counter_number, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.counter_number'),
                                        'id' => 'counter_number',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('delivery_date', __('housingmovements::lang.delivery_date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::input('date', 'delivery_date', \Carbon\Carbon::parse($driver->delivery_date)->format('Y-m-d'), [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.delivery_date'),
                                        'id' => 'delivery_date',
                                        'min' => \Carbon\Carbon::now()->format('Y-m-d'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::file('car_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}


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

        $('#edit_driver_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });

            $('#car__id').select2({
                dropdownParent: $(
                    '#edit_driver_model'),
                width: '100%',
            });
        });

    });
</script>