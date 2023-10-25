<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>{{ __('sales::lang.product_name') }}</th>
        <th>{{ __('sales::lang.product_type') }}</th>
        <th>{{ __('sales::lang.gender') }}</th>
        <th>{{ __('sales::lang.service_price') }}</th>
        <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
      
</tr>
        @foreach($products as $product)
        <tr>
            <td>
            {{ $product->name }}
            </td>
            <td>
            {{ $product->type }}
            </td>
            <td> {{ $product->service_price }}</td>
         
        </tr>
        @endforeach
</table>