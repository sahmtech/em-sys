<div class="modal-dialog modal-lg" id="book_worker_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-bookmark"></i> @lang('housingmovements::lang.book_worker')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\HousingMovements\Http\Controllers\WorkerBookingController@store'),
                            'method' => 'post',
                            'id' => 'carType_add_form',
                        ]) !!}


                        <div class="row">


                            <input type="hidden" name="user_id" value="{{ $worker->id }}" />
                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('booking_start_Date', __('housingmovements::lang.booking_start_Date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date('booking_start_Date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.booking_start_Date'),
                                        'id' => 'booking_start_Date',
                                        'min' => \Carbon\Carbon::now()->format('Y-m-d'),
                                    ]) !!}

                                </div>
                            </div>
                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('booking_end_Date', __('housingmovements::lang.booking_end_Date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date('booking_end_Date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.booking_end_Date'),
                                        'id' => 'booking_end_Date',
                                        'min' => \Carbon\Carbon::now()->format('Y-m-d'),
                                    ]) !!}

                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('project', __('housingmovements::lang.project') . '  ') !!}
                                    <select class="form-control " name="project_id" id="projects__select"
                                        style="padding: 2px;">
                                        <option value="" selected>@lang('housingmovements::lang.no_project')</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>


                        <div class="row">





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

        $('#book_worker_model').on('shown.bs.modal', function(e) {
            $('#worker__select').select2({
                dropdownParent: $(
                    '#book_worker_model'),
                width: '100%',
            });

            $('#projects__select').select2({
                dropdownParent: $(
                    '#book_worker_model'),
                width: '100%',
            });


        });


    });
</script>
