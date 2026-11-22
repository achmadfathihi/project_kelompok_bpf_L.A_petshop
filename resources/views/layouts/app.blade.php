<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1F4E5F">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/petshop.css') }}?v={{ @filemtime(public_path('css/petshop.css')) }}" rel="stylesheet">
    @stack('css')
</head>
<body>
    @include('layouts.partials.icons')
    <a class="visually-hidden-focusable" href="#konten">Lewati ke konten</a>

    @include('layouts.partials.header')

    <main id="konten">
        @hasSection('wide')
            @if (session('status') || $errors->any())
                <div class="container pt-3">@include('layouts.partials.flash')</div>
            @endif
            @yield('wide')
        @else
            <div class="container py-4">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        @endif
    </main>

    @include('layouts.partials.footer')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('js')
</body>
</html>