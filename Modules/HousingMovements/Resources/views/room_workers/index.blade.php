@extends('layouts.app')
@section('title', __('housingmovements::lang.room_workers'))

@section('content')

<section class="content-header">
    <h1>
        <span>@lang('followup::lang.workers_details')</span>
        - {{ __('housingmovements::lang.room_number') }} {{ $roomWorkersHistory->first()->room->room_number }}
    </h1>
</section>

  <!-- Main content -->
  <section class="content">


@component('components.widget', ['class' => 'box-primary'])

    <div class="row">


        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr class="bg-green">
                    <th>{{ __('followup::lang.name') }}</th>
                    <th>{{ __('housingmovements::lang.still_housed') }}</th>
                    <th>{{ __('followup::lang.sponsor') }}</th>
                    <th>{{ __('followup::lang.gender') }}</th>
                    <th>{{ __('followup::lang.nationality') }}</th>
                    <th>{{ __('followup::lang.eqama') }}</th>
                    <th>{{ __('followup::lang.eqama_end_date') }}</th>
                 
                 
                    <th>{{ __('followup::lang.contract_end_date') }}</th>
                    <th>{{ __('followup::lang.passport') }}</th>
                    <th>{{ __('followup::lang.passport_end_date') }}</th>
                 
                    <th>{{ __('followup::lang.salary') }}</th>
                    <th>{{ __('followup::lang.profession') }}</th>
                

                </tr>

                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td>
                            @php
                                $stillHoused = $roomWorkersHistory->where('worker_id', $user->id)->pluck('still_housed')->first();
                            @endphp

                            @if ($stillHoused)
                                {{ __('housingmovements::lang.yes') }}
                            @else
                                {{ __('housingmovements::lang.no') }}
                            @endif
                        </td>
                        <td>{{ optional(optional($user->appointment)->location)->name }}</td>
                        <td>
                            @if ($user->gender == 'male')
                                {{ __('followup::lang.male') }}
                            @elseif ($user->gender == 'female')
                                {{ __('followup::lang.female') }}
                            @else
                            @endif
                        </td>
                        <td>{{ optional($user->country)->nationality ?? ' ' }}</td>
                        <td>{{ $user->id_proof_number }}</td>
                        <td>
                            @foreach ($user->OfficialDocument as $off)
                                @if ($off->type == 'residence_permit')
                                    {{ $off->expiration_date }}
                                @endif
                            @endforeach
                        </td>
                     
                       
                        <td>{{ optional($user->contract)->contract_end_date ?? ' ' }}</td>
                        <td>
                            @foreach ($user->OfficialDocument as $off)
                                @if ($off->type == 'passport')
                                    {{ $off->number }}
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach ($user->OfficialDocument as $off)
                                @if ($off->type == 'passport')
                                    {{ $off->expiration_date }}
                                @endif
                            @endforeach
                        </td>
                       
                        <td>
                            @if ($user->essentials_salary)
                                {{ __('followup::lang.basic_salary') }}: {{ floor($user->essentials_salary) }}
                            @endif
                            <br>
                            @if ($user->allowancesAndDeductions->isNotEmpty())
                                {{ __('followup::lang.allowances') }}:
                                <ul>
                                    @foreach ($user->UserallowancesAndDeductions as $allowanceOrDeduction)
                                        <li>{{ $allowanceOrDeduction->allowancedescription->description ?? '' }}:
                                            {{ floor($allowanceOrDeduction->amount) }}</li>
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
@endcomponent


@endsection