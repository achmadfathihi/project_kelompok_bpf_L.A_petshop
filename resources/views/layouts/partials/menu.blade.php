<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('beranda') }}">{{ config('app.name') }}</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-utama"
                aria-controls="menu-utama" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu-utama">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}">Katalog</a></li>

                @auth
                    @if (auth()->user()->isRole('pelanggan'))
                        @php $jumlahKeranjang = (int) array_sum(session('cart', [])); @endphp
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('keranjang.index') }}">
                                Keranjang
                                @if ($jumlahKeranjang > 0)
                                    <span class="badge text-bg-light ms-1">{{ $jumlahKeranjang }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('pesanan.index') }}">Pesanan Saya</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('poin.index') }}">Poin Saya</a></li>
                    @endif

                    @if (auth()->user()->isRole('karyawan', 'owner'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('karyawan.home') }}">Karyawan</a></li>
                    @endif
                    @if (auth()->user()->isRole('kasir', 'owner'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('kasir.home') }}">Kasir</a></li>
                    @endif
                    @if (auth()->user()->isRole('owner'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('owner.home') }}">Owner</a></li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav align-items-lg-center">
                @auth
                @include('layouts.partials.bell')
                    <li class="nav-item">
                        @if (auth()->user()->isRole('pelanggan'))
                            <a class="nav-link" href="{{ route('profil.edit') }}">{{ auth()->user()->name }}</a>
                        @else
                            <span class="navbar-text me-3">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                        @endif
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm ms-lg-2">Keluar</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>