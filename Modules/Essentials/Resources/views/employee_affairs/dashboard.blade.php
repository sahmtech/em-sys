@extends('layouts.app')


@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">


        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row ">

                <div class="col-md-3 " onclick="finsish_contract_duration()" style="cursor: pointer;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                           
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.finsish_contract_duration') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-0">{{$probation_period}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3 " onclick="finish_contracts()" style="cursor: pointer;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                             
                                <div class="w-title">
                                   
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.finsish_contract_date') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-1">{{$contract_end_date}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-md-3 " onclick="late_admission()" style="cursor: pointer;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.late_admission') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-2">{{$late_vacation}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-3 " onclick="uncomplete_profiles()" style="cursor: pointer;">
                    <div class="custom_card">
                        <div class="widget widget-one_hybrid widget-engagement">
                            <div class="widget-heading">
                                <div class="w-title">
                                    <div>
                                        <p class="w-value"></p>
                                        <h5 style="color:#fff">{{ __('essentials::lang.uncomplemete_profiles') }}</h5>
                                    </div>
                                    <div>
                                        <p class="w-value"></p>
                                        <h4 style="color:#fff" id="counter-3">{{$nullCount}}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>



      
     

        </div>
        </div>
        <div class="row">
            <div class="col-md-11 custom_table">
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
                                 
                                  
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
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
    function finsish_contract_duration() {
        window.location.href = "{{ route('finsish_contract_duration') }}";
    }

    function finish_contracts() {
       
         window.location.href = "{{ route('finish_contracts') }}";
    }

    function uncomplete_profiles()
    {  window.location.href = "{{ route('uncomplete_profiles') }}";}

    function final_visa()
    {  window.location.href = "{{ route('final_visa_index') }}";}

    function late_admission(){
        window.location.href = "{{ route('late_admission') }}";
    }
</script>
@endsection
