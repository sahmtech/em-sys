   <div class="modal fade" id="addDeputyModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('storeDeputy', ':id') }}" method="POST" id="DeputyForm">
                        @csrf
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.add_deputy')</h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                                    {!! Form::select('employee', $users, null, [
                                        'class' => 'form-control',
                                        'id' => 'employeeSelect',
                                        
                                        'placeholder' => __('essentials::lang.select_employee'),
                                        'required',
                                    ]) !!}
                                </div>

                                <div class="form-group  col-md-6">
                                    {!! Form::label('profession', __('essentials::lang.job_title') . ':*') !!}
                                    {!! Form::select('profession',$professions, null, [
                                        'class' => 'form-control profession-select',
                                        'required',
                                        
                                        'id'=>'deputy_profession_selector',
                                        'placeholder' => __('essentials::lang.job_title'),
                                          
                                    ]) !!}

                                </div>

                                <div class=" col-md-6">
                                    {!! Form::label('start_date', __('essentials::lang.start_date') . ':*') !!}
                                    {!! Form::date('start_date', null, [
                                        'class' => 'form-control',
                                      
                                        'placeholder' => __('essentials::lang.start_date'),
                                        'required',
                                    ]) !!}
                                </div>


                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary"
                                id="saveDeputyBtn">@lang('messages.save')</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                        </div>
                    </form>
                    <div id="modalContent"></div>
                </div>
            </div>
        </div>