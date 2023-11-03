<table class="table">
<tr>
   
   
    <th>{{ __('sale.quantity') }}</th>
    <th>{{ __('sale.additional_allwances') }}</th>
</tr>
@foreach($query->sell_lines as $sell_line)
    <tr>
     
        <td>
            {{ $sell_line->quantity }}
          
            {{ $sell_line->additional_allwances ?? ''}}
           
           
        </td>
      
     
        
     
    </tr>
  
@endforeach
</table>