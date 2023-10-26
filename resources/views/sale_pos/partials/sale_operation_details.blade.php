<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>{{ __('sales::lang.product_name') }}</th>
        <th>{{ __('sales::lang.product_type') }}</th>
        <th>{{ __('sales::lang.gender') }}</th>
        <th>{{ __('sales::lang.service_price') }}</th>
        <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
        <th>{{ __('sales::lang.nationality_name') }}</th>
        <th>{{ __('sales::lang.profession_name') }}</th>
        <th>{{ __('sales::lang.specialization_name') }}</th>
        <th>{{ __('sales::lang.unit') }}</th>
</tr>
        @foreach($products as $product)
        <tr>
            <td>
            {{ $product->name }}
            </td>
            <td>
            {{ $product->type }}
            </td>
            <td> {{ $product->gender }}</td>
            <td> {{ $product->service_price }}</td>
            <td> {{ $product->monthly_cost_for_one }}</td>
            <td> {{ $product->nationality_name }}</td>
            <td> {{ $product->profession_name }}</td>
            <td> {{ $product->specialization_name }}</td>
            <td> {{ $product->unit }}</td>
            
        </tr>
        @endforeach
</table>