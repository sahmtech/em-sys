
        <div class="modal-dialog" role="document">
                    <div class="modal-content">

            {!! Form::open(['route' => ['updateInsurance', $insurance->id], 'method' => 'post', 'id' => 'edit_travel_categorie_form']) !!}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.edit_Insurance')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee', __('essentials::lang.employee') . ':') !!}
                                    {!! Form::select('employee', $users, $insurance->employee_id, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.select_employee'),
                                        'required',
                                        'id' => 'employeeSelect',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('insurance_class', __('essentials::lang.insurance_class') . ':') !!}
                                    {{--   {!! Form::select('insurance_class', $insurance_classes, $insurance->insurance_classes_id, [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'placeholder' => __('essentials::lang.insurance_class'),
                                        'required',
                                        'id' => 'classSelect',
                                    ]) !!}--}}
                                  
                                    {!! Form::select('insurance_class',
                                        ['VIP+'=>'VIP+',
                                        'VIP'=>'VIP',
                                        'A+'=>'A+',
                                        'A'=>'A',
                                        'B+'=>'B+',
                                        'B'=>'B',
                                        'C+'=>'C+',
                                        'C'=>'C',
                                        'CR+'=>'CR+',
                                        'CR'=>'CR',
                                        'VVIP'=>"VVIP",
                                        'A+S'=>'A+S',
                                        'VIP+S'=>'VIP+S',
                                        'C6'=>'C6',
                                        'C4'=>'C4',
                                        'C6S'=>'C6S',
                                        
                                        ],  null,
                                        
                                        ['class' => 'form-control',
                                         'required',
                                        'id' => 'classSelect']) !!}
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
          