<div class="modal-dialog modal-lg" id="edit_document_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-edit"></i> @lang('followup::lang.edit_document')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <section class="content">

                        {!! Form::open([
                            'url' => action('Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController@update',$document->id),
                            'method' => 'put',
                            'id' => 'doc_add_form',
                        ]) !!}

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('name_ar', __('followup::lang.doc_name_ar') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name_ar',$sales_projects , [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('followup::lang.doc_name_ar'),
                                        'id' => 'name_ar',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('name_en', __('followup::lang.doc_name_en') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::text('name_en', $document->name_en, [
                                        'class' => 'form-control',
                                        'placeholder' => __('followup::lang.doc_name_en'),
                                        'id' => 'name_en',
                                    ]) !!}
                                </div>
                            </div>
                        </div>



                        <div class="row" style="margin-top: 220px;">
                            <div class="col-sm-12" style="display: flex;justify-content: center;">
                                <button type="submit" style="width:50%; border-radius: 28px;"
                                    class="btn btn-primary pull-right btn-flat ">@lang('messages.save')</button>
                            </div>
                        </div>


                        {!! Form::close() !!}
                    </section>


                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
{{-- <script>
    $(document).ready(function() {
   

    });
</script> --}}
