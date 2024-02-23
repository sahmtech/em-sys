@extends('layouts.app')
@section('title', __('internationalrelations::lang.visa_reports'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('internationalrelations::lang.visa_reports')</span>
    </h1>
</section>


<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

  
      
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="visaCards_table">
        
                <tr>
                    <th>{{ __('internationalrelations::lang.visa_number') }}</th>   
                    <th>{{ __('internationalrelations::lang.visa_start_date') }}</th>   
                    <th>{{ __('internationalrelations::lang.visa_end_date') }}</th>   
                <th>{{ __('internationalrelations::lang.visa_status') }}</th> 

                 
              
                </tr>
                @foreach ($visaCards as $visaCard)
                <tr>

                    <td>{{ $visaCard->visa_number ?? ""}}</td>
                    <td>{{ $visaCard->start_date ?? ""}}</td>
                    <td>{{ $visaCard->delegation->lastArrivalproposedLaborsForVisaCard($visaCard->id)->first()->arrival_date ?? ""}}</td>
                   <td>
                        @if(isset($visaCard->status))
                            @if($visaCard->status == 0)
                                {{ __('internationalrelations::lang.existing_visa') }}
                            @elseif($visaCard->status == 1)
                                {{ __('internationalrelations::lang.final_visa') }}
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
              

        </table>
    </div>
    
 
    @endcomponent
    

</section>
<!-- /.content -->

@endsection
