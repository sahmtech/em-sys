@extends('layouts.app')
@section('title', __('followup::lang.workers_details'))
@section('content')
    <section class="content-header">
        <h1>
            <span>@lang('followup::lang.workers_details')</span>
        </h1>
    </section>


    <!-- Main content -->
    <section class="content">


        @component('components.widget', ['class' => 'box-primary'])
            @can('user.create')
                @slot('tool')
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="box-tools">
                                <a class="btn btn-block btn-primary" href="{{ route('createWorker', ['id' => $id]) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                            </div>
                        </div>
                    </div>
                @endslot
            @endcan
            <div class="row">


                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                            <th>{{ __('followup::lang.action') }}</th>

                        </tr>

                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                <td>{{ optional(optional($user->appointment)->location)->name }}</td>
                                <td>{{ optional($user->country)->nationality ?? ' ' }}</td>
                                <td>{{ $user->id_proof_number }}</td>
                                <td>
                                    @foreach ($user->OfficialDocument as $off)
                                        @if ($off->type == 'residence_permit')
                                            {{ $off->expiration_date }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{ optional($user->workCard)->id ?? ' ' }}</td>
                                <td>
                                    @if ($user->has_insurance === null)
                                        {{ ' ' }}
                                    @elseif ($user->has_insurance == 0)
                                        {{ __('followup::lang.not_have_insurance') }}
                                    @elseif ($user->has_insurance == 1)
                                        {{ __('followup::lang.has_insurance') }}
                                    @endif
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
                                    @if ($user->gender == 'male')
                                        {{ __('followup::lang.male') }}
                                    @elseif ($user->gender == 'female')
                                        {{ __('followup::lang.female') }}
                                    @else
                                    @endif
                                </td>
                                <td>
                                    @if ($user->essentials_salary)
                                        {{ __('followup::lang.basic_salary') }}: {{ floor($user->essentials_salary) }}
                                    @endif
                                    <br>
                                    @if ($user->allowancesAndDeductions->isNotEmpty())
                                        {{ __('followup::lang.allowances') }}:
                                        <ul>
                                            @foreach ($user->userAllowancesAndDeductions as $allowanceOrDeduction)
                                                <li>{{ $allowanceOrDeduction->allowancedescription->description ?? '' }}:
                                                    {{ floor($allowanceOrDeduction->amount) }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>{{ optional(optional($user->appointment)->profession)->name }}</td>
                                <td>
                                    <a href="{{ route('showWorker', ['id' => $user->id]) }}"
                                        class="btn btn-primary">@lang('followup::lang.view_worker_details')</a>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                </div>

            </div>
        @endcomponent



    @endsection
