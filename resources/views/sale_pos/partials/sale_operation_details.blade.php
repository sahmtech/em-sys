<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
    
      
        <th>{{ __('sales::lang.gender') }}</th>
        <th>{{ __('sales::lang.service_price') }}</th>
        <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
      
        <th>{{ __('sales::lang.profession_name') }}</th>
        <th>{{ __('sales::lang.specialization_name') }}</th>
      
</tr>
        @foreach($sell_lines as $line)
        <tr>
          
           
            <td> {{ $line->service->gender }}</td>
            <td> {{ $line->service->service_price }}</td>
            <td> {{ $line->service->monthly_cost_for_one }}</td>
         
            <td> {{ $line->service->profession->name }}</td>
            <td> {{$line->service->specialization->name }}</td>
        
            
        </tr>
        @endforeach
</table>