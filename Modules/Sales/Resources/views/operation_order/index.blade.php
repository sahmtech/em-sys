@extends('layouts.app')
@section('title', __('sales::lang.all_sales_operations'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.all_sales_operations')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @include('sales::layouts.nav_operation_orders')
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="offer_type_filter">@lang('sales::lang.contract'):</label>
                    {!! Form::select('contract-select', $contracts->pluck('contract_number', 'contract_number'), null, [
                        'class' => 'form-control',
                        'style' => 'height:40px',
                        'placeholder' => __('lang_v1.all'),
                        'required',
                        'id' => 'contract-select',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="type_filter">@lang('sales::lang.operation_order_type'):</label>
                    <select class="form-control select2" name="type_filter" required id="type_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="external">@lang('sales::lang.external')</option>
                        <option value="internal">@lang('sales::lang.internal')</option>

                    </select>
                </div>
            </div>
        @endcomponent

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
                            <th>@lang('sales::lang.customer_name')</th>
                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.orderQuantity')</th>
                            <th>@lang('sales::lang.operation_order_type')</th>
                            <th>@lang('sales::lang.Status')</th>
                            <th>@lang('sales::lang.show_operation')</th>
                            {{-- <th>@lang('messages.action')</th> --}}
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade item_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade" id="addOperationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'store']),
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
                                {!! Form::label('contact_id', __('sales::lang.customer') . ':*') !!}
                                {!! Form::select('contact_id', $leads, null, [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'placeholder' => __('sales::lang.select_customer'),
                                    'required',
                                    'id' => 'customer-select',
                                ]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('contract_id', __('sales::lang.contract') . ':*') !!}
                                {!! Form::select('sale_contract_id', [], null, [
                                    'class' => 'form-control',
                                    'style' => 'height:40px',
                                    'placeholder' => __('sales::lang.select_contacts'),
                                    'required',
                                    'id' => 'contact-select',
                                ]) !!}
                            </div>



                            <div class="form-group col-md-6">
                                {!! Form::label('Industry', __('sales::lang.Industry') . ':') !!}
                                {!! Form::text('Industry', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Industry')]) !!}
                            </div>


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
                                {!! Form::label('operation_order_type', __('sales::lang.operation_order_type') . ':*') !!}
                                {!! Form::select(
                                    'operation_order_type',
                                    ['Internal' => __('sales::lang.Internal'), 'External' => __('sales::lang.external')],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'style' => 'height:40px',
                                        'required',
                                        'placeholder' => __('sales::lang.operation_order_type'),
                                    ],
                                ) !!}

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

                            <div class="form-group col-md-6">

                                {!! Form::label('Location', __('sales::lang.Location') . ':') !!}
                                {!! Form::text('Location', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Location')]) !!}


                            </div>

                            <div class="form-group col-md-6">

                                {!! Form::label('Delivery', __('sales::lang.Delivery') . ':') !!}
                                {!! Form::text('Delivery', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Delivery')]) !!}

                            </div>

                            <div class="form-group col-md-6">

                                {!! Form::label('Note', __('sales::lang.Note') . ':') !!}
                                {!! Form::text('Note', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Note')]) !!}


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
                    url: "{{ route('sale.orderOperations') }}",
                    data: function(d) {

                        d.number_of_contract = $('#contract-select').val();
                        d.type = $('#type_filter').val();

                    }
                },



                columns: [


                    {
                        data: 'operation_order_no',
                        name: 'operation_order_no'
                    },
                    {
                        data: 'contact_name',
                        name: 'contact_name'
                    },
                    {
                        data: 'contract_number',
                        name: 'contract_number'
                    },
                    {
                        data: 'orderQuantity',
                        name: 'orderQuantity'
                    },

                    {
                        data: 'operation_order_type',
                        render: function(data, type, full, meta) {
                            switch (data) {
                                case 'External':
                                    return '{{ trans('sales::lang.external') }}';
                                case 'Internal':
                                    return '{{ trans('sales::lang.internal') }}';

                                default:
                                    return data;
                            }
                        }
                    },
                    {
                        data: 'Status',
                        name: 'Status'
                    },
                    {
                        data: 'show_operation',
                        name: 'show_operation'
                    },
                    /*   {
                           data: 'action',
                           name: 'action',
                           orderable: false,
                           searchable: false
                       },*/

                ]
            });
            // Add an event listener to trigger filtering when your filters change

            $('#contract-select, #type_filter').change(function() {
                customers_table.ajax.reload();
            });



            $(document).on('click', 'button.delete_operation_button', function() {
                swal({
                    title: LANG.sure,
                    text: LANG.confirm_contract,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        console.log(href);
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    contracts_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });

            });

            $('#customer-select').change(function() {
                var selectedCustomerId = $(this).val();
                if (selectedCustomerId) {
                    $.ajax({
                        url: '{{ route('get-contracts') }}',
                        type: 'POST',
                        data: {
                            customer_id: selectedCustomerId
                        },
                        success: function(response) {
                            var contactSelect = $('#contact-select');
                            contactSelect.empty();


                            contactSelect.append(
                                '<option value="">اختر العقد</option>');



                            $.each(response, function(index, contract) {
                                contactSelect.append(new Option(contract
                                    .number_of_contract, contract.id
                                ));
                            });

                            console.log(contactSelect);
                            contactSelect.find('option').first().attr('selected',
                                'selected');
                        },
                        error: function() {
                            console.error('An error occurred.');
                        }
                    });
                }
            });


            $('#contact-select').change(function() {
                var selectedContractId = $(this).val();

                if (selectedContractId) {
                    $.ajax({
                        url: '{{ route('get-contract-details') }}',
                        type: 'POST',
                        data: {
                            contract_id: selectedContractId
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
