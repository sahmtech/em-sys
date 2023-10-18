<div class="table-responsive">
  <table class="table table-bordered add-product-price-table table-condensed">
      <tr> 
          <th>@lang('sales::lang.number_of_clients')</th>
          <th>@lang('sales::lang.monthly_cost')</th> 
          <th>@lang('sales::lang.total')</th>
      </tr>
      <tr>
          <td>
              <div class="col-sm-6">
                  {!! Form::text('number', 0, ['class' => 'form-control input-sm input_number', 'placeholder' => __('sales::lang.number_of_clients'), 'required', 'id' => 'number']); !!}
              </div>
          </td>
          <td>
              <div class="col-sm-6">
                  {!! Form::text('monthly_cost', 0, ['class' => 'form-control input-sm input_number', 'placeholder' => __('sales::lang.monthly_cost'), 'required', 'id' => 'monthly_cost']); !!}
              </div> 
          </td>
          <td>
              <div class="col-sm-6">
                  {!! Form::text('total', 0, ['class' => 'form-control input-sm', 'disabled', 'id' => 'total']); !!}
              </div>
          </td>
      </tr>
  </table>
</div>

<script>
  // Get references to the input fields and the total field
  const numberInput = document.getElementById('number');
  const monthlyCostInput = document.getElementById('monthly_cost');
  const totalField = document.getElementById('total');

  // Add event listeners to both input fields
  numberInput.addEventListener('input', updateTotal);
  monthlyCostInput.addEventListener('input', updateTotal);

  // Function to calculate and update the total field
  function updateTotal() {
      // Get the values from the input fields
      const numberValue = parseFloat(numberInput.value) || 0;
      const monthlyCostValue = parseFloat(monthlyCostInput.value) || 0;

      // Calculate the product of the two values
      const totalValue = numberValue * monthlyCostValue;

      // Update the total field with the calculated value
      totalField.value = totalValue;
  }
</script>
