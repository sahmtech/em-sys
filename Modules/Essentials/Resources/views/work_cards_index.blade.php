@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">

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



        <div class="row">
            <div class="col-md-12 custom_table">
                @component('components.widget', [
                    'class' => 'box-solid',
                    'title' => __('essentials::lang.requests'),
                ])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="">
                            <thead>
                                <tr>
                                    <th>@lang('essentials::lang.request_number')</th>
                                    <th>@lang('essentials::lang.worker_name')</th>
                                    <th>@lang('essentials::lang.residency_number')</th>
                                    <th>@lang('essentials::lang.request_type')</th>
                                    <th>@lang('essentials::lang.date_application')</th>
                                    <th>@lang('essentials::lang.Status')</th>
                                    <th>@lang('essentials::lang.nots')</th>
                                    <th>@lang('essentials::lang.actions')</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>


        </div>

            </div>
        </div>
           

    </section>

    <!-- Main content -->
    <section class="content">




    </section>
    <!-- /.content -->
@stop

@section('javascript')
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
