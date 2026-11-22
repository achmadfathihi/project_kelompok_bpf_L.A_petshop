<footer class="ps-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('beranda') }}" class="ps-brand ps-brand-light">
                    <span class="ps-brand-mark"><svg width="26" height="26"><use href="#i-paw"/></svg></span>
                    <span><span class="ps-brand-name">L.A Petshop</span><span class="ps-brand-sub">Rumbai</span></span>
                </a>
                <p class="mt-3 mb-0">Toko kebutuhan hewan peliharaan di Rumbai, Pekanbaru. Cek stok dari rumah, pesan, dan kami antar lewat ojek online.</p>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Belanja</h6>
                <ul>
                    <li><a href="{{ route('beranda') }}">Katalog Produk</a></li>
                    @auth
                        @if (auth()->user()->isRole('pelanggan'))
                            <li><a href="{{ route('keranjang.index') }}">Keranjang</a></li>
                            <li><a href="{{ route('pesanan.index') }}">Pesanan Saya</a></li>
                            <li><a href="{{ route('poin.index') }}">Poin Saya</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}">Masuk</a></li>
                        <li><a href="{{ route('register') }}">Daftar Member</a></li>
                    @endauth
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h6>Jam Buka</h6>
                <p class="mb-1">Setiap hari</p>
                <p class="mb-0 text-white fw-bold">07.00 - 22.40 WIB</p>
                <p class="small mt-2 mb-0">Pesanan di luar jam buka diproses mulai pukul 07.00.</p>
            </div>

            <div class="col-lg-3">
                <h6>Hubungi Kami</h6>
                <ul>
                    @if ($tokoTelp)
                        <li><svg class="ps-ico" width="16" height="16"><use href="#i-phone"/></svg>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $tokoTelp) }}">{{ $tokoTelp }}</a></li>
                    @endif
                    <li><svg class="ps-ico" width="16" height="16"><use href="#i-pin"/></svg> Rumbai, Pekanbaru</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="ps-footer-bottom">
        <div class="container py-3 d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ now()->year }} L.A Petshop Rumbai. Semua hak dilindungi.</span>
            <span>Free HTML Template by <a href="https://templatesjungle.com/" target="_blank" rel="noopener">TemplatesJungle</a></span>
        </div>
    </div>
</footer>