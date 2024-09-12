@extends('layouts.app')
@section('title', __('sales::lang.orderOperationForUnsupportedWorkers'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('internationalrelations::lang.orderOperationForUnsupportedWorkers')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @include('internationalrelations::layouts.nav_operation_orders')


        @component('components.widget', ['class' => 'box-primary'])
            @if (auth()->user()->hasRole('Admin#1') || auth()->user()->can('internationalrelations.add_ir_operation_orders'))
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
                            {{-- <th>@lang('sales::lang.specialization')</th> --}}
                            <th>@lang('sales::lang.nationality')</th>
                            <th>@lang('sales::lang.salary')</th>
                            <th>@lang('sales::lang.date')</th>
                            <th>@lang('sales::lang.orderQuantity')</th>

                            <th>@lang('sales::lang.Status')</th>
                            <th>@lang('sales::lang.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade" id="addOperationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'storeOrderOperation']),
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


        <div class="modal fade" id="addVisaModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'unSupportedVisaStore', 'enctype' => 'multipart/form-data', 'id' => 'addVisaForm']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.addvisa')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            @csrf
                            <input type="hidden" name="unSupported_operation_id" id="visaOrderId" value="">
                            <div class="col-md-10" id="nationalityInputsContainer"></div>
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
            var operation_table = $('#operation_table').DataTable({

                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('ir.orderOperationForUnsupportedWorkers') }}",

                },



                columns: [


                    {
                        data: 'operation_order_no',
                        name: 'operation_order_no'
                    },
                    {
                        data: 'profession_id',
                        name: 'profession_id'
                    },
                    // {
                    //     data: 'specialization_id',
                    //     name: 'specialization_id'
                    // }, 
                    {
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
                        data: 'Status',
                        name: 'Status'
                    },
                    {
                        data: 'Delegation',
                        name: 'Delegation'
                    },


                ]
            });

            $(document).on('click', '.btn-add-visa', function() {
                var orderId = $(this).data('id');
                var modal = $('#addVisaModal');


                $.ajax({
                    type: 'GET',
                    url: '{{ route('getUnSupportedNationalities') }}',
                    data: {
                        orderId: orderId

                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data && response.data.nationalities &&
                            response.data.nationalities.length > 0) {
                            var nationalities = response.data.nationalities;


                            var nationalityInputsContainer = modal.find(
                                '#nationalityInputsContainer');
                            nationalityInputsContainer.html('');


                            $.each(nationalities, function(index, nationality) {

                                var rowHtml = '<div class="row">' +
                                    '<div class="col-md-12">' +
                                    '<h4>' + nationality.nationality + '</h4>' +
                                    '</div>' +
                                    '</div>';


                                nationalityInputsContainer.append(rowHtml);


                                var visaNumberInput =
                                    '<div class="form-group col-md-6">' +
                                    '<label for="visa_number_' + nationality.id + '">' +
                                    '{{ __('internationalrelations::lang.visa_number') }}*' +
                                    '</label>' +
                                    '<input type="number" name="visa_number[' +
                                    nationality.id +
                                    ']" class="form-control" required>' +
                                    '</div>';
                                nationalityInputsContainer.append(visaNumberInput);


                                var fileInput = '<div class="form-group col-md-6">' +
                                    '<label for="file_' + nationality.id + '">' +
                                    '{{ __('internationalrelations::lang.attachments') }}*' +
                                    '</label>' +
                                    '<input type="file" name="file[' + nationality.id +
                                    ']" class="form-control" required>' +
                                    '</div>';
                                nationalityInputsContainer.append(fileInput);
                            });


                            modal.find('#visaOrderId').val(orderId);


                            modal.modal('show');
                        } else {

                            var nationalityInputsContainer = modal.find(
                                '#nationalityInputsContainer');
                            nationalityInputsContainer.html(
                                '<div class="col-md-12"><p>{{ __('internationalrelations::lang.no_nationalities_delegation') }}</p></div>'
                            );
                            modal.find('#visaOrderId').val(orderId);
                            modal.modal('show');
                        }
                    },
                    error: function(error) {

                        console.error('AJAX request failed', error);
                    }
                });
            });
        });
    </script>





@endsection
