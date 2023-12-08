@extends('layouts.app')
@section('title', __('internationalrelations::lang.visa_cards'))

@section('content')


    <section class="content-header">
        <h1>
            @lang('internationalrelations::lang.visa_cards')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
       
        @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
        <div class="box-tools">
            
            <button type="button" class="btn btn-block btn-primary  btn-modal" data-toggle="modal" data-target="#addvisa">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        </div>
        @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="orders-table">
                    <thead>
                        <tr>
                            <th>@lang('internationalrelations::lang.visa_number')</th>
                            <th>@lang('internationalrelations::lang.arrival_date')</th>
                            <th>@lang('internationalrelations::lang.operation_order_no')</th>
                            <th>@lang('internationalrelations::lang.contact_name')</th>
                            <th>@lang('internationalrelations::lang.number_of_contract')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('internationalrelations::lang.professions')</th>
                            <th>@lang('internationalrelations::lang.nationalities')</th>
                            <th>@lang('internationalrelations::lang.totalQuantity')</th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
            
        @endcomponent
        <div class="modal fade" id="addvisa" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    {!! Form::open(['route' => 'storeVisa']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">@lang('internationalrelations::lang.addvisa')</h4>
                    </div>
        
                    <div class="modal-body">
    
                        <div class="row">
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('visa_number', __('internationalrelations::lang.visa_number') . ':*') !!}
                                {!! Form::number('visa_number', null, ['class' => 'form-control', 'placeholder' => __('internationalrelations::lang.visa_number'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('operation_order', __('internationalrelations::lang.operation_order') . ':*') !!}
                                {!! Form::select('operation_order',$orders, null, ['class' => 'form-control', 'style' => 'height:40px','placeholder' => __('internationalrelations::lang.operation_order'), 'required']) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('arrival_date', __('internationalrelations::lang.arrival_date') . ':*') !!}
                                {!! Form::date('arrival_date', null, ['class' => 'form-control', 'placeholder' => __('internationalrelations::lang.arrival_date'), 'required']) !!}
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
 
@stop

@section('javascript')
    <script type="text/javascript">
    $(document).ready(function() {
        var ordersTable = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('visa_cards') }}",
        },
        columns: [
            { data: 'visa_number', name: 'visa_number' },
            { data: 'arrival_date', name: 'arrival_date' },
         
            { data: 'operation_order_no', name: 'operation_order_no' },
            { data: 'supplier_business_name', name: 'supplier_business_name' },
            { data: 'number_of_contract', name: 'number_of_contract' },
            { data: 'agency_name', name: 'agency_name' },
            { data: 'profession_list', name: 'profession_list' },

            
            { data: 'nationality_list',name: 'nationality_list' },
            { data: 'orderQuantity', name: 'orderQuantity' },
        ],
    });
    $('#orders-table tbody').on('click', 'tr', function() {
                var data = ordersTable.row(this).data();
                var visaId = data.id;

                if (visaId) {
       
                    var viewUrl = '{{ route('viewVisaWorkers', ['id' => ':visaId']) }}'.replace(':visaId', visaId);
                    
                    
                    window.location.href = viewUrl;
                }

                });
    });

    </script>
@endsection
