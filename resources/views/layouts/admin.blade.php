<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link href="{{ asset('argon/img/brand/favicon.png') }}" rel="icon" type="image/png">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="{{ asset('argon/vendor/nucleo/css/nucleo.css') }}" rel="stylesheet">
    <link href="{{ asset('argon/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('argon/css/argon.css') }}" rel="stylesheet">
    <style>
        @media print {
            html, body, .main-content { background: #fff !important; }
            #sidenav-main, #navbar-main, .header, .navbar, .no-print, footer { display: none !important; }
            .main-content { margin-left: 0 !important; }
            .container-fluid { padding-left: 0 !important; padding-right: 0 !important; }
            .mt--7 { margin-top: 0 !important; }
            .bg-gradient-primary, .bg-gradient-default { background: #fff !important; }
            .card { box-shadow: none !important; border: 1px solid #CCC !important; background: #fff !important; }
        }
    </style>
    @stack('css')
</head>
<body>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    @include('layouts.admin.sidebar')

    <div class="main-content">
        @include('layouts.admin.topbar')
        @yield('content')
    </div>

    <script src="{{ asset('argon/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('argon/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    @stack('js')
    <script src="{{ asset('argon/js/argon.js') }}"></script>
</body>
</html>