<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"> @lang('internationalrelations::lang.add_delegation')
            </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-3 col-sm-4">
                    <b>{{ __('sales::lang.transaction_number') }} :</b></b> {{ $query->ref_no }}<br>
                    <b>{{ __('sales::lang.transaction_date') }} :</b></b> {{ $query->transaction_date }}<br>
                    <b>{{ __('sales::lang.customer_name') }} :</b></b> {{ $query->sale_project->name }}<br>
                    <b>{{ __('sales::lang.contact_mobile') }} :</b></b> {{ $query->sale_project->phone_in_charge }}<br>
                </div>

                <div class="col-sm-2 col-sm-4">
                    <b>{{ __('sales::lang.final_total') }} :</b></b> {{ $query->final_total }}<br>
                    <b>{{ __('sales::lang.Status') }} :</b>
                    {{ __('sales::lang.' . $query->status) }}
                    <br>

                    <b>{{ __('sales::lang.down_payment') }} :</b></b> {{ $query->down_payment }}<br>
                    <b>{{ __('sales::lang.contract_form') }} :</b></b>
                    {{ __('sales::lang.' . $query->contract_form) }} <br>

                </div>
            </div>
            <div class="row">

                <div class="col-sm-12">
                    <h4>{{ __('sale.products') }}:</h4>

                    {!! Form::open([
                        'url' => action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'saveRequest']),
                        'method' => 'post',
                    ]) !!}
                    <input type="hidden" name="order_id" value="{{ $id }}">

                    <div class="table-responsive">
                        <table
                            class="table @if (!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif"
                            @if (!empty($for_pdf)) style="width: 100%;" @endif id="products_table">

                            <tr @if (empty($for_ledger)) class="bg-green" @endif>

                                <th style="width: 30px;">#</th>
                                <th style="width: 80px;">{{ __('sales::lang.quantity') }}</th>
                                <th style="width: 120px;">{{ __('sales::lang.profession_name') }}</th>
                                <th style="width: 120px;">{{ __('sales::lang.specialization_name') }}</th>
                                <th style="width: 120px;">{{ __('sales::lang.nationality_name') }}</th>
                                <th style="width: 80px;">{{ __('sales::lang.gender') }}</th>
                                <th style="width: 100px;">{{ __('sales::lang.service_price') }}</th>
                                <th style="width: 150px;">{{ __('sales::lang.additional_allwances') }}</th>
                                <th style="width: 150px;">{{ __('internationalrelations::lang.agency_name') }}</th>
                                <th style="width: 120px;">{{ __('internationalrelations::lang.target_quantity') }}</th>
                            </tr>

                            @foreach ($query->sell_lines as $sell_line)
                                <tr>

                                    <td>
                                        <input type="hidden" name="product_id" value="{{ $sell_line->service_id }}">
                                    </td>
                                    <td>{{ $sell_line->quantity }}</td>
                                    <td>{{ $sell_line['service']['profession']['name'] }}</td>
                                    <td>{{ $sell_line['service']['specialization']['name'] }}</td>
                                    <td>{{ $sell_line['service']['nationality']['nationality'] }}</td>
                                    <td> {{ __('sales::lang.' . $sell_line['service']['gender']) }}</td>

                                    <td>{{ $sell_line['service']['service_price'] }}</td>
                                   
                                    <td style="width: 200px;">
                                        @if (!empty($sell_line->additional_allwances))
                                            <ul>
                                                @foreach (json_decode($sell_line->additional_allwances) as $allwance)
                                                    @if (is_object($allwance) && property_exists($allwance, 'salaryType') && property_exists($allwance, 'amount'))
                                                    <li>
                                                        {{ __('sales::lang.' . $allwance->salaryType) }}:
                                                        @if ($allwance->amount == 0)
                                                            {{ __('sales::lang.insured_by_the_other') }}
                                                        @else
                                                            {{ $allwance->amount }}
                                                        @endif
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif



                                    </td>

                                    <td>
                                       
                                        <select name="agency_name" class="form-control" style="width: 200px; height: 40px;">
                                            <option value="" disabled selected>
                                                @lang('internationalrelations::lang.select_agency')
                                            </option>
                                            @foreach ($agencies as $agency)
                                            
                                                <option value="{{ $agency->id }}">
                                                    {{ $agency->supplier_business_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>

                                        {!! Form::text('target_quantity', null, [
                                            'class' => 'form-control',
                                            'style' => 'width: 120px;',
                                            'placeholder' => __('internationalrelations::lang.target_quantity'),
                                        ]) !!}
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-success add_row">Add Row</button>
                                    </td>
                                </tr>
                            @endforeach

                        </table>
                    </div>


                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="button" id="saveButton"
                                class="btn btn-primary btn-big">@lang('messages.save')</button>

                        </div>
                    </div>

                </div>
                {!! Form::close() !!}
            </div>


        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#saveButton').click(function() {
            var data = [];
            var data2 = [];

            data.push({
                order_id: $('input[name="order_id"]').val(),
            });
            $('table tbody tr:gt(0)').each(function() {
                var product_id = $(this).find('input[name="product_id"]').val();
                var agency_id = $(this).find('select[name="agency_name"]').val();
                var target_quantity = $(this).find('input[name="target_quantity"]').val();


                if (product_id !== undefined && agency_id !== undefined && target_quantity !==
                    undefined) {
                    data2.push({
                        product_id: product_id,
                        agency_id: agency_id,
                        target_quantity: target_quantity
                    });
                }

            });

            console.log(data);
            console.log(data2);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/ir/save-data',
                method: 'POST',
                data: {
                    data: data,
                    data2: data2
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();

                    } else {

                        console.log('Server response indicated failure: ' + response
                            .message);
                        toastr.error(response.message);
                        $('#error-message').text(response.message).css('color', 'red');
                    }
                }

            });
        });

        function addRow() {

            var clonedRow = $(this).closest('tr').clone();
            clonedRow.find('input:text').val('');
            clonedRow.find('select').val('');
            // clonedRow.find('.add_row').removeClass('add_row').addClass('remove_row').text('Remove');
            clonedRow.find('.add_row').removeClass('add_row btn-success').addClass('remove_row btn-danger')
                .text('Remove');
            $('#products_table tbody').append(clonedRow);
        }

        function removeRow() {
            $(this).closest('tr').remove();
        }

        $('.add_row').on('click', addRow);

        $(document).on('click', '.remove_row', removeRow);



    });
</script>
{{-- //    $('.add-row').on('click', function () {
    //         var clonedRow = $(this).closest('tr').clone();
    //         clonedRow.find('input:text').val('');
    //         clonedRow.find('select').val('');
    //         $(this).closest('tr').after(clonedRow);
    //     }); --}}
