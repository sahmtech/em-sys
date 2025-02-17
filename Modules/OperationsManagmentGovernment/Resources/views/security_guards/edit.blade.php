@extends('layouts.app')
@section('title', __('operationsmanagmentgovernment::lang.edit_security_guard_data'))

@section('content')

<section class="content-header">
    <h1>@lang('operationsmanagmentgovernment::lang.edit_security_guard_data')</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body">
                    {!! Form::model($security_guard, [
                    'url' =>
                    action('Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController@update',$security_guard->id),
                    'method' => 'post',
                    'id' => 'doc_add_form',
                    'files' => true
                    ]) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('first_name', __('operationsmanagmentgovernment::lang.first_name')) !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('first_name', $security_guard->first_name ?? null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.first_name'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('mid_name', __('operationsmanagmentgovernment::lang.mid_name')) !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('mid_name', $security_guard->mid_name ?? null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.mid_name'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('last_name', __('operationsmanagmentgovernment::lang.last_name')) !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('last_name', $security_guard->last_name ?? null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.last_name'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('profession', __('operationsmanagmentgovernment::lang.profession') .
                                ':*') !!}
                                {!! Form::select('profession', $professions, $security_guard->custom_field_1 ?? null, [
                                'class' => 'form-control select2',
                                'id' => 'professionSelect',
                                'style' => 'height:40px',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.profession'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('fingerprint_no',
                                __('operationsmanagmentgovernment::lang.fingerprint_no')) !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('fingerprint_no', $security_guard->fingerprint_no ?? null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.fingerprint_no'),
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('id_proof_number',
                                __('operationsmanagmentgovernment::lang.id_proof_number')) !!}
                                <span class="text-danger">*</span>
                                {!! Form::text('id_proof_number', $security_guard->id_proof_number ?? null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('operationsmanagmentgovernment::lang.id_proof_number'),
                                ]) !!}
                            </div>
                        </div>




                    </div>



                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 5px;">
                                @lang('messages.save')
                            </button>
                        </div>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Dynamically add new fields
        $("#add-more").click(function () {
            let newField = `
                <div class="row single-field">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="name[]">@lang('followup::lang.doc_name') <span style="color: red; font-size:10px"> *</span></label>
                            <input type="text" name="name[]" class="form-control" required placeholder="@lang('followup::lang.doc_name')">
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attachment[]">@lang('request.attachment')</label>
                            <input type="file" name="attachment[]" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-field">  <i class="fa fa-trash"></i> @lang('messages.delete') </button>
                    </div>
                </div>
            `;
            $("#dynamic-fields").append(newField);
        });

        // Remove the field when clicked
        $(document).on("click", ".remove-field", function () {
            $(this).closest(".single-field").remove();
        });
    });

      // Show Toastr Messages
      function showToastrMessages() {
            // Success message from session
            @if(session('success'))
                toastr.success("{{ session('success') }}", 'Success');
            @endif
            
            // Error message from session
            @if(session('error'))
                toastr.error("{{ session('error') }}", 'Error');
            @endif

            // Displaying validation errors from Laravel
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}", 'Validation Error');
                @endforeach
            @endif
        }

        // Call the function to show Toastr messages on page load
        showToastrMessages();
</script>
@endsection