<div class="modal-dialog no-print" role="document">
    {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\SettingsController@saveMap'), 'method' => 'POST', 'id' => 'save_accounting_map' ]) !!}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalTitle">
                            @if($elem->sub_type == 'sell')
                                إعدادات الترحيل الآلي لمدفوعات المبيعات {{' '.$elem->method.' '}}
{{--                            @elseif(in_array($type, ['sell_payment', 'purchase_payment']))--}}
{{--                                {{$transaction_payment->payment_ref_no}}--}}
{{--                            @elseif($type == 'purchase')--}}
{{--                                {{$transaction->ref_no}}--}}
                            @endif
                        </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">

                    <table class="table table-bordered table-striped hide-footer" id="auto_mapping_form">
                        <thead>
                        <tr>
                            <th class="col-md-1">#</th>
                            <th class="col-md-5">@lang( 'accounting::lang.account' )</th>
                            <th class="col-md-3">@lang( 'accounting::lang.type' )</th>
                        </tr>
                        </thead>
                        <tbody>
                        <input type="hidden" name="id" value="{{$elem->id}}">
                        @foreach($existing_payment_deposit as $key => $pay)
                                <?php
                                $acc = \Modules\Accounting\Entities\AccountingAccount::query()->findOrFail($pay->accounting_account_id);
                                ?>
                            <tr>
                                <td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer"
                                       data-id="{{$key}}"></i></td>
                                <td>
                                    {!! Form::select('account_id[' . $key . ']',  [$acc->id => $acc->name], $acc->id?? null,
                                                ['class' => 'form-control accounts-dropdown account_id',
                                                'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                </td>
                                <td>
                                    <label class="radio-inline">
                                        <input value="credit" type="radio" name="type[{{$key}}]"
                                                {{$pay->type == 'credit'? 'checked' : ''}}
                                        >
                                        @lang( 'accounting::lang.credit' )
                                    </label>
                                    <label class="radio-inline">
                                        <input value="debit" type="radio" name="type[{{$key}}]"
                                                {{$pay->type == 'debit'? 'checked' : ''}}
                                        >
                                        @lang( 'accounting::lang.debit' )
                                    </label>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>

                        {{--                        <tfoot>--}}
                        {{--                        <tr>--}}
                        {{--                            <th></th>--}}
                        {{--                            <th class="text-center">@lang( 'accounting::lang.total' )</th>--}}
                        {{--                            <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>--}}
                        {{--                            <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>--}}
                        {{--                        </tr>--}}
                        {{--                        </tfoot>--}}
                    </table>

                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
        </div>

        {!! Form::close() !!}
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->


