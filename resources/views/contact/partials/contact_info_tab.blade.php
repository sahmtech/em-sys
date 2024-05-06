<span id="view_contact_page"></span>
<div class="row">
    <div class="col-md-12">
        <div class="col-sm-3">
            @include('contact.contact_basic_info')
        </div>
        <div class="col-sm-9 mt-56">
            @include('contact.contact_more_info')
        </div>
        @if ($contact->type != 'customer')
            <div class="col-sm-3 mt-5">
                @include('contact.contact_tax_info')
            </div>
            @else
            <div class="col-sm-3 mt-5"></div>
        @endif
       
        <div class="col-sm-9 mt-5">
            <div class="col-md-12">
                <strong>
                    <h4>@lang('sales::lang.Contract_follower_details'):</h4>
                </strong>
                <br>
                <div class="col-md-4">

                    <p><strong>@lang('sales::lang.first_name_cf'):</strong>
                        @if (!empty($contactFollower->first_name))
                            {{ $contactFollower->first_name }}
                        @endif
                    </p>


                    <p><strong>@lang('sales::lang.last_name_cf'):</strong>
                        @if (!empty($contactFollower->last_name))
                            {{ $contactFollower->last_name }}
                        @endif
                    </p>


                </div>
                <div class="col-md-4">
                    <p><strong>@lang('sales::lang.english_name_cf'):</strong>
                        @if (!empty($contactFollower->english_name))
                            {{ $contactSigners->english_name }}
                        @endif
                    </p>

                    <p><strong>@lang('sales::lang.email_cf'):</strong>
                        @if (!empty($contactFollower->email))
                            {{ $contactSigners->email }}
                        @endif
                    </p>


                </div>
            </div>



        </div>

        {{--
        <div class="col-sm-3 mt-56">
            @include('contact.contact_payment_info') 
        </div>
        @if ($contact->type == 'customer' || $contact->type == 'both')
            <div class="col-sm-3 @if ($contact->type != 'both') mt-56 @endif">
                <strong>@lang('lang_v1.total_sell_return')</strong>
                <p class="text-muted">
                    <span class="display_currency" data-currency_symbol="true">
                    {{ $contact->total_sell_return }}</span>
                </p>
                <strong>@lang('lang_v1.total_sell_return_due')</strong>
                <p class="text-muted">
                    <span class="display_currency" data-currency_symbol="true">
                    {{ $contact->total_sell_return -  $contact->total_sell_return_paid }}</span>
                </p>
            </div>
        @endif
        --}}

        @if ($contact->type == 'supplier' || $contact->type == 'both')
            <div class="clearfix"></div>
            <div class="col-sm-12">
                @if ($contact->total_purchase - $contact->purchase_paid > 0)
                    <a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, 'getPayContactDue'], [$contact->id]) }}?type=purchase"
                        class="pay_purchase_due btn btn-primary btn-sm pull-right"><i class="fas fa-money-bill-alt"
                            aria-hidden="true"></i> @lang('contact.pay_due_amount')</a>
                @endif
            </div>
        @endif
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="modal"
                data-target="#add_discount_modal">@lang('lang_v1.add_discount')</button>
        </div>
    </div>
</div>
