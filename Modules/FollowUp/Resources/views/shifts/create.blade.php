<div class="modal-dialog modal-lg" id="add_shits_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_shift')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\FollowUp\Http\Controllers\ShiftController@store'),
                            'method' => 'post',
                            'id' => 'shift_add_form',
                        ]) !!}

                        <div class="row">

                            <div class="col-sm-6">
                                {!! Form::label('project_name_label', __('housingmovements::lang.contact_name')) !!}<span style="color: red; font-size:10px"> *</span>
                                {!! Form::select('contacts', $contacts, null, [
                                    'class' => 'form-control',
                                    // 'style' => 'padding:2px;',
                                    'placeholder' => __('messages.please_select'),
                                    'id' => 'contacts_select',
                                ]) !!}

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('project_name_label', __('housingmovements::lang.project_name')) !!}<span style="color: red; font-size:10px"> *</span>
                                    <select class="form-control" name="project_id" id="project_id" required
                                        style="padding: 2px;">
                                        <option value="">@lang('messages.please_select')</option>
                                    </select>


                                </div>
                            </div>



                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('shift_name', __('housingmovements::lang.shift_name') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.shift_name'),
                                        'id' => 'shift_name',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                {!! Form::label('start_time', __('restaurant.start_time') . '') !!}<span style="color: red; font-size:10px"> *</span>
                                <div class="form-group">

                                    {!! Form::input('time', 'start_time', null, [
                                        'class' => 'form-control',
                                        'placeholder' => __('restaurant.start_time'),
                                        // 'readonly',
                                        'required',
                                    ]) !!}
                                    {{-- <span class="input-group-addon"><i class="fas fa-clock"></i></span> --}}
                                </div>

                            </div>



                            <div class="col-sm-6">
                                {!! Form::label('end_time', __('restaurant.end_time') . '') !!}<span style="color: red; font-size:10px"> *</span>
                                <div class="form-group">
                                    {!! Form::input('time', 'end_time', null, [
                                        'class' => 'form-control date',
                                        'placeholder' => __('restaurant.end_time'),
                                        // 'readonly',
                                        'required',
                                    ]) !!}

                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 8px;">

                            <div class="col-sm-12">

                                {!! Form::label('holidays', __('essentials::lang.holiday') . ' ') !!}

                            </div>
                            <div class="form-group">
                                <div class="col-md-12">

                                    {!! Form::select(
                                        'holidays[]',
                                        $days,
                                        [],
                                        [
                                            'class' => 'form-control select2',
                                            'multiple',
                                            'id' => 'holidays',
                                            'style' => 'width: 100% !important;',
                                        ],
                                    ) !!}
                                </div>


                            </div>
                        </div>

                        <div class="row" style="margin-top: 220px;">
                            <div class="col-sm-12" style="display: flex;
                justify-content: center;">
                                <button type="submit" style="    width: 50%;
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
        $('#holidays').select2();


        $('#add_shits_model').on('shown.bs.modal', function(e) {
            $('#contacts_select').select2({
                dropdownParent: $(
                    '#add_shits_model'),
                width: '100%',
            });
            $('#project_id').select2({
                dropdownParent: $(
                    '#add_shits_model'),
                width: '100%',
            });
        });


        $(document).on('change', '#contacts_select', function() {
            if ($(this).val() !== '') {
                $.ajax({
                    url: '/followup/projects-by-contacts/' + $(this).val(),
                    dataType: 'json',
                    success: function(result) {
                        console.log(result);
                        $('#project_id')
                        $('#project_id').empty();
                        $.each(result, function(index, project) {
                            $('#project_id').append('<option value="' + project
                                .id + '">' + project.name + '</option>');
                        });

                    },
                });
            }
        })


    });
</script>
