@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('wide')
    @include('layouts.partials.page-head', ['judul' => 'Pesanan Saya', 'sub' => 'Pantau status pesanan Anda dari ongkir sampai barang diterima.'])

    <div class="container py-4">
        @php
            $tabs = [
                'semua' => 'Semua', 'bayar' => 'Belum dibayar', 'proses' => 'Diproses',
                'kirim' => 'Dikirim', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan',
            ];
        @endphp

        <div class="ps-tabs" role="tablist">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('pesanan.index', ['tab' => $key]) }}" class="ps-tab {{ $tab === $key ? 'active' : '' }}">
                    {{ $label }}
                    @if (($counts[$key] ?? 0) > 0) <span class="ps-tab-count">{{ $counts[$key] }}</span> @endif
                </a>
            @endforeach
        </div>

        @forelse ($orders as $o)
            @php
                $pertama = $o->items->first();
                $lain    = $o->items->count() - 1;
                $petunjuk = match ($o->status) {
                    'menunggu_ongkir'     => 'Toko sedang menghitung ongkir.',
                    'menunggu_pembayaran' => $o->pay_before ? 'Bayar sebelum ' . $o->pay_before->translatedFormat('d M Y, H:i') : 'Menunggu pembayaran.',
                    'menunggu_verifikasi' => 'Bukti pembayaran sedang diperiksa kasir.',
                    'diproses'            => 'Pesanan sedang disiapkan toko.',
                    'dikirim'             => 'Dalam pengiriman. Konfirmasi setelah barang sampai.',
                    'selesai'             => 'Terima kasih sudah berbelanja.',
                    'dibatalkan'          => \Illuminate\Support\Str::limit($o->cancel_reason, 70),
                    default               => '',
                };
            @endphp

            <article class="ps-order">
                <header class="ps-order-head">
                    <div>
                        <span class="ps-order-no">{{ $o->invoice_no }}</span>
                        <span class="text-muted small ms-2">{{ $o->created_at->translatedFormat('d M Y, H:i') }}</span>
                        <span class="badge text-bg-light border ms-1">{{ $o->isOnline() ? 'Online' : 'Di toko' }}</span>
                    </div>
                    <span class="ps-pill ps-pill-{{ $o->status_color }}">{{ $o->status_label }}</span>
                </header>

                <div class="ps-order-body">
                    <div class="ps-thumbs">
                        @foreach ($o->items->take(3) as $it)
                            <img src="{{ $it->product->photo_url }}" alt="{{ $it->product_name }}">
                        @endforeach
                        @if ($o->items->count() > 3) <span class="ps-thumb-more">+{{ $o->items->count() - 3 }}</span> @endif
                    </div>

                    <div class="ps-order-main">
                        <div class="fw-semibold">{{ $pertama->product_name }}</div>
                        <div class="small text-muted">
                            {{ $o->items->sum('qty') }} barang{{ $lain > 0 ? ' · dan ' . $lain . ' produk lain' : '' }}
                        </div>
                    </div>

                    <div class="ps-order-total">
                        <div class="small text-muted">{{ $o->grand_total === null ? 'Total barang' : 'Total bayar' }}</div>
                        <div class="ps-price">@rp($o->grand_total ?? $o->items_after_points)</div>
                        @if ($o->grand_total === null) <div class="small text-muted">+ ongkir</div> @endif
                    </div>
                </div>

                <footer class="ps-order-foot">
                    <span>{{ $petunjuk }}</span>
                    <span class="d-flex gap-2">
                        @if ($o->status === 'menunggu_pembayaran' && ! $o->isOverdue())
                            <a href="{{ route('pesanan.show', $o) }}#pembayaran" class="btn btn-primary btn-sm">Bayar Sekarang</a>
                        @endif
                        <a href="{{ route('pesanan.show', $o) }}" class="btn btn-outline-primary btn-sm">Lihat Detail</a>
                    </span>
                </footer>
            </article>
        @empty
            <div class="ps-empty">
                <div class="ps-empty-icon"><svg width="44" height="44"><use href="#i-box"/></svg></div>
                <h2 class="h4">{{ $tab === 'semua' ? 'Belum ada pesanan' : 'Tidak ada pesanan di tab ini' }}</h2>
                <p class="text-muted mb-4">Pesanan Anda akan muncul di sini setelah Anda berbelanja.</p>
                <a href="{{ route('beranda') }}#katalog" class="btn btn-primary">Mulai Belanja</a>
            </div>
        @endforelse

        <div class="mt-4 d-flex justify-content-center">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection