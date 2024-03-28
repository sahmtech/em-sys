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
                    <b>{{ __('sales::lang.worker_order_number') }} :</b></b> {{ $query->order_no }}<br>
                    <b>{{ __('sales::lang.required_date') }} :</b></b> {{ $query->date }}<br>


                </div>

                <div class="col-sm-2 col-sm-4">
                    <b>{{ __('sales::lang.final_total') }} :</b></b> {{ $query->salary * $query->total_quantity }}<br>
                    <b>{{ __('sales::lang.Status') }} :</b>
                    {{ __('sales::lang.' . $query->status) }}
                    <br>


                </div>
            </div>
            <br>
            <h4>{{ __('sales::lang.workers') }}:</h4>
            <div class="row">

                <div class="col-sm-16">


                    {!! Form::open([
                        'url' => action([
                            \Modules\InternationalRelations\Http\Controllers\OrderRequestController::class,
                            'saveUbnSupportedRequest',
                        ]),
                        'method' => 'post',
                        'id' => 'saveDataForm',
                        'enctype' => 'multipart/form-data',
                    ]) !!}

                    <input type="hidden" name="order_id" value="{{ $id }}">

                    <div class="table-responsive">
                        <table
                            class="table @if (!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif"
                            @if (!empty($for_pdf)) style="width: 100%;" @endif id="products_table">

                            <tr @if (empty($for_ledger)) class="bg-green" @endif>

                                <th>#</th>
                                <th>{{ __('sales::lang.quantity') }}</th>
                                <th>{{ __('sales::lang.operation_remaining_quantity') }}</th>
                                <th>{{ __('sales::lang.profession_name') }}</th>
                                <th>{{ __('sales::lang.specialization_name') }}</th>
                                <th>{{ __('sales::lang.nationality_name') }}</th>
                                {{-- <th>{{ __('sales::lang.gender') }}</th> --}}
                                <th>{{ __('sales::lang.salary') }}</th>
                                {{-- <th>{{ __('sales::lang.additional_allwances') }}</th> --}}
                                <th>{{ __('internationalrelations::lang.agency_name') }}</th>
                                <th>{{ __('internationalrelations::lang.target_quantity') }}</th>
                                <th>{{ __('internationalrelations::lang.attachments') }}</th>

                            </tr>


                            <tr>

                                <td>
                                    <input type="hidden" name="worker_order_id" value="{{ $query->id }}">
                                </td>
                                <td>{{ $query->total_quantity }}</td>
                                <td>{{ $query->remaining_quantity_for_delegation }}</td>
                                <td>{{ $professions[$query->profession_id] }}</td>
                                <td>{{ $specializations[$query->specialization_id] }}</td>
                                <td>{{ $nationalities[$query->nationality_id] }}</td>
                                {{-- <td> {{ __('sales::lang.' . $sell_line['service']['gender']) }}</td> --}}

                                <td>{{ $query->salary }}</td>


                                <td>

                                    <select name="agency_name[]" class="form-control"
                                        style="width: 200px; height: 40px;">
                                        <option value="" disabled selected>
                                            @lang('internationalrelations::lang.select_agency')
                                        </option>
                                        @foreach ($agencies as $agency)
                                            <option value="{{ $agency->id }}">
                                                {{ $agency->supplier_business_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    {!! Form::text('target_quantity[]', null, [
                                        'class' => 'form-control',
                                        'style' => 'width: 110px;',
                                        'placeholder' => __('internationalrelations::lang.target_quantity'),
                                    ]) !!}
                                </td>

                                <td>
                                    {!! Form::file('attachments[]', [
                                        'class' => 'form-control',
                                        'style' => 'width: 110px',
                                        'placeholder' => __('essentials::lang.file'),
                                        'required',
                                    ]) !!}
                                </td>

                                <td>
                                    <button type="button" class="btn btn-success add_row">Add Row</button>
                                </td>
                            </tr>


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

        function saveData() {

            var formData = new FormData($('#saveDataForm')[0]);

            $.each(formData.getAll('agency_name[]'), function(index, value) {
                formData.append('data_array[' + index + '][worker_order_id]', formData.getAll(
                    'worker_order_id')[
                    index]);
                formData.append('data_array[' + index + '][agency_name]', value);
                formData.append('data_array[' + index + '][target_quantity]', formData.getAll(
                    'target_quantity[]')[index]);
                formData.append('data_array[' + index + '][attachment]', formData.getAll(
                    'attachments[]')[index]);
            });
            if (!validateTargetQuantities()) {
                return false;
            }
            $.ajax({
                url: '{{ route('saveUbnSupportedRequest') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        console.log(response);
                        toastr.success(response.message);
                        location.reload();

                    } else {

                        console.log('Server response indicated failure: ' + response.message);
                        toastr.error(response.message);
                        $('#error-message').text(response.message).css('color', 'red');
                    }
                }

            });

        }



        $('#saveButton').on('click', saveData);

        function addRow() {

            var clonedRow = $(this).closest('tr').clone();
            clonedRow.find('input:text').val('');
            clonedRow.find('select').val('');

            clonedRow.find('.add_row').removeClass('add_row btn-success').addClass('remove_row btn-danger')
                .text('Remove');
            $('#products_table tbody').append(clonedRow);
        }

        function removeRow() {
            $(this).closest('tr').remove();
        }

        $('.add_row').on('click', addRow);

        $(document).on('click', '.remove_row', removeRow);

        function validateTargetQuantities() {
            var quantityMap = {};


            $('#products_table tbody tr').each(function() {
                var quantity = $(this).find('td:nth-child(3)').text();
                var serviceId = $(this).find('input[name="product_id"]').val();
                var targetQuantity = parseFloat($(this).find('input[name="target_quantity[]"]')
                    .val()) || 0;

                var key = serviceId + '_' + quantity;

                if (!quantityMap.hasOwnProperty(key)) {
                    quantityMap[key] = 0;
                }

                quantityMap[key] += targetQuantity;
            });


            for (var key in quantityMap) {
                if (quantityMap.hasOwnProperty(key)) {
                    var parts = key.split('_');
                    var serviceId = parts[0];
                    var quantity = parts[1];

                    if (quantityMap[key] > quantity) {
                        alert('{{ __('internationalrelations::lang.Target_quantity_should_be_equal_to_or_less_than') }}' +
                            ' ' +
                            quantity);

                        return false;
                    }
                }
            }

            return true;
        }
    });
</script>
