@extends('layouts.app')
@section('title', __('internationalrelations::lang.company_requests'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('internationalrelations::lang.company_requests')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

  
      
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="EmpCompany_table">
        
                <tr>
                    <th>{{ __('sales::lang.quantity') }}</th>
                    <th>{{ __('sales::lang.additional_allwances') }}</th>
                    <th>{{ __('sales::lang.gender') }}</th>
                    <th>{{ __('sales::lang.service_price') }}</th>
                    <th>{{ __('sales::lang.monthly_cost_for_one') }}</th>
                    <th>{{ __('sales::lang.profession_name') }}</th>
                    <th>{{ __('sales::lang.specialization_name') }}</th>
              
                </tr>
                @foreach ($irDelegations as $delegation)
                <tr>
                    <td>{{ $delegation->targeted_quantity }}</td>
                    
                    <td>
                        @if (!empty($delegation->transactionSellLine->additional_allwances))
                            <ul>
                                @foreach (json_decode($delegation->transactionSellLine->additional_allwances) as $allwance)
                                    @if (is_object($allwance) && property_exists($allwance, 'salaryType') && property_exists($allwance, 'amount'))
                                        <li>
                                            {{ __('sales::lang.' . $allwance->salaryType) }}:
                                            {{ $allwance->amount }}

                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif



                    </td>
                    <td> {{ __('sales::lang.' . $delegation->transactionSellLine->service->gender) }}</td>
                    <td>{{ $delegation->transactionSellLine->service->service_price }}</td>
                    <td>{{ $delegation->transactionSellLine->service->monthly_cost_for_one }}</td>
                    <td>{{ $delegation->transactionSellLine->service->profession->name }}</td>
                    <td>{{ $delegation->transactionSellLine->service->specialization->name }}</td>
                </tr>
            @endforeach
              

        </table>
    </div>
    
 
    @endcomponent
    

</section>
<!-- /.content -->

@endsection
