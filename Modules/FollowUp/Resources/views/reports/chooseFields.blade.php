{{-- <div class="modal fade" id="chooseFields_projectsworker" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content"> --}}
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">


        {!! Form::open(['method' => 'post', 'id' => 'chooseFields_projectsworker']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fas fa-plus"></i> @lang('followup::lang.choose_fields')
            </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @php
                    $default_location = [__('followup::lang.name'), __('followup::lang.eqama'), __('followup::lang.project_name'), __('followup::lang.nationality'), __('followup::lang.eqama_end_date'), __('followup::lang.admissions_date'), __('followup::lang.contract_end_date')];
                    if (count($default_location) == 1) {
                        $default_location = array_key_first($default_location->toArray());
                    }
                @endphp
               
                    <div class="form-group">
                        {{-- {!! Form::label('choose_fields', __('followup::lang.choose_fields') . ':') !!} --}}
                        {!! Form::select('product_locations[]', $default_location, $default_location, [
                            'class' => 'form-control select2',
                            'multiple',
                            'id' => 'product_locations',
                        ]) !!}
                    </div>
              


                <div class="modal-footer" style="justify-content: flex-end">
                    <button type="submit" class="btn btn-primary"
                        style="border-radius: 5px;min-width: 25%;">@lang('messages.add')</button>
                    {{-- <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button> --}}
                </div>

                {!! Form::close() !!}
            </div>
        </div>

        {{-- </div> --}}
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
