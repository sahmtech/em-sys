<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle"> @lang('sales::lang.offer_price_details')
            </h4>
        </div>
        <div class="modal-body">



            <div class="row">
                <div class="col-sm-3 col-sm-4">
                    <b>{{ __('sales::lang.transaction_number') }} :</b></b> {{ $query->ref_no }}<br>
                    <b>{{ __('sales::lang.transaction_date') }} :</b></b> {{ $query->transaction_date }}<br>
                    <b>{{ __('sales::lang.customer_name') }} :</b></b> {{ $query->contact->supplier_business_name }}<br>
                    <b>{{ __('sales::lang.contact_mobile') }} :</b></b> {{ $query->contact->mobile }}<br>
                </div>

                <div class="col-sm-2 col-sm-4">
                    <b>{{ __('sales::lang.final_total_for_var_costs') }} :</b></b> {{ $query->final_total }}<br>
                    <b>{{ __('sales::lang.Status') }} :</b>
                    {{ __('sales::lang.' . $query->status) }}
                    <br>
               
                    <b>{{ __('sales::lang.down_payment') }} :</b></b> {{ $query->down_payment }}<br>
                    <b>{{ __('sales::lang.contract_form') }} :</b></b>
                    {{ __('sales::lang.' . $query->contract_form) }} <br>
                  
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <h4>{{ __('sale.products') }}:</h4>
                </div>

                <div class="col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table
                            class="table @if (!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif"
                            @if (!empty($for_pdf)) style="width: 100%;" @endif>
                            <tr @if (empty($for_ledger)) class="bg-green" @endif>


                                <th>{{ __('sales::lang.quantity') }}</th>
                                <th>{{ __('sales::lang.additional_allwances') }}</th>
                                <th>{{ __('sales::lang.gender') }}</th>
                                <th>{{ __('sales::lang.service_price') }}</th>
                                <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
                                <th>{{ __('sales::lang.profession_name') }}</th>
                                <th>{{ __('sales::lang.specialization_name') }}</th>

                            </tr>
                            @foreach ($query->sell_lines as $sell_line)
                                <tr>
                                    <td>{{ $sell_line->quantity }}</td>
                                 
                                    <td>
                                        @if (!empty($sell_line->additional_allwances) &&  !empty($allwance->salaryType))
                                            <ul>
                                                @foreach (json_decode($sell_line->additional_allwances) as $allwance)
                                                    @if (is_object($allwance) && property_exists($allwance, 'salaryType') && property_exists($allwance, 'amount'))
                                                        <li>
                                                            {{ __('sales::lang.' . $allwance->salaryType) }}:
                                                            {{ $allwance->amount }}

                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                       
                                        @else 
                                        {{ __('sales::lang.not_exists') }}
                                        @endif



                                    </td>
                                    <td> {{ __('sales::lang.' . $sell_line['service']['gender']) }}</td>
                                    <td>{{ $sell_line['service']['service_price'] }}</td>
                                    <td>{{ $sell_line['service']['monthly_cost_for_one'] }}</td>
                                    <td>{{ $sell_line['service']['profession']['name'] }}</td>
                                    <td>{{ $sell_line['service']['specialization']['name'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <script type="text/javascript">
            $(document).ready(function() {
                var element = $('div.modal-xl');
                __currency_convert_recursively(element);
            });
        </script>