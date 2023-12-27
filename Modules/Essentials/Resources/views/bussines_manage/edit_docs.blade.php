<div class="modal-dialog modal-lg" id="edit_docs_model" role="document">


    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('essentials::lang.edit_docs')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <section class="content">

                    {!! Form::open([
                        'url' => action('\App\Http\Controllers\BusinessDocumentController@update', $busines->id),
                        'method' => 'put',
                       
                    ]) !!}

                    <input type="hidden" name="business_id" value="{{ $busines->business_id }}">

                    <div class="form-group col-sm-6">
                        {!! Form::label('licence_type', __('essentials::lang.licence_type') . ':*') !!}
                        {!! Form::select(
                            'licence_type',
                            [
                                'COMMERCIALREGISTER' => __('essentials::lang.COMMERCIALREGISTER'),
                                'Gosi' => __('essentials::lang.Gosi'),
                                'Zatca' => __('essentials::lang.Zatca'),
                                'Chamber' => __('essentials::lang.Chamber'),
                                'Balady' => __('essentials::lang.Balady'),
                                'saudizationCertificate' => __('essentials::lang.saudizationCertificate'),
                                'VAT' => __('essentials::lang.VAT'),
                        
                                'memorandum_of_association' => __('essentials::lang.memorandum_of_association'),
                                'national_address' => __('essentials::lang.national_address'),
                                'activity' => __('essentials::lang.activity'),
                            ],
                              $busines->licence_type,
                            [
                                'class' => 'form-control',
                                'style' => 'height:40px',
                                'id' => 'licence_type_edit',
                                'placeholder' => __('essentials::lang.select_licence_type'),
                                'required',
                            ],
                        ) !!}
                    </div>

                    <div class="form-group col-sm-6" id="unified_number_edit" style="display: none;">
                        {!! Form::label('unified_number', __('essentials::lang.unified_number') . ':*') !!}
                        {!! Form::number('unified_number', $busines->unified_number, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.unified_number'),
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-6" id="national_address_edit" style="display: none;">
                        {!! Form::label('national_address', __('essentials::lang.national_address') . ':*') !!}
                        {!! Form::text('national_address', $busines->national_address, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.national_address'),
                        ]) !!}
                    </div>


                    <div class="form-group col-sm-6" id="capital_edit" style="display: none;">
                        {!! Form::label('capital', __('essentials::lang.capital') . ':*') !!}
                        {!! Form::text('capital', $busines->capital, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.capital'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-8" id ="licence_number_edit">
                        {!! Form::label('licence_number', __('essentials::lang.licence_number') . ':*') !!}
                        {!! Form::text('licence_number', $busines->licence_number, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.licence_number'),
                        ]) !!}
                    </div>


                    <div class="form-group col-md-8" id="licence_date_edit">
                        {!! Form::label('licence_date', __('essentials::lang.licence_date') . ':*') !!}
                        {!! Form::date('licence_date', $busines->licence_date, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.licence_date'),
                        ]) !!}
                    </div>


                    <div class="form-group col-md-6" id="renew_date_edit">
                        {!! Form::label('renew_date', __('essentials::lang.renew_date') . ':*') !!}
                        {!! Form::date('renew_date', $busines->renew_date, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.renew_date'),
                        ]) !!}
                    </div>


                    <div class="form-group col-md-6" id="expiration_date_edit">
                        {!! Form::label('expiration_date', __('essentials::lang.expiration_date') . ':') !!}
                        {!! Form::date('expiration_date', $busines->expiration_date, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.expiration_date'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6" id="issuing_location_edit">
                        {!! Form::label('issuing_location', __('essentials::lang.issuing_location') . ':') !!}
                        {!! Form::text('issuing_location', $busines->issuing_location, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.issuing_location'),
                        ]) !!}
                    </div>

                    <div class="form-group col-md-6" id="#details_edit">
                        {!! Form::label('details', __('essentials::lang.contry_details') . ':*') !!}
                        {!! Form::textarea('details', $busines->details, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('essentials::lang.contry_details'),
                            'rows' => 2,
                        ]) !!}
                    </div>
                    
                    <div class="form-group col-md-6" id="file_edit">
                        {!! Form::label('file', __('essentials::lang.file') . ':*') !!}
                        {!! Form::file('file', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('essentials::lang.file'),
                        ]) !!}

                    </div>
                    @if (!empty($busines->path_file))
                        {{-- {!! Form::label('file', __('essentials::lang.file') . ':') !!} --}}

                        <div class="form-group col-md-6">
                            <a href="{{ env('APP_URL') }}/uploads/{{ $busines->path_file }} "
                                class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i>
                                @lang('essentials::lang.doc_view')
                            </a>
                        </div>
                    @endif

                    <div class="clearfix"></div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </section>


            </div>
        </div>

    </div>
</div> <!-- /.modal-content -->

