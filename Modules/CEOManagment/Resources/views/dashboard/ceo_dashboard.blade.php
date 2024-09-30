@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="row widget-statistic">
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'today_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">

                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.today_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $today_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'pending_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.pending_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $pending_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'completed_requests']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.completed_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $completed_requests ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('ceomanagment.getFilteredRequests', ['filter' => 'all']) }}">
                    <div class="col-md-3">
                        <div class="custom_card custom_card_requests">
                            <div class="widget widget-one_hybrid widget-engagement">
                                <div class="widget-heading">
                                    <div class="w-title">
                                        <div>
                                            <p class="w-value"></p>
                                            <h5 class="custom_card_requests_h5">{{ __('request.all_requests') }}</h5>
                                        </div>
                                        <div>
                                            <p class="w-value"></p>
                                            <h4 class="custom_card_requests_h5">{{ $all_requests ?? 0 }}</h4>
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
