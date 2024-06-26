<div class="modal fade" id="editEmployeesContractModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            {!! Form::open(['route' => 'updateEmployeeContract', 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('essentials::lang.add_contract')</h4>
            </div>

            <div class="modal-body">

                <div class="row">
                    {!! Form::hidden('contract_id', null, ['id' => 'contract_id']) !!}
                    <div class="form-group col-md-6">
                        <div class="form-group">
                            {!! Form::label('contract_number', __('essentials::lang.contract_number') . ':') !!}
                            {!! Form::text('contract_number', null, [
                                'class' => 'form-control',
                                'style' => 'height:36px',
                                'placeholder' => __('essentials::lang.contract_number'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contract_duration', __('essentials::lang.contract_duration') . ':') !!}
                        <div class="form-group">
                            <div class="multi-input">
                                <div class="input-group">
                                    {!! Form::number(
                                        'contract_duration',
                                        !empty($contract->contract_duration) ? $contract->contract_duration : null,
                                        [
                                            'class' => 'form-control width-40 pull-left',
                                            'style' => 'height:40px',
                                            'id' => 'contract_duration',
                                            'placeholder' => __('essentials::lang.contract_duration'),
                                        ],
                                    ) !!}
                                    {!! Form::select(
                                        'contract_duration_unit',
                                        ['years' => __('essentials::lang.years'), 'months' => __('essentials::lang.months')],
                                        !empty($contract->contract_per_period) ? $contract->contract_per_period : null,
                                        ['class' => 'form-control width-60 pull-left', 'style' => 'height:40px;', 'id' => 'contract_duration_unit'],
                                    ) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contract_start_date', __('essentials::lang.contract_start_date') . ':') !!}
                        {!! Form::date(
                            'contract_start_date',
                            !empty($contract->contract_start_date) ? $contract->contract_start_date : null,
                            [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'id' => 'contract_start_date',
                                'placeholder' => __('essentials::lang.contract_start_date'),
                            ],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('contract_end_date', __('essentials::lang.contract_end_date') . ':') !!}
                        {!! Form::date('contract_end_date', !empty($contract->contract_end_date) ? $contract->contract_end_date : null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'id' => 'contract_end_date',
                            'placeholder' => __('essentials::lang.contract_end_date'),
                        ]) !!}
                    </div>




                    <div class="form-group col-md-6">
                        {!! Form::label('probation_period', __('essentials::lang.probation_period') . ':*') !!}
                        {!! Form::number('probation_period', !empty($contract->probation_period) ? $contract->probation_period : null, [
                            'class' => 'form-control  pull-left',
                            'placeholder' => __('essentials::lang.probation_period_in_days'),
                            'required',
                        ]) !!}
                    </div>
                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('cancle_contract_under_trial', __('essentials::lang.cancle_contract_under_trial') . ':*') !!}
                        {!! Form::select(
                            'cancle_contract_under_trial',
                            [
                                'employee' => __('essentials::lang.by_employee'),
                                'work_owner' => __('essentials::lang.by_work_owner'),
                                'both' => __('essentials::lang.by_both_parties'),
                            ],
                            !empty($contract->cancle_contract_under_trial) ? $contract->cancle_contract_under_trial : null,
                            ['class' => 'form-control pull-left', 'style' => 'height:40px; width:100%'],
                        ) !!}

                    </div> --}}
                    <div class="form-group col-md-6">
                        {!! Form::label('is_renewable', __('essentials::lang.is_renewable') . ':*') !!}
                        {!! Form::select(
                            'is_renewable',
                            ['1' => __('essentials::lang.is_renewable'), '0' => __('essentials::lang.is_unrenewable')],
                            null,
                            ['class' => 'form-control pull-left', 'style' => 'height:40px; width:100%'],
                        ) !!}
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('contract_type', __('essentials::lang.contract_type') . ':*') !!}
                        {!! Form::select('contract_type', $contract_types, !empty($user->location_id) ? $user->location_id : null, [
                            'class' => 'form-control  pull-left ',
                            'style' => 'height:40px; width:100%',
                            'required',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>

                    <div class="clearfix"></div>
                    <div class="form-group col-md-6">
                        {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                        {!! Form::file('file', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.file'),
                            'required',
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
