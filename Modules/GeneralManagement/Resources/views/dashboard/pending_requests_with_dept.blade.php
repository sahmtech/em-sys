@php
$departments = [
['id' => 37, 'title' => __('request.pending_requests_ceo'), 'count' => $pending_requests_ceo ?? 0],
['id' => 27, 'title' => __('request.pending_requests_housing_transport'), 'count' => $pending_requests_housing_transport
?? 0],
['id' => 26, 'title' => __('request.pending_requests_operations_business'), 'count' =>
$pending_requests_operations_business ?? 0],
['id' => 28, 'title' => __('request.pending_requests_sales'), 'count' => $pending_requests_sales ?? 0],
['id' => 29, 'title' => __('request.pending_requests_international_relations'), 'count' =>
$pending_requests_international_relations ?? 0],
['id' => 30, 'title' => __('request.pending_requests_hr'), 'count' => $pending_requests_hr ?? 0],
['id' => 31, 'title' => __('request.pending_requests_hr_applications'), 'count' => $pending_requests_hr_applications ??
0],
['id' => 32, 'title' => __('request.pending_requests_legal_affairs'), 'count' => $pending_requests_legal_affairs ?? 0],
['id' => 33, 'title' => __('request.pending_requests_finance'), 'count' => $pending_requests_finance ?? 0],
['id' => 34, 'title' => __('request.pending_requests_government_relations'), 'count' =>
$pending_requests_government_relations ?? 0],
['id' => 35, 'title' => __('request.pending_requests_personnel_affairs'), 'count' => $pending_requests_personnel_affairs
?? 0],
];
@endphp

<div class="row">
    @foreach ($departments as $department)
    <a
        href="{{ route('generalmanagement.getFilteredRequests', ['filter' => 'pending_requests', 'departmentId' => $department['id']]) }}">
        <div class="col-md-3 mt-15">
            <div class="custom_card custom_card_requests" style="background-color: #374699">
                <div class="widget widget-one_hybrid widget-engagement">
                    <div class="widget-heading">
                        <div class="w-title">
                            <div>
                                <p class="w-value"></p>
                                <h5 class="custom_card_requests_h5" style="color: aliceblue">{{ $department['title'] }}
                                </h5>
                            </div>
                            <div>
                                <p class="w-value"></p>
                                <h4 class="custom_card_requests_h5" style="color: aliceblue">{{ $department['count'] }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
    @endforeach
</div>