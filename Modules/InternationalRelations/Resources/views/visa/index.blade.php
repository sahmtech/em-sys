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
      
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="orders-table">
                    <thead>
                        <tr>
                            <th>@lang('internationalrelations::lang.visa_number')</th>
                            <th>@lang('internationalrelations::lang.operation_order_no')</th>
                            <th>@lang('internationalrelations::lang.contact_name')</th>
                            <th>@lang('internationalrelations::lang.number_of_contract')</th>
                            <th>@lang('internationalrelations::lang.agency_name')</th>
                            <th>@lang('internationalrelations::lang.professions')</th>
                            <th>@lang('internationalrelations::lang.nationalities')</th>
                            <th>@lang('internationalrelations::lang.totalQuantity')</th>
                            <th>@lang('messages.action')</th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
            
        @endcomponent
   
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
         
            { data: 'operation_order_no', name: 'operation_order_no' },
            { data: 'supplier_business_name', name: 'supplier_business_name' },
            { data: 'number_of_contract', name: 'number_of_contract' },
            { data: 'agency_name', name: 'agency_name' },
            { data: 'profession_list', name: 'profession_list' },

            { data: 'nationality_list',name: 'nationality_list' },
            { data: 'orderQuantity', name: 'orderQuantity' },
            {
                data: null,
                render: function (data, type, row) {
                    return '<button class="btn btn-primary view-visa">@lang("internationalrelations::lang.view_visa_workers")</button>';
                }
            }
        ],
    });
    $('#orders-table tbody').on('click', 'button.view-visa', function() {
            var data = ordersTable.row($(this).closest('tr')).data();
            var visaId = data.id;

            if (visaId) {
                var viewUrl = '{{ route('viewVisaWorkers', ['id' => ':visaId']) }}'.replace(':visaId', visaId);
                window.location.href = viewUrl;
            }
        });
    });

    </script>
@endsection
