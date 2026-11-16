@extends('layouts.app')
@section('title', 'Katalog Produk')

@section('content')
    @php
        $pelanggan = auth()->check() && auth()->user()->isRole('pelanggan');
        $pembeli   = ! auth()->check() || $pelanggan;   // tamu atau pelanggan
        $isiKeranjang = session('cart', []);
    @endphp

    <div class="p-4 mb-4 bg-white rounded shadow-sm">
        <h2 class="mb-1">Katalog Produk</h2>
        <p class="text-muted mb-0">Cek ketersediaan produk sebelum memesan. Toko buka setiap hari pukul 07.00 - 22.40 WIB.</p>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau merek produk">
        </div>
        <div class="col-md-4">
            <select name="kategori" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('kategori') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Cari</button>
        </div>
    </form>

    @if ($products->isEmpty())
        <div class="alert alert-info">Produk tidak ditemukan.</div>
    @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($products as $p)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $p->photo_url }}" class="card-img-top" alt="{{ $p->name }}" style="height:180px;object-fit:cover">
                        <div class="card-body d-flex flex-column">
                            <div class="small text-muted">{{ $p->category->name }}</div>
                            <h6 class="card-title mb-1">{{ $p->name }}</h6>
                            <div class="small text-muted mb-2">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</div>
                            <div class="fw-bold mb-2">@rp($p->price)</div>

                            <div>
                                @if ($p->stock_status === 'habis')
                                    <span class="badge text-bg-danger">Habis</span>
                                @elseif ($p->stock_status === 'menipis')
                                    <span class="badge text-bg-warning">Stok menipis</span>
                                    <span class="small text-muted ms-1">Stok {{ $p->stock }}</span>
                                @else
                                    <span class="badge text-bg-success">Tersedia</span>
                                    <span class="small text-muted ms-1">Stok {{ $p->stock }}</span>
                                @endif
                            </div>

                            @if ($pembeli)
                                <div class="mt-auto pt-3">
                                    @if ($p->stock <= 0)
                                        <button class="btn btn-sm btn-secondary w-100" disabled>Habis</button>
                                    @elseif (! $pelanggan)
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-primary w-100">Keranjang</a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-primary w-100"
                                                data-bs-toggle="modal" data-bs-target="#modal-keranjang"
                                                data-id="{{ $p->id }}"
                                                data-name="{{ $p->name }}"
                                                data-detail="{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}"
                                                data-price="{{ $p->price }}"
                                                data-stock="{{ $p->stock }}"
                                                data-incart="{{ $isiKeranjang[$p->id] ?? 0 }}"
                                                data-photo="{{ $p->photo_url }}">
                                            Keranjang
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- Pop-up pilih jumlah (hanya untuk pelanggan yang sudah login) --}}
    @if ($pelanggan)
        <div class="modal fade" id="modal-keranjang" tabindex="-1" aria-labelledby="judul-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" id="form-keranjang" action="#" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="judul-modal">Tambah ke Keranjang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="d-flex mb-3">
                            <img id="km-foto" src="" alt="" class="rounded me-3" style="width:96px;height:96px;object-fit:cover">
                            <div>
                                <div class="fw-bold" id="km-nama"></div>
                                <div class="small text-muted" id="km-detail"></div>
                                <div class="mt-1" id="km-harga"></div>
                                <div class="small text-muted">Stok: <span id="km-stok"></span></div>
                            </div>
                        </div>

                        <label class="form-label" for="km-qty">Jumlah</label>
                        <div class="input-group" style="max-width:200px">
                            <button type="button" class="btn btn-outline-secondary" id="km-kurang" aria-label="Kurangi">−</button>
                            <input type="number" name="qty" id="km-qty" class="form-control text-center" value="1" min="1" required>
                            <button type="button" class="btn btn-outline-secondary" id="km-tambah" aria-label="Tambah">+</button>
                        </div>
                        <div class="small text-muted mt-2" id="km-info"></div>

                        <div class="mt-3">Subtotal: <strong id="km-subtotal"></strong></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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

                // Terisi otomatis dari tombol Keranjang yang ditekan
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