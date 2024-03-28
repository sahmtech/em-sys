@extends('layouts.app')
@section('title', __('sales::lang.orderOperationForUnsupportedWorkers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.orderOperationForUnsupportedWorkers')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @include('sales::layouts.nav_operation_orders')


        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('sales.add_sale_operation_orders'))
                @slot('tool')
                    <div class="box-tools">

                        <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                            data-target="#addOperationModal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endslot
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="operation_table">
                    <thead>
                        <tr>

                            <th>@lang('sales::lang.operation_order_number')</th>
                            <th>@lang('sales::lang.profession')</th>
                            <th>@lang('sales::lang.specialization')</th>
                            <th>@lang('sales::lang.nationality')</th>
                            <th>@lang('sales::lang.salary')</th>
                            <th>@lang('sales::lang.date')</th>
                            <th>@lang('sales::lang.orderQuantity')</th>
                            <th>@lang('sales::lang.Interview')</th>
                            <th>@lang('sales::lang.Industry')</th>
                            <th>@lang('sales::lang.Location')</th>
                            <th>@lang('sales::lang.Delivery')</th>
                            <th>@lang('sales::lang.Status')</th>

                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent


        <div class="modal fade" id="addOperationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => action([\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'storeOrderOperation']),
                        'method' => 'post',
                    ]) !!}

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('sales::lang.create_sale_operation')</h4>
                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <div class="form-group col-md-6">
                                {!! Form::label('order_id', __('sales::lang.order_number') . ':*') !!}
                                {!! Form::select('order_id', $orders, null, [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'placeholder' => __('sales::lang.select_order'),
                                    'required',
                                    'id' => 'order-select',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('Industry', __('sales::lang.Industry') . ':') !!}
                                {!! Form::text('Industry', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Industry')]) !!}
                            </div>
                            <div class='clearfix'></div>

                            <div class="form-group col-md-6">
                                {!! Form::label('quantity', __('sales::lang.quantity') . ':*') !!}
                                {!! Form::Number('quantity', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('sales::lang.quantity'),
                                    'id' => 'quantity-input',
                                ]) !!}
                                <p id="quantity-message" style="color: red;"></p>
                            </div>



                            <div class="form-group col-md-6">

                                {!! Form::label('Interview', __('sales::lang.Interview') . ':*') !!}
                                {!! Form::select(
                                    'Interview',
                                    ['Client ' => __('sales::lang.Client'), 'Company' => __('sales::lang.Company')],
                                    null,
                                    ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.Interview')],
                                ) !!}


                            </div>
                            <div class='clearfix'></div>
                            <div class="form-group col-md-6">

                                {!! Form::label('Location', __('sales::lang.Location') . ':') !!}
                                {!! Form::text('Location', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Location')]) !!}


                            </div>

                            <div class="form-group col-md-6">

                                {!! Form::label('Delivery', __('sales::lang.Delivery') . ':') !!}
                                {!! Form::text('Delivery', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Delivery')]) !!}

                            </div>



                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>


    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var customers_table = $('#operation_table').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sale.orderOperationForUnsupportedWorkers') }}",
                    // data: function(d) {

                    //     d.number_of_contract = $('#contract-select').val();
                    //     d.type = $('#type_filter').val();

                    // }
                },



                columns: [


                    {
                        data: 'operation_order_no',
                        name: 'operation_order_no'
                    },
                    {
                        data: 'profession_id',
                        name: 'profession_id'
                    }, {
                        data: 'specialization_id',
                        name: 'specialization_id'
                    }, {
                        data: 'nationality_id',
                        name: 'nationality_id'
                    }, {
                        data: 'salary',
                        name: 'salary'
                    }, {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'orderQuantity',
                        name: 'orderQuantity'
                    },
                    {
                        data: 'Interview',
                        name: 'Interview'
                    }, {
                        data: 'Industry',
                        name: 'Industry'
                    }, {
                        data: 'Location',
                        name: 'Location'
                    }, {
                        data: 'Delivery',
                        name: 'Delivery'
                    },
                    {
                        data: 'Status',
                        name: 'Status'
                    },


                ]
            });
            // Add an event listener to trigger filtering when your filters change

            // $('#contract-select, #type_filter').change(function() {
            //     customers_table.ajax.reload();
            // });

            $('#order-select').change(function() {
                var selectedOrderId = $(this).val();

                if (selectedOrderId) {
                    $.ajax({
                        url: '{{ route('get-order-details') }}',
                        type: 'POST',
                        data: {
                            order_id: selectedOrderId
                        },
                        success: function(response) {

                            $('#quantity-input').attr('max', response);
                            $('#quantity-message').text('Enter a number equal or less than ' +
                                response);

                        },
                        error: function() {
                            console.error('An error occurred.');
                        }
                    });
                }
            });





        });
    </script>





@endsection
