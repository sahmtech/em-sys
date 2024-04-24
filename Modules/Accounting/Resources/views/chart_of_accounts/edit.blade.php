<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action('\Modules\Accounting\Http\Controllers\CoaController@update', $account->id),
            'method' => 'put',
            'id' => 'create_client_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"> <i class="fas fa-edit"></i> @lang('accounting::lang.edit_account')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12">

                        <div class="form-group">
                            {!! Form::label('name', __('user.name') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                            {!! Form::text('name', $account->name, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('user.name'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('account_type', __('accounting::lang.account_type') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                            <select class="form-control" name="account_type" id="account_type" style="padding: 2px"
                                required>
                                @foreach ($account_types as $key => $value)
                                    <option value="{{ $key }}"
                                        @if ($account->account_type == $key) selected @endif
                                        style="font-size: 14px !important;
                            line-height: 1.42857143;
                            color: #555;">
                                        {{ $value }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('account_category', __('accounting::lang.account_category') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                            <select class="form-control" name="account_category" id="account_category"
                                style="padding: 2px" required>
                                @foreach ($account_category as $key => $value)
                                    <option value="{{ $key }}"
                                        @if ($account->account_category == $key) selected @endif
                                        style="font-size: 14px !important;
                        line-height: 1.42857143;
                        color: #555;">
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                </div>
            </div>

        </div>

        <div class="modal-footer" style="justify-content: flex-end">
            <button type="submit" class="btn btn-primary"
                style="border-radius: 5px;
            min-width: 25%;">@lang('messages.save')</button>
            {{-- <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button> --}}
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
