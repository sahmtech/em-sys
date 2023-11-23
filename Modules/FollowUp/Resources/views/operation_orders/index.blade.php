@extends('layouts.app')
@section('title', __('sales::lang.sales'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('sales::lang.all_sales_operations')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label for="offer_type_filter">@lang('sales::lang.contract'):</label>
                    {!! Form::select('contract-select', $contracts->pluck('contract_number', 'contract_number'), null, [
                        'class' => 'form-control',
                        'style' => 'height:36px',
                        'placeholder' => __('lang_v1.all'),
                        'required',
                        'id' => 'contract-select',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('sales::lang.operation_order_type'):</label>
                    <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all')</option>
                        <option value="external">@lang('sales::lang.external')</option>
                        <option value="internal">@lang('sales::lang.internal')</option>

                    </select>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            {{-- @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('sales::lang.create_sale_operation')</a>
            </div>
        @endslot --}}
            @slot('tool')
                <div class="box-tools">

                    <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal"
                        data-target="#addOperationModal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot

            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="operation_table">
                    <thead>
                        <tr>

                            <th>@lang('sales::lang.operation_order_number')</th>
                            <th>@lang('sales::lang.customer_name')</th>
                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.operation_order_type')</th>
                            <th>@lang('sales::lang.Status')</th>
                            <th>@lang('sales::lang.show_operation')</th>
                            {{-- <th>@lang('messages.action')</th> --}}
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
        <div class="modal fade" id="addOperationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open([
                        'url' => action([\Modules\Followup\Http\Controllers\FollowUpOperationOrderController::class, 'store']),
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
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('contact_id', $leads, null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('sales::lang.select_customer'),
                                            'required',
                                            'id' => 'customer-select',
                                        ]) !!}
                                    </div>
                               
                            </div>

                           


                            <div class="form-group col-md-6">
                                
                                    {!! Form::label('contract_id', __('sales::lang.contract') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('sale_contract_id', [], null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('sales::lang.select_contacts'),
                                            'required',
                                            'id' => 'contact-select',
                                        ]) !!}
                                    </div>
                               
                            </div>
                            <div class="form-group col-md-6">
                               
                                    {!! Form::label('status', __('sales::lang.Status') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::select('status', $status, null, [
                                            'class' => 'form-control',
                                            'style' => 'height:36px',
                                            'placeholder' => __('sales::lang.select_status'),
                                            'required',
                                            'id' => 'status-select',
                                        ]) !!}
                                    </div>
                              
                            </div>


                            <div class="form-group col-md-6">
                                
                                    {!! Form::label('Industry', __('sales::lang.Industry') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Industry', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Industry')]) !!}
                                    </div>
                           
                            </div>



                            <div class="form-group col-md-6">
                                
                                    {!! Form::label('operation_order_type', __('sales::lang.operation_order_type') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>

                                        {!! Form::select(
                                            'operation_order_type',
                                            ['Internal' => __('sales::lang.Internal'), 'External' => __('sales::lang.external')],
                                            null,
                                            ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.operation_order_type')],
                                        ) !!}
                                    </div>
                            
                            </div>

                            <div class="form-group col-md-6">
                              
                                    {!! Form::label('Interview', __('sales::lang.Interview') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>

                                        {!! Form::select(
                                            'Interview',
                                            ['Client ' => __('sales::lang.Client'), 'Company' => __('sales::lang.Company')],
                                            null,
                                            ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.Interview')],
                                        ) !!}
                                    </div>
                               
                            </div>

                            <div class="form-group col-md-6">
                                
                                    {!! Form::label('Location', __('sales::lang.Location') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Location', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Location')]) !!}
                                    </div>
                            
                            </div>

                            <div class="form-group col-md-6">
                                
                                    {!! Form::label('Delivery', __('sales::lang.Delivery') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Delivery', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Delivery')]) !!}
                                    </div>
                             
                            </div>

                            <div class="form-group col-md-6">
                               
                                    {!! Form::label('Note', __('sales::lang.Note') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-id-badge"></i>
                                        </span>
                                        {!! Form::text('Note', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Note')]) !!}
                                    </div>
                            
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
        {{-- @foreach($row as $row)
        <button class="btn btn-primary" data-toggle="modal" data-target="#editModal" data-id="{{ $row->id }}">Edit</button>
        @endforeach --}}
            {{-- <div class="modal fade" id="edit_order" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        {!! Form::open(['route' => ['updateOrder', $id], 'method' => 'put', 'id' => 'edit-order-form']) !!}


                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">@lang('essentials::lang.edit_order')</h4>
                        </div>

                        <div class="modal-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                
                                        {!! Form::label('contact_id', __('sales::lang.customer') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::select('contact_id', $leads, null, [
                                                'class' => 'form-control',
                                                'style' => 'height:36px',
                                                'placeholder' => __('sales::lang.select_customer'),
                                                'required',
                                                'id' => 'customer-select',
                                            ]) !!}
                                        </div>
                                
                                </div>

                            


                                <div class="form-group col-md-6">
                                    
                                        {!! Form::label('contract_id', __('sales::lang.contract') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::select('sale_contract_id', [], null, [
                                                'class' => 'form-control',
                                                'style' => 'height:36px',
                                                'placeholder' => __('sales::lang.select_contacts'),
                                                'required',
                                                'id' => 'contact-select',
                                            ]) !!}
                                        </div>
                                
                                </div>
                                <div class="form-group col-md-6">
                                
                                        {!! Form::label('status', __('sales::lang.Status') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::select('status', $status, null, [
                                                'class' => 'form-control',
                                                'style' => 'height:36px',
                                                'placeholder' => __('sales::lang.select_status'),
                                                'required',
                                                'id' => 'status-select',
                                            ]) !!}
                                        </div>
                                
                                </div>


                                <div class="form-group col-md-6">
                                    
                                        {!! Form::label('Industry', __('sales::lang.Industry') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::text('Industry', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Industry')]) !!}
                                        </div>
                            
                                </div>



                                <div class="form-group col-md-6">
                                    
                                        {!! Form::label('operation_order_type', __('sales::lang.operation_order_type') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>

                                            {!! Form::select(
                                                'operation_order_type',
                                                ['Internal' => __('sales::lang.Internal'), 'External' => __('sales::lang.external')],
                                                null,
                                                ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.operation_order_type')],
                                            ) !!}
                                        </div>
                                
                                </div>

                                <div class="form-group col-md-6">
                                
                                        {!! Form::label('Interview', __('sales::lang.Interview') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>

                                            {!! Form::select(
                                                'Interview',
                                                ['Client ' => __('sales::lang.Client'), 'Company' => __('sales::lang.Company')],
                                                null,
                                                ['class' => 'form-control', 'style' => 'height:40px', 'placeholder' => __('sales::lang.Interview')],
                                            ) !!}
                                        </div>
                                
                                </div>

                                <div class="form-group col-md-6">
                                    
                                        {!! Form::label('Location', __('sales::lang.Location') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::text('Location', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Location')]) !!}
                                        </div>
                                
                                </div>

                                <div class="form-group col-md-6">
                                    
                                        {!! Form::label('Delivery', __('sales::lang.Delivery') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::text('Delivery', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Delivery')]) !!}
                                        </div>
                                
                                </div>

                                <div class="form-group col-md-6">
                                
                                        {!! Form::label('Note', __('sales::lang.Note') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-id-badge"></i>
                                            </span>
                                            {!! Form::text('Note', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.Note')]) !!}
                                        </div>
                                
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
            </div> --}}

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
                    url: "{{ route('operation_orders') }}",
                    data: function(d) {

                        d.number_of_contract = $('#contract-select').val();
                        d.Status = $('#status_filter').val();

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


            $('#contract-select, #status_filter').change(function() {
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


                            contactSelect.append('<option value="">اختر العقد</option>');


                           
                            $.each(response, function(index, contract) {
                                contactSelect.append(new Option(contract
                                    .number_of_contract, contract.id));
                            });

                            console.log(contactSelect);
                            contactSelect.find('option').first().attr('selected', 'selected');
                        },
                        error: function() {
                            console.error('An error occurred.');
                        }
                    });
                }
            });

          
            $('.btn-modal').on('click', function (e) {
                e.preventDefault();

                var rowId = $(this).data('row-id');
                $('#edit_order form').attr('action', '/updateOrder/' + rowId);

               
                $('#edit_order').modal('show');
   
            });

            function updateModalContent(rowId) {
        
                $.ajax({
                    url: '/getUpdatedData/' + rowId, 
                    type: 'GET',
                    success: function (data) {
                       
                        $('#edit_order .modal-content').html(data);
                    },
                    error: function (xhr, status, error) {
                       
                        console.error(xhr.responseText);
                    }
            });
    }


        });
    </script>





@endsection
