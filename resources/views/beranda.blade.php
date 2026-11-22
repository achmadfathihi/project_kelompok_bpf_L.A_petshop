@extends('layouts.app')
@section('title', 'Katalog Produk')

@section('wide')
@php
    $tamu      = ! auth()->check();
    $pelanggan = auth()->check() && auth()->user()->isRole('pelanggan');
    $pembeli   = $tamu || $pelanggan;
    $isiKeranjang = session('cart', []);
    $adaFilter = request()->filled('q') || request()->filled('kategori');
    $kategoriAktif = $categories->firstWhere('id', (int) request('kategori'));

    $ikon = fn (string $nama) => match (true) {
        str_contains(strtolower($nama), 'pasir')   => 'i-box',
        str_contains(strtolower($nama), 'vitamin') => 'i-pill',
        str_contains(strtolower($nama), 'kucing')  => 'i-cat',
        str_contains(strtolower($nama), 'anjing')  => 'i-dog',
        default                                    => 'i-paw',
    };

    $langkah = 'Rp' . number_format(\App\Models\Order::POINT_STEP, 0, ',', '.');
    $hadiah  = number_format(\App\Models\Order::POINT_REWARD, 0, ',', '.');

    $slides = [
        [
            'judul'  => 'Cek stok dulu, baru berangkat.',
            'teks'   => 'Lihat ketersediaan makanan, vitamin, dan perlengkapan hewan kesayangan Anda dari rumah.',
            'tombol' => 'Lihat Katalog',
            'url'    => '#katalog',
        ],
        [
            'judul'  => 'Pesan dari rumah, kami antar.',
            'teks'   => 'Barang berat seperti pasir kucing diantar lewat ojek online. Tidak perlu repot membawanya pulang.',
            'tombol' => $tamu ? 'Daftar Gratis' : 'Mulai Belanja',
            'url'    => $tamu ? route('register') : '#katalog',
        ],
        [
            'judul'  => 'Kumpulkan poin, hemat belanja.',
            'teks'   => "Setiap kelipatan {$langkah} dapat {$hadiah} poin, dan {$hadiah} poin sama dengan potongan Rp{$hadiah}.",
            'tombol' => $tamu ? 'Jadi Member' : ($pelanggan ? 'Lihat Poin Saya' : 'Lihat Katalog'),
            'url'    => $tamu ? route('register') : ($pelanggan ? route('poin.index') : '#katalog'),
        ],
    ];
@endphp

@unless ($adaFilter)
<section class="ps-hero" aria-label="Informasi toko">
    <div id="hero" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6500">
        <div class="carousel-indicators">
            @foreach ($slides as $i => $s)
                <button type="button" data-bs-target="#hero" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"
                        aria-label="Slide {{ $i + 1 }}" @if ($i === 0) aria-current="true" @endif></button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach ($slides as $i => $s)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <div class="container">
                        <div class="row align-items-center g-4 ps-hero-row">
                            <div class="col-md-7 col-lg-6">
                                <span class="ps-eyebrow">{{ config('app.name') }}</span>
                                <h2 class="ps-hero-title">{{ $s['judul'] }}</h2>
                                <p class="ps-hero-text">{{ $s['teks'] }}</p>
                                <a href="{{ $s['url'] }}" class="btn btn-primary btn-lg">{{ $s['tombol'] }}</a>
                            </div>
                            <div class="col-md-5 col-lg-6 d-none d-md-block text-center">
                                @include('layouts.partials.hero-art')
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-3">
            @foreach ([
                ['i-check', 'Stok terkini', 'Lihat stok sebelum berangkat'],
                ['i-truck', 'Antar ojek online', 'Dari toko langsung ke rumah'],
                ['i-gift', 'Poin member', 'Dapat poin setiap belanja'],
                ['i-clock', 'Buka sampai 22.40', 'Setiap hari, mulai 07.00 WIB'],
            ] as [$ic, $judul, $ket])
                <div class="col-6 col-lg-3">
                    <div class="ps-feature">
                        <div class="ps-feature-icon"><svg width="24" height="24"><use href="#{{ $ic }}"/></svg></div>
                        <div><strong>{{ $judul }}</strong><span>{{ $ket }}</span></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endunless

@if ($categories->isNotEmpty())
<section class="py-4">
    <div class="container">
        <div class="ps-section-title">
            <div>
                <h2 class="h3 mb-1">Belanja per Kategori</h2>
                <p class="text-muted mb-0">Pilih kategori untuk menyaring produk.</p>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
            <div class="col">
                <a href="{{ route('beranda') }}#katalog" class="ps-cat {{ request('kategori') ? '' : 'active' }}">
                    <div class="ps-cat-icon"><svg width="28" height="28"><use href="#i-paw"/></svg></div>
                    <strong>Semua</strong><small>Semua produk</small>
                </a>
            </div>
            @foreach ($categories as $c)
                <div class="col">
                    <a href="{{ route('beranda', ['kategori' => $c->id]) }}#katalog"
                       class="ps-cat {{ (int) request('kategori') === $c->id ? 'active' : '' }}">
                        <div class="ps-cat-icon"><svg width="28" height="28"><use href="#{{ $ikon($c->name) }}"/></svg></div>
                        <strong>{{ $c->name }}</strong><small>{{ $c->products_count }} produk</small>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section id="katalog" class="py-4 py-lg-5">
    <div class="container">
        <div class="ps-section-title">
            <div>
                <h2 class="h3 mb-1">{{ $adaFilter ? 'Hasil Pencarian' : 'Semua Produk' }}</h2>
                <p class="text-muted mb-0">
                    @if ($adaFilter) {{ $products->total() }} produk ditemukan.
                    @else Cek status stok sebelum Anda memesan.
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('beranda') }}" class="d-flex align-items-center gap-2">
                @if (request()->filled('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                @if (request()->filled('kategori')) <input type="hidden" name="kategori" value="{{ request('kategori') }}"> @endif
                <label for="urut" class="small text-muted mb-0">Urutkan</label>
                <select id="urut" name="urut" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="nama" @selected($urut === 'nama')>Nama A-Z</option>
                    <option value="termurah" @selected($urut === 'termurah')>Harga termurah</option>
                    <option value="termahal" @selected($urut === 'termahal')>Harga termahal</option>
                </select>
            </form>
        </div>

        @if ($adaFilter)
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                @if (request()->filled('q')) <span class="ps-chip">Kata kunci: <strong>{{ request('q') }}</strong></span> @endif
                @if ($kategoriAktif) <span class="ps-chip">Kategori: <strong>{{ $kategoriAktif->name }}</strong></span> @endif
                <a href="{{ route('beranda') }}#katalog" class="small">Hapus filter</a>
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="text-center py-5">
                <svg width="56" height="56" class="text-muted mb-2"><use href="#i-search"/></svg>
                <h3 class="h5">Produk tidak ditemukan</h3>
                <p class="text-muted">Coba kata kunci lain atau pilih kategori yang berbeda.</p>
                <a href="{{ route('beranda') }}" class="btn btn-outline-primary">Lihat semua produk</a>
            </div>
        @else
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4">
                @foreach ($products as $p)
                    <div class="col">
                        <article class="ps-product {{ $p->stock <= 0 ? 'is-out' : '' }}">
                            <div class="ps-product-media">
                                <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" class="ps-product-img" loading="lazy">
                                @if ($p->stock_status === 'habis')
                                    <span class="ps-status ps-status-out">Habis</span>
                                @elseif ($p->stock_status === 'menipis')
                                    <span class="ps-status ps-status-low">Stok menipis</span>
                                @else
                                    <span class="ps-status ps-status-ok">Tersedia</span>
                                @endif
                            </div>
                            <div class="ps-product-body">
                                <div class="ps-product-cat">{{ $p->category->name }}</div>
                                <h3 class="ps-product-name">{{ $p->name }}</h3>
                                <div class="ps-product-meta">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</div>

                                <div class="ps-product-foot">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <span class="ps-price">@rp($p->price)</span>
                                        <span class="ps-stock">{{ $p->stock > 0 ? 'Stok ' . $p->stock : 'Stok kosong' }}</span>
                                    </div>

                                    @if ($pembeli)
                                        @if ($p->stock <= 0)
                                            <button class="btn btn-secondary btn-sm w-100" disabled>Habis</button>
                                        @elseif ($tamu)
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">Masuk untuk membeli</a>
                                        @else
                                            <button type="button" class="btn btn-primary btn-sm w-100"
                                                    data-bs-toggle="modal" data-bs-target="#modal-keranjang"
                                                    data-id="{{ $p->id }}"
                                                    data-name="{{ $p->name }}"
                                                    data-detail="{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}"
                                                    data-price="{{ $p->price }}"
                                                    data-stock="{{ $p->stock }}"
                                                    data-incart="{{ $isiKeranjang[$p->id] ?? 0 }}"
                                                    data-photo="{{ $p->photo_url }}">
                                                <svg class="ps-ico" width="16" height="16"><use href="#i-cart"/></svg> Keranjang
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</section>

@if ($pelanggan)
    <div class="modal fade" id="modal-keranjang" tabindex="-1" aria-labelledby="judul-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="form-keranjang" action="#" class="modal-content" style="border-radius:var(--ps-radius)">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="judul-modal">Tambah ke Keranjang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex mb-3">
                        <img id="km-foto" src="" alt="" class="rounded-3 me-3" style="width:96px;height:96px;object-fit:cover">
                        <div>
                            <div class="fw-bold" id="km-nama"></div>
                            <div class="small text-muted" id="km-detail"></div>
                            <div class="mt-1 ps-price" id="km-harga"></div>
                            <div class="small text-muted">Stok: <span id="km-stok"></span></div>
                        </div>
                    </div>

                    <label class="form-label" for="km-qty">Jumlah</label>
                    <div class="input-group" style="max-width:210px">
                        <button type="button" class="btn btn-outline-primary" id="km-kurang" aria-label="Kurangi">&minus;</button>
                        <input type="number" name="qty" id="km-qty" class="form-control text-center" value="1" min="1" required>
                        <button type="button" class="btn btn-outline-primary" id="km-tambah" aria-label="Tambah">+</button>
                    </div>
                    <div class="small text-muted mt-2" id="km-info"></div>

                    <div class="mt-3">Subtotal: <strong id="km-subtotal"></strong></div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="km-simpan">Simpan ke Keranjang</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal   = document.getElementById('modal-keranjang');
            var form    = document.getElementById('form-keranjang');
            var qty     = document.getElementById('km-qty');
            var simpan  = document.getElementById('km-simpan');
            var urlTemplate = {!! json_encode(route('keranjang.add', ['product' => '__ID__'])) !!};
            var harga = 0, maks = 1;

            function rupiah(n) { return 'Rp' + n.toLocaleString('id-ID'); }

            function atur(n) {
                n = parseInt(n) || 1;
                n = Math.max(1, Math.min(n, Math.max(maks, 1)));
                qty.value = n;
                document.getElementById('km-subtotal').textContent = rupiah(harga * n);
            }

            modal.addEventListener('show.bs.modal', function (e) {
                var b = e.relatedTarget;
                var stok = parseInt(b.dataset.stock);
                var diKeranjang = parseInt(b.dataset.incart) || 0;

                harga = parseInt(b.dataset.price);
                maks  = Math.max(stok - diKeranjang, 0);

                form.action = urlTemplate.replace('__ID__', b.dataset.id);
                document.getElementById('km-foto').src = b.dataset.photo;
                document.getElementById('km-nama').textContent = b.dataset.name;
                document.getElementById('km-detail').textContent = b.dataset.detail;
                document.getElementById('km-harga').textContent = rupiah(harga);
                document.getElementById('km-stok').textContent = stok;

                var info = document.getElementById('km-info');
                if (maks <= 0) {
                    info.textContent = 'Jumlah di keranjang Anda sudah mencapai stok yang tersedia.';
                    simpan.disabled = true;
                    qty.disabled = true;
                } else {
                    info.textContent = diKeranjang > 0
                        ? 'Sudah ' + diKeranjang + ' di keranjang. Maksimal tambahan ' + maks + '.'
                        : 'Maksimal ' + maks + '.';
                    simpan.disabled = false;
                    qty.disabled = false;
                    qty.max = maks;
                }
                atur(1);
            });

            document.getElementById('km-kurang').addEventListener('click', function () { atur((parseInt(qty.value) || 1) - 1); });
            document.getElementById('km-tambah').addEventListener('click', function () { atur((parseInt(qty.value) || 1) + 1); });
            qty.addEventListener('input', function () { atur(qty.value); });
        })();
    </script>
@endif
@endsection