@extends('layouts.app')
@section('title', 'Keranjang')

@section('wide')
    @include('layouts.partials.page-head', [
        'judul' => 'Keranjang Belanja',
        'sub'   => $lines->isEmpty() ? null : $lines->sum('qty') . ' barang siap dipesan',
    ])

    <div class="container py-4">
        @if ($lines->isEmpty())
            <div class="ps-empty">
                <div class="ps-empty-icon"><svg width="44" height="44"><use href="#i-cart"/></svg></div>
                <h2 class="h4">Keranjang Anda masih kosong</h2>
                <p class="text-muted mb-4">Yuk, pilih makanan, vitamin, atau perlengkapan untuk hewan kesayangan Anda.</p>
                <a href="{{ route('beranda') }}#katalog" class="btn btn-primary btn-lg">Mulai Belanja</a>
            </div>
        @else
            <ol class="ps-flow mb-4" aria-label="Langkah belanja">
                <li class="current"><span class="ps-flow-num">1</span> Keranjang</li>
                <li><span class="ps-flow-num">2</span> Alamat &amp; Poin</li>
                <li><span class="ps-flow-num">3</span> Pembayaran</li>
                <li><span class="ps-flow-num">4</span> Selesai</li>
            </ol>

            <div class="row g-4">
                <div class="col-lg-8">
                    @foreach ($lines as $l)
                        @php $p = $l['product']; @endphp
                        <div class="ps-cart-item {{ $l['ok'] ? '' : 'is-problem' }}">
                            <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" class="ps-cart-img">

                            <div>
                                <div class="ps-product-cat">{{ $p->category->name }}</div>
                                <h3 class="h6 mb-0">{{ $p->name }}</h3>
                                <div class="ps-product-meta">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</div>
                                <div class="ps-price-sm mt-1">@rp($p->price) <span class="text-muted fw-normal small">/ item</span></div>
                                @unless ($l['ok'])
                                    <div class="ps-warn">
                                        <svg width="16" height="16" class="ps-ico mt-1"><use href="#i-alert"/></svg>
                                        <span>{{ $p->stock > 0 ? 'Stok tinggal ' . $p->stock . '. Kurangi jumlahnya.' : 'Stok habis. Hapus dari keranjang.' }}</span>
                                    </div>
                                @endunless
                            </div>

                            <div class="ps-cart-actions">
                                <form method="POST" action="{{ route('keranjang.update', $p) }}" class="ps-stepper">
                                    @csrf @method('PATCH')
                                    <button type="button" data-step="-1" aria-label="Kurangi jumlah">&minus;</button>
                                    <input type="number" name="qty" value="{{ $l['qty'] }}" min="0" max="{{ max($p->stock, 1) }}" aria-label="Jumlah {{ $p->name }}">
                                    <button type="button" data-step="1" aria-label="Tambah jumlah" @disabled($l['qty'] >= $p->stock)>+</button>
                                    <noscript><button type="submit">Ubah</button></noscript>
                                </form>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="ps-cart-sub">@rp($l['subtotal'])</span>
                                    <form method="POST" action="{{ route('keranjang.remove', $p) }}">
                                        @csrf @method('DELETE')
                                        <button class="ps-trash" aria-label="Hapus {{ $p->name }} dari keranjang" title="Hapus">
                                            <svg width="20" height="20"><use href="#i-trash"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('beranda') }}#katalog" class="btn btn-link px-0">&larr; Lanjut belanja</a>
                </div>

                <div class="col-lg-4">
                    <aside class="ps-summary">
                        <h2 class="h5 mb-3">Ringkasan Belanja</h2>

                        <div class="ps-sum-row"><span>Subtotal ({{ $lines->sum('qty') }} barang)</span><strong>@rp($total)</strong></div>
                        <div class="ps-sum-row text-muted"><span>Ongkir</span><span>Dihitung toko</span></div>

                        <div class="ps-sum-note">
                            <svg width="18" height="18"><use href="#i-star"/></svg>
                            <span>Perkiraan poin: <strong>+{{ number_format(\App\Models\Order::pointsFor($total), 0, ',', '.') }}</strong>
                                <span class="text-muted">(jika tidak memakai poin)</span></span>
                        </div>

                        <hr>
                        <div class="ps-sum-total mb-3"><span>Total barang</span><strong>@rp($total)</strong></div>

                        @if ($semuaOk)
                            <a href="{{ route('checkout.show') }}" class="btn btn-primary btn-lg w-100">
                                Lanjut ke Checkout <svg class="ps-ico" width="18" height="18"><use href="#i-arrow"/></svg>
                            </a>
                        @else
                            <button class="btn btn-primary btn-lg w-100" disabled>Perbaiki jumlah dulu</button>
                            <div class="small text-muted mt-2">Ada barang yang stoknya berubah. Sesuaikan jumlahnya untuk melanjutkan.</div>
                        @endif

                        <ul class="ps-trust">
                            <li><svg width="18" height="18"><use href="#i-truck"/></svg><span>Dikirim lewat ojek online. Ongkir dihitung toko setelah pesanan dibuat.</span></li>
                            <li><svg width="18" height="18"><use href="#i-gift"/></svg><span>Poin bisa dipakai sebagai potongan harga di langkah berikutnya.</span></li>
                            <li><svg width="18" height="18"><use href="#i-clock"/></svg><span>Pesanan di luar jam buka diproses mulai pukul 07.00 WIB.</span></li>
                        </ul>
                    </aside>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('js')
<script>
    document.querySelectorAll('.ps-stepper').forEach(function (f) {
        var i = f.querySelector('input[name="qty"]');

        f.querySelectorAll('[data-step]').forEach(function (b) {
            b.addEventListener('click', function () {
                var n = (parseInt(i.value) || 0) + parseInt(b.dataset.step);
                n = Math.max(1, Math.min(n, parseInt(i.max) || n));
                if (n !== parseInt(i.value)) { i.value = n; f.submit(); }
            });
        });

        i.addEventListener('change', function () { f.submit(); });
    });
</script>
@endpush