<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200&display=swap" rel="stylesheet">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>

    @include('layouts.partials.css')

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
    </style>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif
    <div class="container-fluid">
        <div class="row eq-height-row">
            <div class="col-md-6 col-sm-6 hidden-xs left-col eq-height-col" style="padding: 0px">
                <div class="left-col-content login-header">
                    <div id="myCarousel" class="carousel slide" data-ride="carousel">
                        <!-- Indicators -->
                        <ol class="carousel-indicators">
                            <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                            <li data-target="#myCarousel" data-slide-to="1"></li>
                            <li data-target="#myCarousel" data-slide-to="2"></li>
                        </ol>

                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                            <div class="item active"
                                style="background-image:url(/uploads/slide1.png);width:100%;height: 64rem;background-size: cover;    background-color: #000;
                                opacity: var(--header-image-opacity);">
                                <div style="margin: 0px">
                                    <h2 style="color: #f5c21c;margin: 0px;padding-top:250px">@lang('lang_v1.emdadatalatta_comp')</h2>
                                    <h3 style="color: white"> @lang('lang_v1.provide_gifts') </h3>
                                </div>


                            </div>

                            <div class="item"
                                style="background-image:url(/uploads/emdatat_flaq.webp);width:100%;height: 64rem;background-size: cover;    background-color: #000;
                                opacity: var(--header-image-opacity);">
                                <div style="margin: 0px">

                                </div>


                            </div>
                            <div class="item"
                                style="background-image:url(/uploads/slide3.webp);width:100%;height: 64rem;background-size: cover;    background-color: #000;
                            opacity: var(--header-image-opacity);">
                                <div style="margin: 0px">

                                </div>


                            </div>

                        </div>

                        <!-- Left and right controls -->
                        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                            <span class="glyphicon glyphicon-chevron-left"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#myCarousel" data-slide="next">
                            <span class="glyphicon glyphicon-chevron-right"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>



                    {{-- <div style="margin-top: 50%;">
                        <a href="/">
                            @if (file_exists(public_path('uploads/logo.png')))
                                <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                            @else
                                {{ config('app.name', 'ultimatePOS') }}
                            @endif
                        </a>
                        <br />
                        @if (!empty(config('constants.app_title')))
                            <small>{{ config('constants.app_title') }}</small>
                        @endif
                    </div> --}}
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 right-col eq-height-col">
                <div class="row" style="background: white;">
                    <div class="col-md-3 col-xs-4" style="text-align: left;">
                        <select class="form-control input-sm" id="change_lang"
                            style="margin: 10px;    width: 20rem;
                        padding: 6px;
                        border-radius: 3px;">
                            @foreach (config('constants.langs') as $key => $val)
                                <option value="{{ $key }}" @if ((empty(request()->lang) && config('app.locale') == $key) || request()->lang == $key) selected @endif>
                                    {{ $val['full_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9 col-xs-8" style="text-align: right;padding-top: 10px;">
                        @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                            <!-- Register Url -->
                            @if (config('constants.allow_registration'))
                                {{-- <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif"
                                    class="btn bg-maroon btn-flat"><b>{{ __('business.not_yet_registered') }}</b>
                                    {{ __('business.register_now') }}</a> --}}
                                <!-- pricing url -->
                                @if (Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                                    &nbsp; <a
                                        href="{{ action([\Modules\Superadmin\Http\Controllers\PricingController::class, 'index']) }}">@lang('superadmin::lang.pricing')</a>
                                @endif
                            @endif
                        @endif
                        @if ($request->segment(1) != 'login')
                            &nbsp; &nbsp;<span class="text-white">{{ __('business.already_registered') }} </span><a
                                href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login']) }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif">{{ __('business.sign_in') }}</a>
                        @endif
                    </div>

                    @yield('content')

                </div>

            </div>

        </div>

    </div>
    <footer class="main-footer no-print" style="    text-align: center;
    margin: 0px;
    font-size: larger;">
        <small>
            {{ config('app.name', 'ultimatePOS') }} - V{{ config('author.app_version') }} | Copyright &copy;
            {{ date('Y') }} All rights reserved.
        </small>
        <small>
            <a style="margin-right:25px; margin-left:25px;" href="/privacy-policy"
                class="privacy-policy-link">@lang('lang_v1.privacy_policy')</a>

        </small>
    </footer>
    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>

    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>
