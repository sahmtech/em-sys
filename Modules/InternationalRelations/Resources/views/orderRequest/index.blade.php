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
            'id' => 'contract-select'
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
                        <th>@lang('sales::lang.operation_order_type')</th>
                        <th>@lang('internationalrelations::lang.Delegation')</th>
                        <th>@lang('sales::lang.Status')</th>
                     
                      
                    </tr>
                </thead>
            </table>
        </div>

    @endcomponent



</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
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
          
         
            { data: 'operation_order_no', name: 'operation_order_no' },
            { data: 'contact_name', name: 'contact_name' },
            { data: 'contract_number', name: 'contract_number' },
            { data: 'operation_order_type', name: 'operation_order_type' },
            {data: 'Delegation' ,name:'Delegation'},
            { data: 'Status', name: 'Status' },
         
         
           
        ]
    });
     // Add an event listener to trigger filtering when your filters change
 
     $('#contract-select, #status_filter').change(function () {
        customers_table.ajax.reload();
    });

    $(document).on('click', 'button.delete_operation_button', function () {
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
                            success: function (result) {
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
});

</script>





@endsection