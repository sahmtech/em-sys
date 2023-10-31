<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle"> @lang('internationalrelations::lang.add_deleation') 
            </h4>
    </div>
            <div class="modal-body">

                

                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <h4>{{ __('sale.products') }}:</h4>
                        </div>
                        {!! Form::open(['url' => action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'saveRequest']), 'method' => 'post']) !!}
                        <div class="col-sm-12 col-xs-12">
                            <div class="table-responsive">
                            <table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
                                <tr @if(empty($for_ledger)) class="bg-green" @endif>
                                    
                                <th>#</th>
                                        <th>{{ __('sales::lang.profession_name') }}</th>
                                        <th>{{ __('sales::lang.specialization_name') }}</th>
                                        <th>{{ __('sales::lang.nationality_name') }}</th>
                                        <th>{{ __('sales::lang.gender') }}</th>
                                        <th>{{ __('sales::lang.service_price') }}</th>
                                        <th>{{ __('internationalrelations::lang.currecnt_quantity') }}</th>

                                        <th>{{ __('internationalrelations::lang.agency_name') }}</th>
                                        <th>{{ __('internationalrelations::lang.target_quantity') }}</th>
                                      
                                </tr>

                                @foreach($products as $product)
                                        <tr>
                                       
                                            <td>
                                                                    <input type="hidden" name="product_id" value="{{ $product->t_id }}">
                                                                </td>
                                            <td>
                                            {{ $product->profession_name }}
                                            </td>
                                            <td>
                                            {{ $product->specialization_name }}
                                            </td>
                                            <td>
                                            {{ $product->nationality_name }}
                                            </td>
                                            <td>
                                            {{ $product->gender }}
                                            </td>
                                            <td>
                                            {{ $product->service_price }}
                                            </td>
                                            <td>
                                            {{ $product->quantity }}
                                            </td>

                                        <td>
                                        <select name="agency_name" class="form-control">
                                                @foreach($agencies as $agency)
                                                    <option value="{{ $agency->id }}">{{ $agency->supplier_business_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                           
                                            {!! Form::text('target_quantity', null, ['class' => 'form-control', 'placeholder' => __('internationalrelations::lang.target_quantity')]); !!}
                                        </td>
                                            
                                        </tr>
                                        @endforeach
                              </table>
                            </div>

                            <!-- Add the "Save" button -->
                            <div class="row">
		
                                    <div class="col-sm-12 text-center">
                                        <button type="button" id="saveButton" class="btn btn-primary btn-big">@lang('messages.save')</button>
                                    
                                    </div>
                        	</div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                

            </div>

</div>
</div>
<!-- JavaScript code to handle the button click -->
<script>
$(document).ready(function() {
    $('#saveButton').click(function() {
        var data = [];

        // Select all table rows except the first one (header row)
        $('table tbody tr:gt(0)').each(function() {
            var product_id = $(this).find('input[name="product_id"]').val();
            var agency_id = $(this).find('select[name="agency_name"]').val();
            var target_quantity = $(this).find('input[name="target_quantity"]').val();

            data.push({
                product_id: product_id,
                agency_id: agency_id,
                target_quantity: target_quantity
            });
        });

        console.log(data);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/ir/save-data',
            method: 'POST', // Ensure this is set to POST
            data: {
                data: data
            },
            success: function(response) {
                // Handle success response here (e.g., show a success message)
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle error response here (e.g., display an error message)
                console.log(error);
            }
        });
    });
});


</script>

