    <div class="modal-dialog modal-lg" id="edit_car_model" role="document">
        <div class="modal-content">
            {!! Form::open([
                'url' => action('\Modules\Sales\Http\Controllers\ClientsController@changeStatusContact', $contact_id),
            
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'id' => 'change_status_form',
            ]) !!}

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.change_status')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <section class="content">

                            <div class="form-group">
                                <input type="hidden" name="selectedRowsData" id="selectedRowsData" />
                                <label for="status">@lang('sale.status'):*</label>
                                <select class="form-control select2" name="status" required id="status_dropdown"
                                    style="width: 100%;">
                                    @foreach ($status as $key => $value)
                                        <option value="{{ $key }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row" style="margin-top:8px; ">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('file_lead', __('sales::lang.file_lead') . ':*') !!}
                                        {!! Form::file('file_lead', ['class' => 'form-control', 'required', 'accept' => 'doc/*']) !!}


                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('note_lead', __('sales::lang.note_lead') . '') !!}
                                        {!! Form::text('nots', '', [
                                            'class' => 'form-control',
                                            'placeholder' => __('sales::lang.note_lead'),
                                            'id' => 'note_lead',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitFilesBtn">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
