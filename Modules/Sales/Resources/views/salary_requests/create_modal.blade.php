<div class="modal fade item_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => 'store_salay_request', 'enctype' => 'multipart/form-data']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('sales::lang.add_salary_request')</h4>
            </div>
            <input type="hidden" id="salaryrequestIdInput" name="salaryrequestIdInput">
            <div class="modal-body">
                <div class="row">
                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('workers', __('sales::lang.worker_name') . ':*') !!}
                        {!! Form::select('workers', $workers->pluck('full_name', 'id'), null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('followup::lang.choose_worker'),
                            'required',
                            'id' => 'workers_select',
                            'style' => 'height:40px; width:100%',
                        ]) !!}
                    </div> --}}
                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('salary', __('sales::lang.salary') . ':*') !!}
                        {!! Form::number('salary', null, ['class' => 'form-control', 'required']) !!}
                    </div> --}}

                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('arrival_period', __('sales::lang.arrival_period') . ':') !!}
                        {!! Form::date('arrival_period', null, ['class' => 'form-control']) !!}
                    </div> --}}

                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('recruitment_fees', __('sales::lang.recruitment_fees') . ':') !!}
                        {!! Form::number('recruitment_fees', null, ['class' => 'form-control']) !!}
                    </div> --}}
                    {{--  --}}



                    <div class="form-group col-md-6">
                        {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                        {!! Form::select('profession', $professions, null, [
                            'class' => 'form-control',
                            'required',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.profession'),
                            'id' => 'professionSelect',
                        ]) !!}

                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                        {!! Form::select('specialization', $specializations, null, [
                            'class' => 'form-control',
                            'required',
                            'style' => ' height: 40px',
                            'placeholder' => __('sales::lang.specialization'),
                            'id' => 'specializationSelect',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('nationlity', __('essentials::lang.nationlity') . ':*') !!}
                        {!! Form::select('nationlity', $nationalities, null, [
                            'class' => 'form-control',
                            'id' => 'nationlity_select',
                            'required',
                            'style' => ' height: 40px',
                            'placeholder' => __('essentials::lang.nationlity'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('quantity', __('essentials::lang.quantity') . ':*') !!}
                        {!! Form::number('quantity', null, [
                            'class' => 'form-control',
                            'style' => ' height: 40px',
                            'required',
                            'placeholder' => __('essentials::lang.quantity'),
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('note', __('followup::lang.note') . ':') !!}
                        {!! Form::textarea('note', null, [
                            'class' => 'form-control',
                            'placeholder' => __('followup::lang.note'),
                            'rows' => 3,
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('file', __('sales::lang.file') . ':') !!}
                        {!! Form::file('file', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.file'),
                        
                            'style' => 'height:40px',
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
