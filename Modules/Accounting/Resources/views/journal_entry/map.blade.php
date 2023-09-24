<div class="modal-dialog no-print" role="document">
    {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\JournalEntryController@saveMap'), 'method' => 'POST', 'id' => 'save_accounting_map' ]) !!}

    <input type="hidden" name="type" value="{{$type}}" id="transaction_type">
    @if(in_array($type, ['sell', 'purchase']))
        <input type="hidden" name="id" value="{{$transaction->id}}">
    @elseif(in_array($type, ['sell_payment', 'purchase_payment']))
        <input type="hidden" name="id" value="{{$transaction_payment->id}}">
    @endif
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">
                @if($type == 'sell')
                    {{$transaction->invoice_no.' - '.$transaction->final_total.' ريال سعودي '}}
                @elseif(in_array($type, ['sell_payment', 'purchase_payment']))
                    {{$transaction_payment->payment_ref_no}}
                @elseif($type == 'purchase')
                    {{$transaction->ref_no}}
                @endif
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">

                    <table class="table table-bordered table-striped hide-footer" id="journal_table_form">
                        <thead>
                        <tr>
                            <th class="col-md-1">#</th>
                            <th class="col-md-5">@lang( 'accounting::lang.account' )</th>
                            <th class="col-md-3">@lang( 'accounting::lang.credit' )</th>
                            <th class="col-md-3">@lang( 'accounting::lang.debit' )</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($existing_payment_deposit->first())
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
                                        {!! Form::text('credit[' . $key . ']', $pay->map_type == 'payment_account'? $pay->amount : null, ['class' => 'form-control input_number credit']); !!}
                                    </td>

                                    <td>
                                        {!! Form::text('debit[' . $key . ']', $pay->map_type == 'deposit_to'? $pay->amount : null, ['class' => 'form-control input_number debit']); !!}
                                    </td>

                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td><i class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="1"></i></td>
                                <td>
                                    {!! Form::select('account_id[' . 1 . ']', [], null,
                                                ['class' => 'form-control accounts-dropdown account_id',
                                                'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                </td>

                                <td>
                                    {!! Form::text('credit[' . 1 . ']', null, ['class' => 'form-control input_number credit']); !!}
                                </td>

                                <td>
                                    {!! Form::text('debit[' . 1 . ']', null, ['class' => 'form-control input_number debit']); !!}
                                </td>

                            </tr>
                        @endif
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


