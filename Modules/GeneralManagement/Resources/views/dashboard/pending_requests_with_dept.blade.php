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
$icons = [
37 => 'fa-cogs',
27 => 'fa-home',
26 => 'fa-briefcase',
28 => 'fa-users',
29 => 'fa-globe',
30 => 'fa-user-tie',
31 => 'fa-calendar',
32 => 'fa-gavel',
33 => 'fa-calculator',
34 => 'fa-flag',
35 => 'fa-handshake'
];
@endphp

<div class="row">
    @foreach ($departments as $department)
    <div class="col-md-3">
        <a
            href="{{ route('generalmanagement.getFilteredRequests', ['filter' => 'pending_requests', 'departmentId' => $department['id']]) }}">
            <div class="card-counter primary">
                <div class="card-left">
                    <i class="fa {{ $icons[$department['id']] }}"></i> <!-- Dynamic icon based on department -->
                </div>
                <div class="card-center">
                    <span class="count-name">{{ $department['title'] }}</span> <!-- Title centered -->
                </div>
                <div class="card-right">
                    <span class="count-numbers">{{ $department['count'] }}</span> <!-- Count on the right -->
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<style>
    .card-counter {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin: 15px;
        padding: 20px 15px;
        background-color: #fff;
        height: 120px;
        border-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease-in-out;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .card-counter:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
    }

    .card-counter.primary {
        background-color: #1572e8;
        color: #fff;
    }

    .card-left {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
    }

    .card-left i {
        font-size: 2.5em;
        opacity: 1;
        /* Ensure the icon is fully visible */
        color: white;
        /* Set icon color to white */
    }



    .card-center {
        flex-grow: 1;
        text-align: center;
        padding-left: 10px;
    }





    .card-right {
        font-size: 36px;
        font-weight: 600;
        text-align: right;
        min-width: 60px;
        color: #fabc17;
    }

    .card-counter .count-name {
        font-size: 18px;
        font-weight: 500;
        text-transform: capitalize;
        opacity: 0.85;
        color: #ffffff;

    }

    .card-counter .count-numbers {
        font-size: 32px;
        font-weight: 600;
        color: #ffffff;
    }

    .card-counter .count-name {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }
</style>