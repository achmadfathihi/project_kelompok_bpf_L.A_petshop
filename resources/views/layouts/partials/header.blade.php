@php
    $user       = auth()->user();
    $pelanggan  = $user && $user->isRole('pelanggan');
    $jumlahKeranjang = $pelanggan ? (int) array_sum(session('cart', [])) : 0;
@endphp

<div class="ps-topbar">
    <div class="container d-flex flex-wrap justify-content-between gap-2 py-2">
        <span><svg class="ps-ico" width="15" height="15"><use href="#i-clock"/></svg> Buka setiap hari 07.00 - 22.40 WIB</span>
        <span class="d-none d-sm-inline">
            @if ($tokoTelp)
                <svg class="ps-ico" width="15" height="15"><use href="#i-phone"/></svg>
                Hubungi toko: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $tokoTelp) }}">{{ $tokoTelp }}</a>
                &nbsp;·&nbsp;
            @endif
            <svg class="ps-ico" width="15" height="15"><use href="#i-pin"/></svg> Rumbai, Pekanbaru
        </span>
    </div>
</div>

<header class="ps-header">
    <div class="container">
        <div class="row align-items-center g-3 py-3">
            <div class="col-6 col-lg-3">
                <a href="{{ route('beranda') }}" class="ps-brand" aria-label="{{ config('app.name') }}">
                    <span class="ps-brand-mark"><svg width="26" height="26"><use href="#i-paw"/></svg></span>
                    <span><span class="ps-brand-name">L.A Petshop</span><span class="ps-brand-sub">Rumbai</span></span>
                </a>
            </div>

            <div class="col-12 col-lg-5 order-3 order-lg-2">
                <form method="GET" action="{{ route('beranda') }}" class="ps-search" role="search">
                    <select name="kategori" class="d-none d-sm-block" aria-label="Kategori">
                        <option value="">Semua kategori</option>
                        @foreach ($menuCategories as $c)
                            <option value="{{ $c->id }}" @selected(request('kategori') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari makanan, vitamin, pasir..." aria-label="Cari produk">
                    <button type="submit" aria-label="Cari"><svg width="20" height="20"><use href="#i-search"/></svg></button>
                </form>
            </div>

            <div class="col-6 col-lg-4 order-2 order-lg-3">
                <ul class="navbar-nav ps-actions flex-row justify-content-end align-items-center gap-2">
                    @guest
                        <li class="nav-item"><a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a></li>
                    @endguest

                    @auth
                        @include('layouts.partials.bell')

                        @if ($pelanggan)
                            <li class="nav-item">
                                <a class="ps-iconbtn" href="{{ route('keranjang.index') }}" aria-label="Keranjang belanja">
                                    <svg width="22" height="22"><use href="#i-cart"/></svg>
                                    @if ($jumlahKeranjang > 0)
                                        <span class="ps-count">{{ $jumlahKeranjang > 99 ? '99+' : $jumlahKeranjang }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="ps-account" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
                                <span class="ps-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                <span class="d-none d-xl-inline">{{ \Illuminate\Support\Str::limit($user->name, 14) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ $user->name }}</h6></li>
                                @if ($pelanggan)
                                    <li><a class="dropdown-item" href="{{ route('profil.edit') }}">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="{{ route('pesanan.index') }}">Pesanan Saya</a></li>
                                    <li><a class="dropdown-item" href="{{ route('poin.index') }}">Poin Saya</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('home') }}">Ke Area Staf</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>

    <div class="border-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg ps-nav px-0" aria-label="Menu utama">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-utama" aria-controls="nav-utama" aria-expanded="false" aria-label="Buka menu">
                    <svg width="20" height="20"><use href="#i-menu"/></svg> <span class="ms-1 fw-semibold">Menu</span>
                </button>
                <div class="collapse navbar-collapse" id="nav-utama">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('beranda') && ! request('kategori') ? 'active' : '' }}" href="{{ route('beranda') }}">Beranda</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request('kategori') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategori</a>
                            <ul class="dropdown-menu">
                                @foreach ($menuCategories as $c)
                                    <li><a class="dropdown-item" href="{{ route('beranda', ['kategori' => $c->id]) }}#katalog">{{ $c->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        @if ($pelanggan)
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('pesanan.*') ? 'active' : '' }}" href="{{ route('pesanan.index') }}">Pesanan Saya</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('poin.*') ? 'active' : '' }}" href="{{ route('poin.index') }}">Poin Saya</a></li>
                        @elseif ($user)
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Area Staf</a></li>
                        @endif
                    </ul>
                    <span class="navbar-text small d-none d-lg-inline">Pesan dari rumah, kami antar lewat ojek online.</span>
                </div>
            </nav>
        </div>
    </div>
</header>