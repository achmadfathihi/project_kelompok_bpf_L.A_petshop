@php $user = auth()->user(); @endphp

<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
                aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand pt-0" href="{{ route('home') }}">
            <span class="h3 font-weight-bold text-primary">L. A Petshop</span>
        </a>

        {{-- Menu pengguna (tampil di layar kecil) --}}
        <ul class="nav align-items-center d-md-none">
            @include('layouts.admin.bell')
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="avatar avatar-sm rounded-circle bg-primary"><i class="ni ni-single-02"></i></span>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ $user->name }}</h6>
                    </div>
                    <a href="#" class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="ni ni-user-run"></i><span>Keluar</span>
                    </a>
                </div>
            </li>
        </ul>

        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="{{ route('home') }}"><span class="h3 font-weight-bold text-primary">L. A Petshop</span></a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main"
                                aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                            <span></span><span></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== OWNER ===== --}}
            @if ($user->isRole('owner'))
                <h6 class="navbar-heading text-muted">Owner</h6>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.home') ? 'active' : '' }}" href="{{ route('owner.home') }}">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard Owner
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.laporan.penjualan') ? 'active' : '' }}" href="{{ route('owner.laporan.penjualan') }}">
                            <i class="ni ni-chart-bar-32 text-green"></i> Laporan Penjualan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.laporan.stok') ? 'active' : '' }}" href="{{ route('owner.laporan.stok') }}">
                            <i class="ni ni-archive-2 text-info"></i> Laporan Stok
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.laporan.kedaluwarsa') ? 'active' : '' }}" href="{{ route('owner.laporan.kedaluwarsa') }}">
                            <i class="ni ni-calendar-grid-58 text-red"></i> Laporan Kedaluwarsa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.staf.*') ? 'active' : '' }}" href="{{ route('owner.staf.index') }}">
                            <i class="ni ni-badge text-orange"></i> Akun Staf
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.member.*') ? 'active' : '' }}" href="{{ route('owner.member.index') }}">
                            <i class="ni ni-single-02 text-default"></i> Daftar Member
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('owner.pengaturan.*') ? 'active' : '' }}" href="{{ route('owner.pengaturan.edit') }}">
                            <i class="ni ni-settings-gear-65 text-default"></i> Pengaturan Toko
                        </a>
                    </li>
                </ul>
            @endif

            {{-- ===== KASIR ===== --}}
            @if ($user->isRole('kasir', 'owner'))
                @php
                    $perluTindakan = \App\Models\Order::whereIn('status', ['menunggu_ongkir', 'menunggu_verifikasi'])->count();
                @endphp
                <h6 class="navbar-heading text-muted">Kasir</h6>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kasir.home') ? 'active' : '' }}" href="{{ route('kasir.home') }}">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard Kasir
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kasir.pesanan.*') ? 'active' : '' }}" href="{{ route('kasir.pesanan.index') }}">
                            <i class="ni ni-bullet-list-67 text-orange"></i> Pesanan
                            @if ($perluTindakan > 0)
                                <span class="badge badge-pill badge-danger ml-2">{{ $perluTindakan }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kasir.transaksi.*') ? 'active' : '' }}" href="{{ route('kasir.transaksi.create') }}">
                            <i class="ni ni-credit-card text-green"></i> Transaksi di Toko
                        </a>
                    </li>
                </ul>
            @endif

            {{-- ===== KARYAWAN ===== --}}
            @if ($user->isRole('karyawan', 'owner'))
                <h6 class="navbar-heading text-muted">Karyawan</h6>
                @php
                    $perluDisiapkan = \App\Models\Order::where('channel', 'online')->where('status', 'diproses')->count();
                @endphp
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.home') ? 'active' : '' }}" href="{{ route('karyawan.home') }}">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.pesanan.*') ? 'active' : '' }}" href="{{ route('karyawan.pesanan.index') }}">
                            <i class="ni ni-delivery-fast text-info"></i> Pesanan Online
                            @if ($perluDisiapkan > 0)
                                <span class="badge badge-pill badge-danger ml-2">{{ $perluDisiapkan }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.kategori.*') ? 'active' : '' }}" href="{{ route('karyawan.kategori.index') }}">
                            <i class="ni ni-tag text-info"></i> Kategori
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.produk.*') ? 'active' : '' }}" href="{{ route('karyawan.produk.index') }}">
                            <i class="ni ni-box-2 text-orange"></i> Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.stok.*') ? 'active' : '' }}" href="{{ route('karyawan.stok.index') }}">
                            <i class="ni ni-delivery-fast text-green"></i> Stok Masuk/Keluar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('karyawan.batch.*') ? 'active' : '' }}" href="{{ route('karyawan.batch.index') }}">
                            <i class="ni ni-calendar-grid-58 text-red"></i> Batch &amp; Kedaluwarsa
                        </a>
                    </li>
                </ul>
            @endif

            <hr class="my-3">
            <ul class="navbar-nav mb-md-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('beranda') }}"><i class="ni ni-shop"></i> Lihat Toko</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('akun.password*') ? 'active' : '' }}" href="{{ route('akun.password') }}">
                        <i class="ni ni-key-25"></i> Ganti Password
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="ni ni-user-run"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>