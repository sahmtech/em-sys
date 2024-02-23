  <div class="modal fade" id="editEmpCompanyModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  
                    {!! Form::open(['route' => ['update.EmploymentCompanies', 'empCompanyId'], 'method' => 'post', 'id' => 'edit_employment_company_form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.edit_empCompany')</h4>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="empCompanyIdInput" name="empCompanyIdInput">
                        <div class="row">
                            <div class="col-md-4 contact_type_div">
                                <div class="form-group">
                                    {!! Form::label('Office_name', __('internationalrelations::lang.Office_name') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-user"></i>
                                        </span>
                                        {!! Form::text('Office_name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.Office_name'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('country', __('internationalrelations::lang.country') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::select('country', $countries, null, [
                                            'id' => 'country',
                                            'style' => 'height:40px',
                                            'class' => 'form-control',
                                            'placeholder' => __('essentials::lang.country'),
                                            'required',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('nationality', __('internationalrelations::lang.nationality') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::select('nationalities[]', $nationalities, null, [
                                          
                                            'multiple',
                                            'style' => 'width: 230px; height: 40px;',
                                            'class' => 'form-control select2',
                                            'placeholder' => __('internationalrelations::lang.nationality'),
                                           
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('Office_representative', __('internationalrelations::lang.Office_representative') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.Office_representative'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('mobile', __('internationalrelations::lang.mobile') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('mobile', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.mobile'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('email', __('internationalrelations::lang.email') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('email', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.email'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('Evaluation', __('internationalrelations::lang.Evaluation') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>

                                        <select class="form-control select2" name="evaluation" required id="evaluation"
                                            style="width: 100%;">

                                            <option value="good">@lang('internationalrelations::lang.good')</option>
                                            <option value="bad">@lang('internationalrelations::lang.bad')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <div class="form-group">
                                    {!! Form::label('landing', __('internationalrelations::lang.landing') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-briefcase"></i>
                                        </span>
                                        {!! Form::text('landline', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('internationalrelations::lang.landing'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>


                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>