<div class="modal-dialog modal-lg" id="add_document_model" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white" style="background-color: white">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: red;">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fas fa-plus"></i> @lang('operationsmanagmentgovernment::lang.add_security_guard')
            </h4>
        </div>

        <div class="modal-body">
            {!! Form::open([
            'url' => action('Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController@store'),
            'method' => 'post',
            'id' => 'doc_add_form',
            'files' => true
            ]) !!}




            <!-- Dynamic Fields for Document Name and Attachment -->
            <div id="dynamic-fields">
                <div class="field-group mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('first_name',
                                __('operationsmanagmentgovernment::lang.first_name'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.first_name'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                {!! Form::label('mid_name',
                                __('operationsmanagmentgovernment::lang.mid_name'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('mid_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.mid_name'),
                                ]) !!}
                            </div>



                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                {!! Form::label('last_name',
                                __('operationsmanagmentgovernment::lang.last_name'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('last_name', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.last_name'),
                                ]) !!}
                            </div>



                        </div>

                        {{-- fingerprint_no --}}
                        <div class="col-md-6">
                            <div class="form-group ">
                                {!! Form::label('fingerprint_no',
                                __('operationsmanagmentgovernment::lang.fingerprint_no'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('fingerprint_no', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.fingerprint_no'),
                                ]) !!}
                            </div>
                        </div>

                        {{-- id_proof_number --}}
                        <div class="col-md-6">
                            <div class="form-group ">
                                {!! Form::label('id_proof_number',
                                __('operationsmanagmentgovernment::lang.id_proof_number'))
                                !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('id_proof_number', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.id_proof_number'),
                                ]) !!}
                            </div>
                        </div>

                        {{-- profession --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('profession', __('operationsmanagmentgovernment::lang.profession') .
                                ':*') !!}
                                {!! Form::select('profession', $professions, null, [
                                'class' => 'form-control select2',

                                'id' => 'professionSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.profession'),
                                ]) !!}
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-primary btn-lg">
                    @lang('messages.save')
                </button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize select2 for profession
        $('#professionSelect').select2({
            placeholder: "{{ __('operationsmanagmentgovernment::lang.profession') }}",
            allowClear: true
        });

        // Initialize select2 for other fields
        $('#professionSelect').select2({
            dropdownParent: $('#add_document_model'),
            width: '100%',
        });

        $('#specializationSearch').select2({
            dropdownParent: $('#add_document_model'),
            width: '100%',
        });

     

       
    });
</script>


</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>