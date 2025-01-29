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
        height: 100px;
        border-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease-in-out;
        overflow: hidden;
        border: 1px solid #ddd;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 400;
    }

    .card-counter:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
    }

    .card-counter.primary {
        background-color: #37479a;
        color: #fff;
    }

    .card-left {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
    }

    .card-left i {
        font-size: clamp(1em, 2vw, 1.5em);
        font-weight: bold;
        color: white;
    }

    .card-center {
        flex-grow: 1;
        text-align: center;
        padding-left: 10px;
        font-weight: bold;
    }

    .card-right {
        font-size: clamp(18px, 3vw, 20px);
        font-weight: 700;
        text-align: right;
        min-width: 60px;
        color: #fabc17;
    }

    .card-counter .count-name {
        font-size: clamp(12px, 3vw, 16px);
        font-weight: bold;
        text-transform: capitalize;
        opacity: 0.85;
        color: #ffffff;
    }

    .card-counter .count-numbers {
        font-size: 24px;
        font-weight: 700;
        color: #ffffff;
    }

    /* Responsive Styles */
    @media (max-width: 1200px) {
        .card-left i {
            font-size: 2.2em;
        }

        .card-right {
            font-size: 30px;
        }

        .card-counter .count-name {
            font-size: 14px;
        }

        .card-counter .count-numbers {
            font-size: 20px;
        }
    }

    @media (max-width: 768px) {
        .card-counter {
            height: 90px;
            padding: 15px;
        }

        .card-left i {
            font-size: 1.8em;
        }

        .card-right {
            font-size: 24px;
        }

        .card-counter .count-name {
            font-size: 12px;
        }

        .card-counter .count-numbers {
            font-size: 18px;
        }
    }

    @media (max-width: 576px) {
        .card-counter {
            flex-direction: column;
            text-align: center;
            height: auto;
            padding: 10px;
        }

        .card-left {
            margin-bottom: 10px;
        }

        .card-left i {
            font-size: 1.5em;
        }

        .card-right {
            font-size: 20px;
        }

        .card-counter .count-name {
            font-size: 10px;
        }

        .card-counter .count-numbers {
            font-size: 16px;
        }
    }

    /* Main Card Styling for Counter */
    .card-counter-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: clamp(10px, 2vw, 20px);
        background-color: #fbbc16;
        color: #070505;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none;
    }

    .card-counter-main:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .card-left-main {
        display: flex;
        align-items: center;
        justify-content: center;
        width: clamp(30px, 6vw, 40px);
    }

    .card-left-main i {
        font-size: clamp(1.2rem, 2vw, 2rem);
        color: #070505;
    }

    .card-center-main {
        flex-grow: 1;
        text-align: center;
        font-weight: bold;
        color: #070505;
        font-size: clamp(16px, 2.5vw, 22px);
    }

    .card-right-main {
        text-align: right;
    }

    .card-right-main .count-numbers {
        font-size: clamp(16px, 3vw, 20px);
        font-weight: 600;
        color: #070505;
    }

    .card-center-main .count-name {
        font-size: clamp(16px, 2.5vw, 20px);
        font-weight: 600;
        color: #070505;
    }

    /* Optional Responsive Adjustments */
    @media (max-width: 768px) {
        .card-counter-main {
            flex-direction: column;
            text-align: center;
            padding: 15px;
        }

        .card-left-main {
            margin-bottom: 10px;
        }

        .card-right-main {
            text-align: center;
        }
    }
</style>