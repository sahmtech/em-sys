    <div class="modal fade" id="createWorkCardModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">@lang('essentials::lang.create_work_cards')</h4>

                </div>
                <!-- Modal body -->
                {!! Form::open(['url' => route('card.store'), 'method' => 'post', 'id' => 'workCardForm']) !!}
                <div class="modal-body">


                    <div class="row">
                        <div class="col-md-12 col-sm-12">


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('employees', __('essentials::lang.choose_card_owner') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('employees[]', $employees, null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%;',
                                            'placeholder' => __('lang_v1.all'),
                                            'id' => 'employees',
                                            'required',
                                            'onchange' => 'getData(this.value)',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('all_responsible_users', __('essentials::lang.select_responsible_users') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('all_responsible_users[]', [], null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%;',
                                            'id' => 'responsible_users',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('responsible_client', __('essentials::lang.responsible_client') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('responsible_client[]', [], null, [
                                            'class' => 'form-control select2',
                                            'style' => 'width: 100%;',
                                            'id' => 'responsible_client',
                                            'multiple' => 'multiple',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                {!! Form::hidden('employee_id', null, ['id' => 'employee_id']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::hidden('responsible_user_id', null, ['id' => 'responsible_user_id']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::hidden('Residency_id', null, ['id' => 'Residency_id']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::hidden('border_id', null, ['id' => 'border_id']) !!}
                            </div>

                            <div class="clearfix"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('Residency_no', __('essentials::lang.Residency_no') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Residency_no', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.Residency_no'),
                                            'style' => 'width: 100%;',
                                            'id' => 'Residency_no',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 border_no">
                                <div class="form-group">
                                    {!! Form::label('border_no', __('essentials::lang.border_number') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('border_no', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.border_number'),
                                            'style' => 'width: 100%;',
                                            'id' => 'border_no',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4" id="Residency_end_date_id">
                                <div class="form-group">
                                    {!! Form::label('Residency_end_date', __('essentials::lang.Residency_end_date') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::date('Residency_end_date', null, [
                                            'class' => 'form-control',
                                            'style' => 'width: 100%;',
                                            'placeholder' => __('essentials::lang.Residency_end_date'),
                                            'id' => 'Residency_end_date',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('business', __('essentials::lang.business') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-building"></i>
                                        </span>
                                        {!! Form::select('business', $companies, null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.business'),
                                            'id' => 'business',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('workcard_duration', __('essentials::lang.work_card_duration') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('workcard_duration_input', $durationOptions, null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'id' => 'workcard_duration_input',
                                            'required',
                                            'placeholder' => __('essentials::lang.work_card_duration'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('fees', __('essentials::lang.passport_fees') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('passport_fees_input', [], null, [
                                            'class' => 'form-control',
                                            'id' => 'fees_input',
                                            'required',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.passport_fees'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('work_card_fees', __('essentials::lang.work_card_fees') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('work_card_fees', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.work_card_fees'),
                                            'id' => 'work_card_fees',
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('other_fees', __('essentials::lang.other_fees') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('other_fees', null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('essentials::lang.other_fees'),
                                            'style' => 'width: 100%;',
                                            'id' => 'other_fees',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('pay_number', __('essentials::lang.pay_number') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Payment_number', null, [
                                            'class' => 'form-control',
                                            'id' => 'Payment_number',
                                            'placeholder' => __('essentials::lang.pay_number'),
                                        ]) !!}
                                    </div>
                                    <div id="error-message" style="color: red; display: none;">You cannot enter more
                                        than 14 numbers</div>

                                </div>
                            </div>

                        </div>
                    </div>



                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary " id="saveButton">@lang('messages.save')</button>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
