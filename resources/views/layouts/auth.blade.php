<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1F4E5F">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/petshop.css') }}?v={{ @filemtime(public_path('css/petshop.css')) }}" rel="stylesheet">
</head>
<body>
    @include('layouts.partials.icons')

    <div class="ps-auth-wrap">
        <aside class="ps-auth-side d-none d-lg-flex">
            <a href="{{ route('beranda') }}" class="ps-brand ps-brand-light">
                <span class="ps-brand-mark"><svg width="26" height="26"><use href="#i-paw"/></svg></span>
                <span><span class="ps-brand-name">L.A Petshop</span><span class="ps-brand-sub">Rumbai</span></span>
            </a>

            <div class="text-center my-4">@include('layouts.partials.hero-art')</div>

            <div>
                <h2>Belanja kebutuhan si bulu, lebih mudah.</h2>
                <ul class="ps-auth-points">
                    <li><span class="ps-feature-icon"><svg width="20" height="20"><use href="#i-check"/></svg></span> Cek stok sebelum berangkat</li>
                    <li><span class="ps-feature-icon"><svg width="20" height="20"><use href="#i-truck"/></svg></span> Diantar lewat ojek online</li>
                    <li><span class="ps-feature-icon"><svg width="20" height="20"><use href="#i-gift"/></svg></span> Kumpulkan poin di setiap belanja</li>
                </ul>
            </div>
        </aside>

        <section class="ps-auth-main">
            <div class="ps-auth-card">
                <a href="{{ route('beranda') }}" class="ps-brand d-lg-none mb-4">
                    <span class="ps-brand-mark"><svg width="26" height="26"><use href="#i-paw"/></svg></span>
                    <span><span class="ps-brand-name">L.A Petshop</span><span class="ps-brand-sub">Rumbai</span></span>
                </a>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                @yield('content')

                <p class="text-center mt-4 mb-0 small">
                    <a href="{{ route('beranda') }}">&larr; Kembali ke toko</a>
                </p>
            </div>
        </section>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>