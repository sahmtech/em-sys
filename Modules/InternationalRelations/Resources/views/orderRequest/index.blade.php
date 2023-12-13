@extends('layouts.app')
@section('title', __('internationalrelations::lang.Delegation'))

@section('content')


    <section class="content-header">
        <h1>
            <span>@lang('internationalrelations::lang.all_Delegation')</span>
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
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="operation_table">
                    <thead>
                        <tr>

                            <th>@lang('sales::lang.operation_order_number')</th>
                            <th>@lang('sales::lang.customer_name')</th>
                            <th>@lang('sales::lang.contract_number')</th>
                            <th>@lang('sales::lang.orderQuantity')</th>
                            <th>@lang('sales::lang.Status')</th>
                            <th>@lang('messages.action')</th>


                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade" id="addVisaModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => 'storeVisa', 'enctype' => 'multipart/form-data', 'id' => 'addVisaForm']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.addvisa')</h4>
                    </div>
        
                    <div class="modal-body">
                        <div class="row">
                            @csrf
                            <input type="hidden" name="id" id="visaOrderId" value="">
                            <div class="form-group col-md-6">
                                {!! Form::label('visa_number', __('internationalrelations::lang.visa_number') . ':*') !!}
                                {!! Form::number('visa_number', null, ['class' => 'form-control', 'placeholder' => __('internationalrelations::lang.visa_number'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('file', __('internationalrelations::lang.attachments') . ':*') !!}
                                {!! Form::file('file', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.file'), 'required']) !!}
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
                    url: "{{ route('order_request') }}",
                    data: function(d) {

                        d.number_of_contract = $('#contract-select').val();


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
                        data: 'Status',
                        name: 'Status'
                    },
               
                    {
                        data: 'Delegation',
                        name: 'Delegation',
                      
                    },


                ]
            });

            $(document).on('click', '.btn-add-visa', function () {
                var orderId = $(this).data('id');
             
                $('#visaOrderId').val(orderId);
                $('#addVisaModal').modal('show');
            });


            $('#contract-select, #status_filter').change(function() {
                customers_table.ajax.reload();
            });
            
          
                });
    </script>





@endsection
