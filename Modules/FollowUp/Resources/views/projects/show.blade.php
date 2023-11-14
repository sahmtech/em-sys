<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title" id="modalTitle"> @lang('sales::lang.offer_price_details') 
      </h4>
  </div>
  <div class="modal-body">
  
      <div class="row">
      
        <div class="col-sm-12 col-xs-12">
          <div class="table-responsive">
            <table class="table bg-gray" style="width: 100%;">
              <tr class="bg-green">
          
          
              <th>{{ __('followup::lang.name') }}</th>
                <th>{{ __('followup::lang.sponsor') }}</th>
                <th>{{ __('followup::lang.nationality') }}</th>
              <th>{{ __('followup::lang.eqama') }}</th>
              <th>{{ __('followup::lang.eqama_end_date') }}</th>
              <th>{{ __('followup::lang.work_card') }}</th>
              <th>{{ __('followup::lang.insurance') }}</th>
              <th>{{ __('followup::lang.contract_end_date') }}</th>
               <th>{{ __('followup::lang.passport') }}</th>
              <th>{{ __('followup::lang.passport_end_date') }}</th>
              <th>{{ __('followup::lang.gender') }}</th>
              <th>{{ __('followup::lang.salary') }}</th>
              <th>{{ __('followup::lang.profession') }}</th> 



        
      </tr>

      @foreach($users as $user)
      
      <tr>
 
        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
        <td>{{  optional(optional($user->appointment)->location)->name  }}</td>
        <td>{{ optional($user->country)->nationality ?? ' '}}</td>
        <td>{{ $user->id_proof_number}}</td>
       <td> @foreach ( $user->OfficialDocument as $off)
            @if ( $off->type == 'residence_permit') 
            {{ $off->expiration_date}}
          
            @endif
        @endforeach</td>
      
        <td>{{optional($user->workCard)->id ?? ' '}}</td>
        <td>
          @if ($user->has_insurance)
              {{ __('followup::lang.has_insurance') }}
          @else
              {{ __('followup::lang.not_have_insurance') }}
          @endif
      </td>
        <td>{{optional($user->contract)->contract_end_date ?? ' '}}</td>
        <td> @foreach ( $user->OfficialDocument as $off)
            @if ( $off->type == 'passport') 
            {{ $off->number}}
        
            @endif
         @endforeach
        </td>
        <td> @foreach ( $user->OfficialDocument as $off)
        @if ( $off->type == 'passport') 
        {{ $off->expiration_date}}
      
        @endif
        @endforeach</td>
        <td>
          @if ($user->gender == 'male')
              {{ __('followup::lang.male') }}
          @elseif ($user->gender == 'female')
              {{ __('followup::lang.female') }}
          @else
              {{ __('followup::lang.other') }}
          @endif
      </td>
      
      <td>
        {{ __('followup::lang.basic_salary') }}: {{ $user->essentials_salary }}
        <br>
    
        @if ($user->allowancesAndDeductions->isNotEmpty())
        {{ __('followup::lang.allowances') }}:
            <ul>
                @foreach ($user->allowancesAndDeductions as $allowanceOrDeduction)
                    <li>{{ $allowanceOrDeduction->description }}: {{ $allowanceOrDeduction->amount }}</li>
                @endforeach
            </ul>
       
        @endif
    </td>
    
        <td>{{ optional(optional($user->appointment)->profession)->name }}</td> 


    </tr>
  @endforeach
      </table>
          </div>
        </div>
      </div>
  
  </div>
  
  <script type="text/javascript">
    $(document).ready(function(){
      var element = $('div.modal-xl');
      __currency_convert_recursively(element);
    });
    </script>