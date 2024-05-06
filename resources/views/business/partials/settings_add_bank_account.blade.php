<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">



        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('lang_v1.add_BankAccounts')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">

                        {!! Form::open([
                            'url' => action([\App\Http\Controllers\BankAccountsController::class, 'store']),
                            'method' => 'post',
                            'id' => 'journal_add_form',
                        ]) !!}

                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {!! Form::label('bnak_id', __('lang_v1.bank_name') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        
                                         {!! Form::select('bnak_id', $banks,null, [
                                            'class' => 'form-control',
                                            'required',
                                            // 'placeholder' => __('lang_v1.bank_name'),
                                            'style'=>'display: flex !important;width: 100% !important;',
                                            'id' => 'bnak_id',
                                        ]) !!}
                                    </div>
                                </div>
                                

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {!! Form::label('account_number', __('lang_v1.account_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::text('account_number', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('lang_v1.account_number'),
                                            'id' => 'account_number',
                                        ]) !!}
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {!! Form::label('ibn', __('lang_v1.ibn') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::text('ibn', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('lang_v1.ibn'),
                                            'id' => 'ibn',
                                        ]) !!}
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {!! Form::label('account_name', __('lang_v1.account_name_') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::text('account_name', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('lang_v1.account_name_'),
                                            'id' => 'account_name',
                                        ]) !!}
                                    </div>
                                </div>
                               
                            </div>
                        @endcomponent

                        <div class="row">
                            <div class="col-sm-12" style="display: flex;
                                justify-content: center;">
                                <button type="submit" style="    width: 50%;
                                    border-radius: 28px;"
                                    class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </section>


                </div>

            </div>
        </div>

    </div> <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
