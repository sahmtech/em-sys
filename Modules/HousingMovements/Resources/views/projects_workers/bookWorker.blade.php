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


                            <div class="col-sm-6">
                                {!! Form::label('worker', __('housingmovements::lang.worker')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select" style="padding: 2px;">
                                    {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('project', __('housingmovements::lang.project') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control " name="project_id" id="projects__select"
                                        style="padding: 2px;">
                                        {{-- <option value="all" selected>@lang('lang_v1.all')</option> --}}
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
