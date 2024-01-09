<div class="modal-dialog modal-lg" id="edit_insurance_model" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('housingmovements::lang.edit_insurance')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action('\Modules\Essentials\Http\Controllers\CarInsuranceController@update', $insurance->id),
                            'method' => 'put',
                            'id' => 'carType_add_form',
                        ]) !!}



                        <div class="row">

                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_company_id', __('housingmovements::lang.insurance_company_id') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    <select class="form-control" id="insurance_company_id" name="insurance_company_id"
                                        style="padding: 2px;">
                                        <option value="">@lang('messages.please_select')</option>

                                        @foreach ($insurance_companies as $insurance_company)
                                            <option value="{{ $insurance_company->id }}"
                                                @if ($insurance_company->id == $insurance->insurance_company_id) selected @endif>
                                                {{ $insurance_company->supplier_business_name }}</option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_start_Date', __('housingmovements::lang.insurance_start_Date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date(
                                        'insurance_start_Date',
                                        $insurance->insurance_start_Date ? $insurance->insurance_start_Date : '',
                                        [
                                            'class' => 'form-control',
                                    
                                            'placeholder' => __('housingmovements::lang.insurance_start_Date'),
                                            'id' => 'insurance_start_Date',
                                        ],
                                    ) !!}

                                </div>
                            </div>
                            <div class="col-sm-6">

                                <div class="form-group">
                                    {!! Form::label('insurance_end_date', __('housingmovements::lang.insurance_end_date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>

                                    {!! Form::date('insurance_end_date', $insurance->insurance_end_date ? $insurance->insurance_end_date : '', [
                                        'class' => 'form-control',
                                    
                                        'placeholder' => __('housingmovements::lang.insurance_end_date'),
                                        'id' => 'insurance_end_date',
                                    ]) !!}
                                </div>
                            </div>





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


        $('#edit_car_model').on('shown.bs.modal', function(e) {
            $('#worker_select').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });

            $('#car_type_id_select').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });

            $('#manufacturing_year').select2({
                dropdownParent: $(
                    '#edit_car_model'),
                width: '100%',
            });
        });

    });
</script>
