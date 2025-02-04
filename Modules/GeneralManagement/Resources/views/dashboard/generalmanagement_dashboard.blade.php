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
                    <a href="{{ route($request['route'], $request['params']) }}" class="card-link">
                        <div class="card-counter primary" style="background-color:#fbbc16;">
                            <div class="card-left-main">
                                <i class="fa {{ $request['icon'] }}"></i>
                            </div>
                            <div class="card-center-main">
                                <span class="count-name">{{ $request['title'] }}</span>
                            </div>
                            <div class="card-right-main">
                                <span class="count-numbers">{{ $request['count'] }}</span>
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