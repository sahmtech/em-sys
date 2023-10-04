@extends('layouts.app')
@section('title', __('home.home'))
@section('content')

<section class="content" style="font-size: calc(100%);">
    
        

<div class="row">
     
            <div class="box-body">
            
            <div class="row">
                <!-- /.col -->
             
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('users.index') }}">
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                       <a href="{{ route('users.index') }}"> <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">المستخدمين </span></a>

                         
                        <img src="img/coworking.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
</a>
                  <!-- /.info-box -->
                </div>


                
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('customer-group.index') }}"> 
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('customer-group.index') }}"> <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">العملاء والموردين </span></a>

                         
                        <img src="img/global.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


  
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('products.index') }}"> 
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('products.index') }}"> <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">الأصناف </span></a>

                         
                        <img src="img/spare-parts.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


                
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('purchases.index') }}">
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('purchases.index') }}"> <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">المشتريات </span></a>

                         
                        <img src="img/buy-button.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>

                
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('sells.index') }}">  
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('sells.index') }}">  <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">الموارد البشرية  </span></a>

                         
                        <img src="img/acquisition.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


                 
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('expenses.index') }}"> 
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('expenses.index') }}">  <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">المصاريف </span></a>

                         
                        <img src="img/budget.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


                
                 
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('reports.profit-loss') }}">   
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('reports.profit-loss') }}">     <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">التقارير </span></a>

                         
                        <img src="img/report.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>



                   
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('notification-templates.index') }}"> 
                   <div class="info-box">
                      

                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('notification-templates.index') }}">  <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif">نماذج الاشعارات </span></a>

                         
                        <img src="img/notifications.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


                   
                <div class="col-md-4 col-sm-20 col-xs-15" >
                <a href="{{ route('housingmovements.dashboard') }}">
                   <div class="info-box">
                      
                   
                        <div class="info-box-content" style=" background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                       

                        <a href="{{ route('housingmovements.dashboard') }}">  <span class="info-box-text" style=" text-align: center; font-size:30px;, sans-serif"> إدارة السكن والحركة  </span></a>

                         
                        <img src="img/mobility.png"  style="width: 75px;  height: 75px;" alt="">
                        </div>
                     
                        <!-- /.info-box-content -->
                   </div>
                   </a>
                  <!-- /.info-box -->
                </div>


    	    <!-- /.col -->
            </div>
            </div>
    <!-- /.box-body -->
</div>       

</section>

@stop
@section('javascript')
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    @includeIf('sales_order.common_js')
    @includeIf('purchase_order.common_js')
    @if(!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script type="text/javascript">
        $(document).ready( function(){
        sales_order_table = $('#sales_order_table').DataTable({
          processing: true,
          serverSide: true,
          scrollY: "75vh",
          scrollX:        true,
          scrollCollapse: true,
          aaSorting: [[1, 'desc']],
          "ajax": {
              "url": '{{action([\App\Http\Controllers\SellController::class, 'index'])}}?sale_type=sales_order',
              "data": function ( d ) {
                    d.for_dashboard_sales_order = true;

                    if ($('#so_location').length > 0) {
                        d.location_id = $('#so_location').val();
                    }
                }
          },
          columnDefs: [ {
              "targets": 7,
              "orderable": false,
              "searchable": false
          } ],
          columns: [
              { data: 'action', name: 'action'},
              { data: 'transaction_date', name: 'transaction_date'  },
              { data: 'invoice_no', name: 'invoice_no'},
              { data: 'conatct_name', name: 'conatct_name'},
              { data: 'mobile', name: 'contacts.mobile'},
              { data: 'business_location', name: 'bl.name'},
              { data: 'status', name: 'status'},
              { data: 'shipping_status', name: 'shipping_status'},
              { data: 'so_qty_remaining', name: 'so_qty_remaining', "searchable": false},
              { data: 'added_by', name: 'u.first_name'},
          ]
        });

        @if(auth()->user()->can('account.access') && config('constants.show_payments_recovered_today') == true)

            // Cash Flow Table
            cash_flow_table = $('#cash_flow_table').DataTable({
                processing: true,
                serverSide: true,
                "ajax": {
                        "url": "{{action([\App\Http\Controllers\AccountController::class, 'cashFlow'])}}",
                        "data": function ( d ) {
                            d.type = 'credit';
                            d.only_payment_recovered = true;
                        }
                    },
                "ordering": false,
                "searching": false,
                columns: [
                    {data: 'operation_date', name: 'operation_date'},
                    {data: 'account_name', name: 'account_name'},
                    {data: 'sub_type', name: 'sub_type'},
                    {data: 'method', name: 'TP.method'},
                    {data: 'payment_details', name: 'payment_details', searchable: false},
                    {data: 'credit', name: 'amount'},
                    {data: 'balance', name: 'balance'},
                    {data: 'total_balance', name: 'total_balance'},
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#cash_flow_table'));
                },
                "footerCallback": function ( row, data, start, end, display ) {
                    var footer_total_credit = 0;

                    for (var r in data){
                        footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(data[r].credit).data('orig-value')) : 0;
                    }
                    $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                }
            });
        @endif

        $('#so_location').change( function(){
            sales_order_table.ajax.reload();
        });
        @if(!empty($common_settings['enable_purchase_order']))
          //Purchase table
          purchase_order_table = $('#purchase_order_table').DataTable({
              processing: true,
              serverSide: true,
              aaSorting: [[1, 'desc']],
              scrollY: "75vh",
              scrollX:        true,
              scrollCollapse: true,
              ajax: {
                  url: '{{action([\App\Http\Controllers\PurchaseOrderController::class, 'index'])}}',
                  data: function(d) {
                      d.from_dashboard = true;

                        if ($('#po_location').length > 0) {
                            d.location_id = $('#po_location').val();
                        }
                  },
              },
              columns: [
                  { data: 'action', name: 'action', orderable: false, searchable: false },
                  { data: 'transaction_date', name: 'transaction_date' },
                  { data: 'ref_no', name: 'ref_no' },
                  { data: 'location_name', name: 'BS.name' },
                  { data: 'name', name: 'contacts.name' },
                  { data: 'status', name: 'transactions.status' },
                  { data: 'po_qty_remaining', name: 'po_qty_remaining', "searchable": false},
                  { data: 'added_by', name: 'u.first_name' }
              ]
            })

            $('#po_location').change( function(){
                purchase_order_table.ajax.reload();
            });
        @endif

        @if(!empty($common_settings['enable_purchase_requisition']))
          //Purchase table
          purchase_requisition_table = $('#purchase_requisition_table').DataTable({
              processing: true,
              serverSide: true,
              aaSorting: [[1, 'desc']],
              scrollY: "75vh",
              scrollX:        true,
              scrollCollapse: true,
              ajax: {
                  url: '{{action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index'])}}',
                  data: function(d) {
                      d.from_dashboard = true;

                        if ($('#pr_location').length > 0) {
                            d.location_id = $('#pr_location').val();
                        }
                  },
              },
              columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'ref_no', name: 'ref_no' },
                    { data: 'location_name', name: 'BS.name' },
                    { data: 'status', name: 'status' },
                    { data: 'delivery_date', name: 'delivery_date' },
                    { data: 'added_by', name: 'u.first_name' },
              ]
            })

            $('#pr_location').change( function(){
                purchase_requisition_table.ajax.reload();
            });

            $(document).on('click', 'a.delete-purchase-requisition', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    purchase_requisition_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
        @endif

        sell_table = $('#shipments_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            scrollY:        "75vh",
            scrollX:        true,
            scrollCollapse: true,
            "ajax": {
                "url": '{{action([\App\Http\Controllers\SellController::class, 'index'])}}',
                "data": function ( d ) {
                    d.only_pending_shipments = true;
                    if ($('#pending_shipments_location').length > 0) {
                        d.location_id = $('#pending_shipments_location').val();
                    }
                }
            },
            columns: [
                { data: 'action', name: 'action', searchable: false, orderable: false},
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'conatct_name', name: 'conatct_name'},
                { data: 'mobile', name: 'contacts.mobile'},
                { data: 'business_location', name: 'bl.name'},
                { data: 'shipping_status', name: 'shipping_status'},
                @if(!empty($custom_labels['shipping']['custom_field_1']))
                    { data: 'shipping_custom_field_1', name: 'shipping_custom_field_1'},
                @endif
                @if(!empty($custom_labels['shipping']['custom_field_2']))
                    { data: 'shipping_custom_field_2', name: 'shipping_custom_field_2'},
                @endif
                @if(!empty($custom_labels['shipping']['custom_field_3']))
                    { data: 'shipping_custom_field_3', name: 'shipping_custom_field_3'},
                @endif
                @if(!empty($custom_labels['shipping']['custom_field_4']))
                    { data: 'shipping_custom_field_4', name: 'shipping_custom_field_4'},
                @endif
                @if(!empty($custom_labels['shipping']['custom_field_5']))
                    { data: 'shipping_custom_field_5', name: 'shipping_custom_field_5'},
                @endif
                { data: 'payment_status', name: 'payment_status'},
                { data: 'waiter', name: 'ss.first_name', @if(empty($is_service_staff_enabled)) visible: false @endif }
            ],
            "fnDrawCallback": function (oSettings) {
                __currency_convert_recursively($('#sell_table'));
            },
            createdRow: function( row, data, dataIndex ) {
                $( row ).find('td:eq(4)').attr('class', 'clickable_td');
            }
        });

        $('#pending_shipments_location').change( function(){
            sell_table.ajax.reload();
        });
    });
    </script>
@endsection

