@extends('layouts.app')
@section('title', __('sales::lang.sales'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('sales::lang.all_your_customers')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
@component('components.filters', ['title' => __('report.filters')])
  
        <div class="col-md-3">
            <div class="form-group">
                <label>
                    {!! Form::checkbox('has_sell_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_due']); !!} <strong>@lang('lang_v1.sell_due')</strong>
                </label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>
                    {!! Form::checkbox('has_sell_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_return']); !!} <strong>@lang('lang_v1.sell_return')</strong>
                </label>
            </div>
        </div>

  
    <div class="col-md-3">
        <div class="form-group">
            <label>
                {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']); !!} <strong>@lang('lang_v1.advance_balance')</strong>
            </label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>
                {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']); !!} <strong>@lang('lang_v1.opening_balance')</strong>
            </label>
        </div>
    </div>
  
        <div class="col-md-3">
            <div class="form-group">
                <label for="has_no_sell_from">@lang('lang_v1.has_no_sell_from'):</label>
                {!! Form::select('has_no_sell_from', ['one_month' => __('lang_v1.one_month'), 'three_months' => __('lang_v1.three_months'), 'six_months' => __('lang_v1.six_months'), 'one_year' => __('lang_v1.one_year')], null, ['class' => 'form-control', 'id' => 'has_no_sell_from', 'placeholder' => __('messages.please_select')]); !!}
            </div>
        </div>

      
   

   

    <div class="col-md-3">
        <div class="form-group">
            <label for="status_filter">@lang('sale.status'):</label>
            {!! Form::select('status_filter', ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')], null, ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')]); !!}
        </div>
    </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])

    @slot('tool')
            <div class="box-tools">
                
                <button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#addCountryModal">
                    <i class="fa fa-plus"></i> @lang('sales::lang.add_contact')
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