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


    @component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addSaleOperationModal">
                    <i class="fa fa-plus"></i> @lang('sales::lang.add_sale_operation')
                </button>
            </div>
    @endslot
    @endcomponent


<div class="modal fade" id="addSaleOperationModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
                {!! Form::open(['route' => 'sale.storeSaleOperation']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('sales::lang.add_sale_operation')</h4>
                </div>

            <div class="modal-body">
                               
                                <div class="row">            

                                        <div class="col-md-4 ">
                                                <div class="form-group">
                                                    {!! Form::label('Industry', __('sales::lang.first_name')  . ':*') !!}
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="fa fa-briefcase"></i>
                                                        </span>
                                                        {!! Form::text('Industry', null, ['class' => 'form-control', 'placeholder' => __('sales::lang.first_name')]); !!}
                                                    </div>
                                                </div>
                                        </div>

                  

                                       
                                 </div>                           
                      
                                     

                            
            </div>
    </div>
</div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    // Countries table
    $(document).ready(function () {
    var customers_table = $('#cust_table').DataTable({
        ajax:'', 
        processing: true,
        serverSide: true,
        
        
       
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'contact_id', name: 'contact_id' },
            { data: 'name', name: 'name' },
            { data: 'english_name', name: 'english_name' },
            { data: 'commercial_register_no', name: 'commercial_register_no' },
            { data: 'mobile', name: 'mobile' },
            { data: 'email', name: 'email' },
            { data: 'city', name: 'city' }
        ]
    });
   
});





  

</script>




@endsection