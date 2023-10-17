<div class="table-responsive">
    <table class="table table-bordered add-client-price-table table-condensed">
        <tr> 
            <th>@lang('sales::lang.number_of_clients')</th>
            <th>@lang('sales::lang.monthly_cost')</th> 
            <th>@lang('sales::lang.total')</th>
       
          
        </tr>
        <tr>
          <td>
            <div class="col-sm-6">
              {!! Form::text('number', 0, ['class' => 'form-control input-sm  input_number', 'placeholder' => __('sales::lang.number_of_clients'), 'required']); !!}
            </div> </td>
        <td>
            <div class="col-sm-6">
              {!! Form::text('monthly_cost', 0, ['class' => 'form-control input-sm  input_number', 'placeholder' => __('sales::lang.monthly_cost'), 'required']); !!}
            </div> 
        </td>

          

        </tr>
    </table>
</div>