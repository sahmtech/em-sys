<div class="modal-dialog modal-lg" id="add_driver_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_driver')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\Essentials\Http\Controllers\DriverCarController@store'),
                            'enctype' => 'multipart/form-data',
                            'method' => 'post',
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">
                            <div class="col-sm-12" style="margin-top: 0px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select" style="padding: 2px;">
                                    {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name . ' - ' . $worker->essentialsEmployeeAppointmets->profession->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- <input type="text" id="searchWorkerInput" placeholder="Search Worker"
                                    style="margin-top: 5px;"> --}}
                            </div>

                            <div class="col-sm-12" style="margin-top: 5px;">
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
                        </div>

                        <div class="row" style="margin-top: 5px;">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('counter_number', __('housingmovements::lang.counter_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::number('counter_number', '', [
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
                                    {!! Form::input('date', 'delivery_date', '', [
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
                                    {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
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

        $('#add_car_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });

            $('#car_id').select2({
                dropdownParent: $(
                    '#add_car_model'),
                width: '100%',
            });
        });

    });
</script>
