@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="row widget-statistic">

            <div class="row">
                @foreach ([
                ['route' => 'generalmanagement.getFilteredRequests', 'params' => ['filter' => 'today_requests'], 'title'
                => __('request.today_requests'), 'count' => $today_requests ?? 0, 'icon' => 'fa-calendar-day'],
                ['route' => 'generalmanagement.getFilteredRequests', 'params' => ['filter' => 'pending_requests'],
                'title' => __('request.pending_requests'), 'count' => $pending_requests ?? 0, 'icon' =>
                'fa-hourglass-half'],
                ['route' => 'generalmanagement.getFilteredRequests', 'params' => ['filter' => 'completed_requests'],
                'title' => __('request.completed_requests'), 'count' => $completed_requests ?? 0, 'icon' =>
                'fa-check-circle'],
                ['route' => 'generalmanagement.getFilteredRequests', 'params' => ['filter' => 'all'], 'title' =>
                __('request.all_requests'), 'count' => $all_requests ?? 0, 'icon' => 'fa-list']
                ] as $request)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <a href="{{ route($request['route'], $request['params']) }}">
                        <div class="card-counter primary"
                            style="background-color: #fbbc16; color: rgb(7, 5, 5); border-radius: 8px; padding: 20px;">
                            <div class="card-left">
                                <i class="fa {{ $request['icon'] }}" style="font-size: 2rem; color: rgb(7, 5, 5);"></i>
                                <!-- Unique icon -->
                            </div>
                            <div class="card-center-main" style="text-align: center;">
                                <span class="count-name" style="font-weight: bold; color: rgb(7, 5, 5);">{{
                                    $request['title'] }}</span>
                                <!-- Title -->
                            </div>
                            <div class="card-right-main" style="text-align: right;">
                                <span class="count-numbers" style=" font-size: 22px;
                                 font-weight: 600;  color: rgb(7, 5, 5);">{{
                                    $request['count'] }}</span>
                                <!-- Count -->
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>


            @include('generalmanagement::dashboard.pending_requests_with_dept')


        </div>
        <br>

    </div>
</section>

<!-- Main content -->
<section class="content">




</section>
@endsection