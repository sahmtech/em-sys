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
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addCountryModal">
                    <i class="fa fa-plus"></i> @lang('sales::lang.add_sale_operation')
                </button>
            </div>
    @endslot
      
          
 
    @endcomponent



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