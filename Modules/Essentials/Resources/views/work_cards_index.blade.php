@extends('layouts.app')


@section('content')
<style>
    .widget-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Adjust the gap between columns as needed */
}

.custom_card {
    flex: 1;
    min-width: 0; /* Allow cards to shrink beyond their minimum content width */
}
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
    <div class="widget-container">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">

            @if (auth()->user()->can('essentials.view_all_expire_resdiency_by_fiften'))
                <div class="col-md-3 " onclick="redirectToExpiredResidencies()" style="cursor: pointer; padding:15px;">
              
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                           
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.end_residency') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-0">{{$last15_expire_date_residence}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
             
            @endif

            @if (auth()->user()->can('essentials.view_all_expire_resdiency'))
                <div class="col-md-3 " onclick="redirectToAllEndedResidency()" style="cursor: pointer; padding:15px;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                             
                                <div class="w-title">
                                   
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.all_finish_residency') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-1">{{$all_ended_residency_date}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endif
              
                <div class="col-md-3 " style="cursor: pointer; padding:15px;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.absentee_report') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-2">{{$escapeRequest}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
             
                <div class="col-md-3 " style="cursor: pointer; padding:15px;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.residency_Vacations') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-3">{{$vacationrequest}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <br>

                @if (auth()->user()->can('essentials.view_late_empolyee'))
                <div class="col-md-3 "  onclick="redirectTolatevacation()" style="cursor: pointer; padding:15px;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.late_empolyee') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-3">{{$late_vacation}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

              @endif

              @if (auth()->user()->can('essentials.view_final_empolyee_visa'))
                <div class="col-md-3 " onclick="final_visa()" style="cursor: pointer; padding:15px;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.visa_employee') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-3">{{$final_visa}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                @endif

      

            </div>
        </div>
           
    </div>
    </section>

     <!-- Main content -->
<section class="content">

        <div class="row">
            <div class="col-md-12 ">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('essentials::lang.requests'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="requests_table">
                            <thead>
                                <tr>
                                    <th>@lang('followup::lang.request_number')</th>
                                    <th>@lang('followup::lang.worker_name')</th>
                                    <th>@lang('followup::lang.eqama_number')</th>
                   
                                    <th>@lang('followup::lang.request_type')</th>
                                    <th>@lang('followup::lang.request_date')</th>
                                    <th>@lang('followup::lang.status')</th>
                                    <th>@lang('followup::lang.note')</th>
                         


                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>


        </div>



</section>
<!-- /.content -->
@stop

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            var requests_table = $('#requests_table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('essentials_word_cards_dashboard') }}"
                },

                columns: [

                    {
                        data: 'request_no'
                    },

                    {
                        data: 'user'
                    },
                    {
                        data: 'id_proof_number'
                    },
                
                    {
                        data: 'request_type_id',
                        render: function(data, type, row) {
                            if (data === 'exitRequest') {
                                return '@lang('followup::lang.exitRequest')';

                            } else if (data === 'returnRequest') {
                                return '@lang('followup::lang.returnRequest')';
                            } else if (data === 'escapeRequest') {
                                return '@lang('followup::lang.escapeRequest')';
                            } else if (data === 'advanceSalary') {
                                return '@lang('followup::lang.advanceSalary')';
                            } else if (data === 'leavesAndDepartures') {
                                return '@lang('followup::lang.leavesAndDepartures')';
                            } else if (data === 'atmCard') {
                                return '@lang('followup::lang.atmCard')';
                            } else if (data === 'residenceRenewal') {
                                return '@lang('followup::lang.residenceRenewal')';
                            } else if (data === 'workerTransfer') {
                                return '@lang('followup::lang.workerTransfer')';
                            } else if (data === 'residenceCard') {
                                return '@lang('followup::lang.residenceCard')';
                            } else if (data === 'workInjuriesRequest') {
                                return '@lang('followup::lang.workInjuriesRequest')';
                            } else if (data === 'residenceEditRequest') {
                                return '@lang('followup::lang.residenceEditRequest')';
                            } else if (data === 'baladyCardRequest') {
                                return '@lang('followup::lang.baladyCardRequest')';
                            } else if (data === 'mofaRequest') {
                                return '@lang('followup::lang.mofaRequest')';
                            } else if (data === 'insuranceUpgradeRequest') {
                                return '@lang('followup::lang.insuranceUpgradeRequest')';
                            } else if (data === 'chamberRequest') {
                                return '@lang('followup::lang.chamberRequest')';
                            } else if (data === 'cancleContractRequest') {
                                return '@lang('followup::lang.cancleContractRequest')';
                            } else if (data === 'WarningRequest') {
                                return '@lang('followup::lang.WarningRequest')';
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'status',

                    },
                    {
                        data: 'note'
                    },


                ],
            });


        });
    </script>
<script>
    function redirectToExpiredResidencies() {
        window.location.href = "{{ route('expired.residencies') }}";
    }

    function redirectToAllEndedResidency() {
       
         window.location.href = "{{ route('all.expired.residencies') }}";
    }

    function redirectTolatevacation()
    {  window.location.href = "{{ route('late_for_vacation') }}";}

    function final_visa()
    {  window.location.href = "{{ route('final_visa_index') }}";}
</script>
@endsection
