@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'today_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">

                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">{{ __('request.today_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $today_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'pending_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">{{ __('request.pending_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $pending_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'completed_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">{{ __('request.completed_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $completed_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'all']) }}">
                    <div class="col-md-3">
                        <div class="custom_card">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 style="color:#fff">{{ __('request.all_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 style="color:#fff">{{ $all_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>


            </div>
            <br>

        </div>
    </section>

    <!-- Main content -->
    <section class="content">




    </section>
@endsection
