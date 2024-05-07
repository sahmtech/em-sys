{{-- <link rel="stylesheet" href="{{ asset('custom_css/cards.css') }}">
<link rel="stylesheet" href="{{ asset('custom_css/custom_nav.css') }}">
<link rel="stylesheet" href="{{ asset('custom_css/agent.css') }}">
<link rel="stylesheet" href="{{ asset('custom_css/genral.css') }}">
<link rel="stylesheet" href="{{ asset('custom_css/accounting.css') }}">
<link rel="stylesheet" href="{{ asset('custom_css/reports.css') }}"> --}}

<link rel="stylesheet" href="{{ asset('custom_css/cards.css') }}?v={{ filemtime(public_path('custom_css/cards.css')) }}">
<link rel="stylesheet"
    href="{{ asset('custom_css/custom_nav.css') }}?v={{ filemtime(public_path('custom_css/custom_nav.css')) }}">
<link rel="stylesheet" href="{{ asset('custom_css/agent.css') }}?v={{ filemtime(public_path('custom_css/agent.css')) }}">
<link rel="stylesheet"
    href="{{ asset('custom_css/genral.css') }}?v={{ filemtime(public_path('custom_css/genral.css')) }}">
<link rel="stylesheet"
    href="{{ asset('custom_css/accounting.css') }}?v={{ filemtime(public_path('custom_css/accounting.css')) }}">
<link rel="stylesheet"
    href="{{ asset('custom_css/reports.css') }}?v={{ filemtime(public_path('custom_css/reports.css')) }}">
