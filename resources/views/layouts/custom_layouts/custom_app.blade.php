@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ Session::get('business.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    @include('layouts.partials.css')
    @include('layouts.custom_layouts.custom_partials.custom_css')

    @yield('css')
    <style>
        *,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Cairo', sans-serif;
            color: "black";
            font-weight: bold;
            /* font-size: 15px */
        }

        .fa-folder:before {
            color: #ffd400 !important;
            /* background-color:#ffd400; */
        }

        .fa-arrow-alt-circle-right:before {
            color: #3936f5;
        }

        body {
            min-height: 100vh;
            background-color: #243949;
            /* color: #000000; */
            font-weight: bold;

            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.12'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .navbar-default {
            background-color: transparent;
            border: none;

        }

        .navbar-static-top {
            margin-bottom: 19px;

        }

        /* .option::after {
            color: #121212;
            font-weight: bold;
        } */

        .navbar-default .navbar-nav>li>a {
            color: #303030;
            font-weight: 600;
            font-size: 15px;
        }

        .navbar-default .navbar-nav>li>a:hover {
            color: #bba2a2;

        }

        .navbar-default .navbar-brand {
            color: #0f0f0f;

        }

        .checkbox label,
        .checkbox-inline,
        .radio label,
        .radio-inline {

            font-weight: bold !important;
        }
    </style>
    <script src='//fw-cdn.com/11549296/4203905.js' chat='true'></script>
</head>

<body
    class="@if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif">
    <div class="wrapper thetop" style=" background-color: #12142e;">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>

        @if (!$pos_layout)
            @include('layouts.partials.header')
            @include('layouts.custom_layouts.custom_partials.custom_sidebar')
        @else
            @include('layouts.partials.header-pos')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="@if (!$pos_layout) content-wrapper @endif">
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <!-- Add currency related field-->
            {{-- <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
            <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
            <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
            <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
            <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') }}">
            <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
            <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}"> --}}
            <!-- End of currency related field-->
            @can('view_export_buttons')
                <input type="hidden" id="view_export_buttons">
            @endcan
            @if (isMobile())
                <input type="hidden" id="__is_mobile">
            @endif
            @if (session('status'))
                <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                    data-msg="{{ session('status.msg') }}">
            @endif
            @yield('content')

            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>

        </div>
        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->
        {{-- @include('layouts.custom_layouts.custom_footer') --}}
        @include('layouts.partials.footer')

        {{-- @if (!$pos_layout)
            @include('layouts.partials.footer')
        @else
            @include('layouts.partials.footer_pos')
        @endif --}}

        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
    </div>

    @if (!empty($__additional_html))
        {!! $__additional_html !!}
    @endif

    @include('layouts.partials.javascripts')

    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    @if (!empty($__additional_views) && is_array($__additional_views))
        @foreach ($__additional_views as $additional_view)
            @includeIf($additional_view)
        @endforeach
    @endif
</body>

</html>
