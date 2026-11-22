@extends('layouts.app')
@section('title', 'Pesanan ' . $order->invoice_no)

@section('wide')
@php
    $online = $order->isOnline();
    $posisi = [
        'menunggu_ongkir' => 1, 'menunggu_pembayaran' => 2, 'menunggu_verifikasi' => 2,
        'diproses' => 3, 'dikirim' => 4, 'selesai' => 6,
    ][$order->status] ?? 0;

    $langkah = [
        ['i-cart',  'Pesanan dibuat',  $order->created_at],
        ['i-tag',   'Ongkir dihitung', null],
        ['i-card',  'Pembayaran',      $order->paid_at],
        ['i-box',   'Diproses',        null],
        ['i-truck', 'Dikirim',         $order->shipped_at],
        ['i-check', 'Selesai',         $order->completed_at],
    ];
    $catatan = [
        1 => 'Dihitung toko',
        2 => $order->status === 'menunggu_verifikasi' ? 'Sedang diperiksa' : 'Menunggu pembayaran',
        3 => 'Sedang disiapkan',
        4 => 'Dalam perjalanan',
    ];
    $telp = $setting->get('store_phone');
@endphp

@include('layouts.partials.page-head', [
    'judul' => $order->invoice_no, 'induk' => 'Pesanan Saya', 'indukUrl' => route('pesanan.index'),
    'sub'   => 'Dipesan ' . $order->created_at->translatedFormat('d F Y, H:i'),
])

<div class="container py-4">

    {{-- Status dan garis waktu --}}
    <div class="ps-panel mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <span class="badge text-bg-light border">{{ $online ? 'Pesanan online' : 'Transaksi di toko' }}</span>
            <span class="ps-pill ps-pill-{{ $order->status_color }}">{{ $order->status_label }}</span>
        </div>

        @if ($order->status === 'dibatalkan')
            <div class="ps-cancelled">
                <svg width="26" height="26" class="ps-ico flex-none"><use href="#i-alert"/></svg>
                <div>
                    <strong>Pesanan dibatalkan</strong>
                    <div>{{ $order->cancel_reason }}</div>
                    <div class="small mt-1">
                        {{ $order->cancelled_at?->translatedFormat('d F Y, H:i') }}. Stok dan poin yang dipakai sudah dikembalikan.
                    </div>
                </div>
            </div>
        @elseif ($online)
            <ol class="ps-steps" aria-label="Status pesanan">
                @foreach ($langkah as $i => [$ic, $lbl, $waktu])
                    <li class="{{ $i < $posisi ? 'done' : ($i === $posisi ? 'current' : '') }}">
                        <span class="ps-step-dot"><svg width="18" height="18"><use href="#{{ $ic }}"/></svg></span>
                        <div>
                            <div class="ps-step-label">{{ $lbl }}</div>
                            <div class="ps-step-time">
                                @if ($i < $posisi && $waktu) {{ $waktu->translatedFormat('d M, H:i') }}
                                @elseif ($i === $posisi) {{ $catatan[$i] ?? '' }}
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- Pemberitahuan sesuai status --}}
    @if ($order->status === 'menunggu_ongkir')
        <div class="alert alert-warning">
            Toko sedang menghitung ongkir ojek online ke alamat Anda. Setelah ongkir terisi, Anda bisa membayar.
            Pesanan di luar jam buka (07.00 - 22.40 WIB) diproses mulai pukul 07.00.
        </div>
    @endif

    @if ($order->status === 'menunggu_verifikasi')
        @php $kirim = $order->payments->where('status', 'menunggu')->last(); @endphp
        <div class="alert alert-info">
            Bukti pembayaran sudah dikirim dan sedang diperiksa kasir.
            @if ($kirim) <a href="{{ route('bukti.show', $kirim) }}" target="_blank" rel="noopener">Lihat bukti yang saya kirim</a> @endif
        </div>
    @endif

    @if ($order->status === 'diproses')
        <div class="alert alert-success">Pembayaran sudah diverifikasi. Pesanan Anda sedang disiapkan toko.</div>
    @endif

    @if ($order->status === 'dikirim')
        <div class="alert alert-info">
            <strong>Pesanan Anda sedang dikirim lewat ojek online.</strong>
            @if ($order->shipping_note) <div class="small">Catatan toko: {{ $order->shipping_note }}</div> @endif

            <form method="POST" action="{{ route('pesanan.diterima', $order) }}" class="mt-2"
                  onsubmit="return confirm('Pastikan barang sudah sampai dan sesuai. Tandai pesanan ini selesai? Setelah selesai, statusnya tidak bisa diubah sendiri.')">
                @csrf
                <button class="btn btn-success btn-sm">Pesanan Sudah Diterima</button>
            </form>

            @if ($order->shipped_at)
                <div class="small mt-2">
                    Jika tidak dikonfirmasi, pesanan ditandai selesai otomatis pada
                    {{ $order->shipped_at->copy()->addDays(\App\Models\Order::AUTO_COMPLETE_DAYS)->translatedFormat('d M Y, H:i') }}.
                </div>
            @endif
        </div>
    @endif

    @if ($order->status === 'selesai')
        <div class="alert alert-success">
            Pesanan selesai{{ $order->completed_auto ? ' (otomatis)' : '' }}. Terima kasih sudah berbelanja di {{ config('app.name') }}.
            <div class="small mt-1">Ada masalah dengan pesanan? Hubungi toko{{ $telp ? ' di ' . $telp : '' }}.</div>
        </div>
    @endif

    {{-- Pembayaran --}}
    @if ($order->status === 'menunggu_pembayaran')
        <div class="ps-panel mb-4" id="pembayaran" style="border-color:var(--ps-orange)">
            <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-card"/></svg> Pembayaran</div>

            <p class="mb-1">
                Total yang harus dibayar: <strong class="ps-price">@rp($order->grand_total)</strong>
                <span class="text-muted small">(barang @rp($order->items_after_points) + ongkir @rp($order->shipping_fee))</span>
            </p>
            <p class="{{ $order->isOverdue() ? 'text-danger' : 'text-muted' }}">
                Batas pembayaran: <strong>{{ $order->pay_before->translatedFormat('d M Y, H:i') }}</strong>
                {{ $order->isOverdue() ? '(sudah lewat, hubungi toko)' : '' }}
            </p>

            @php
                $ditolak = $order->payments->where('status', 'ditolak')->last();
                $sisaKesempatan = \App\Models\Order::MAX_REJECTIONS - $order->payments->where('status', 'ditolak')->count();
            @endphp
            @if ($ditolak && $ditolak->reject_reason)
                <div class="alert alert-danger">
                    Bukti sebelumnya ditolak: {{ $ditolak->reject_reason }}. Silakan kirim ulang bukti yang benar.
                    <div class="small mt-1">
                        Sisa kesempatan mengirim bukti: <strong>{{ $sisaKesempatan }}</strong>.
                        Jika bukti ditolak lagi sampai habis, pesanan dibatalkan otomatis.
                    </div>
                </div>
            @endif

            @unless ($order->isOverdue())
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-bold mb-1">Transfer Bank</div>
                            @if ($setting->get('bank_account'))
                                <div>{{ $setting->get('bank_name') }}</div>
                                <div class="fs-5 fw-bold">{{ $setting->get('bank_account') }}</div>
                                <div class="text-muted small">a.n. {{ $setting->get('bank_holder') }}</div>
                            @else
                                <div class="text-muted small">Data rekening belum diisi toko.</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-bold mb-1">QRIS</div>
                            @if ($setting->get('qris_image'))
                                <img src="{{ asset('storage/' . $setting->get('qris_image')) }}" alt="QRIS" class="img-fluid" style="max-height:200px">
                            @else
                                <div class="text-muted small">QRIS belum diisi toko.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('pesanan.bayar', $order) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label d-block">Saya membayar lewat</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="method" id="m-transfer" value="transfer" @checked(old('method', 'transfer') === 'transfer')>
                            <label class="form-check-label" for="m-transfer">Transfer bank</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="method" id="m-qris" value="qris" @checked(old('method') === 'qris')>
                            <label class="form-check-label" for="m-qris">QRIS</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto atau tangkapan layar bukti pembayaran <span class="text-muted fw-normal">(jpg, png, webp, maks. 3 MB)</span></label>
                        <input type="file" name="proof" accept="image/*" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">Kirim Bukti Pembayaran</button>
                </form>
            @endunless
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ps-panel mb-4">
                <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-box"/></svg> Barang Pesanan</div>
                @foreach ($order->items as $i)
                    <div class="ps-line">
                        <img src="{{ $i->product->photo_url }}" alt="{{ $i->product_name }}" class="ps-line-img">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $i->product_name }}</div>
                            <div class="small text-muted">@rp($i->price) &times; {{ $i->qty }}</div>
                        </div>
                        <div class="fw-bold">@rp($i->subtotal)</div>
                    </div>
                @endforeach
            </div>

            @if ($online)
                <div class="ps-panel">
                    <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-pin"/></svg> Dikirim ke</div>
                    <div class="fw-semibold">{{ $order->recipient_name }} <span class="text-muted fw-normal">· {{ $order->recipient_phone }}</span></div>
                    <div class="text-muted">{{ $order->address }}</div>
                    @if ($order->shipping_note)
                        <div class="small mt-2"><strong>Pengiriman:</strong> {{ $order->shipping_note }}</div>
                    @endif
                </div>
            @else
                <div class="ps-panel">
                    <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-card"/></svg> Pembayaran di Toko</div>
                    <div class="text-muted">Dibayar {{ strtoupper($order->payment_method) }} · {{ $order->paid_at?->translatedFormat('d M Y, H:i') }}</div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="ps-panel">
                <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-tag"/></svg> Rincian Pembayaran</div>

                <div class="ps-sum-row"><span>Total barang</span><span>@rp($order->items_total)</span></div>
                @if ($order->points_used > 0)
                    <div class="ps-sum-row text-success"><span>Potongan poin</span><span>&minus;@rp($order->points_used)</span></div>
                @endif
                @if ($online)
                    <div class="ps-sum-row">
                        <span>Ongkir</span>
                        <span>{{ $order->shipping_fee === null ? 'Belum dihitung' : 'Rp' . number_format($order->shipping_fee, 0, ',', '.') }}</span>
                    </div>
                @endif
                <hr>
                <div class="ps-sum-total">
                    <span>Total bayar</span>
                    <strong>{{ $order->grand_total === null ? 'Menunggu ongkir' : 'Rp' . number_format($order->grand_total, 0, ',', '.') }}</strong>
                </div>

                @if ($order->status !== 'dibatalkan' && $order->customer_id)
                    <div class="ps-sum-note mt-3 mb-0">
                        <svg width="18" height="18"><use href="#i-star"/></svg>
                        <span>
                            @if ($order->points_earned > 0)
                                Anda mendapat <strong>{{ number_format($order->points_earned, 0, ',', '.') }} poin</strong> dari pesanan ini.
                            @elseif (in_array($order->status, ['diproses', 'dikirim', 'selesai']))
                                Pesanan ini tidak menghasilkan poin.
                            @else
                                Setelah pembayaran diverifikasi, Anda mendapat
                                <strong>{{ number_format(\App\Models\Order::pointsFor($order->items_after_points), 0, ',', '.') }} poin</strong>.
                            @endif
                        </span>
                    </div>
                @endif

                @if ($order->canBeCancelledByCustomer())
                    <form method="POST" action="{{ route('pesanan.batal', $order) }}" class="mt-3"
                          onsubmit="return confirm('Batalkan pesanan ini? Stok dan poin akan dikembalikan.')">
                        @csrf
                        <button class="btn btn-outline-danger w-100">Batalkan Pesanan</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <a href="{{ route('pesanan.index') }}" class="btn btn-link px-0 mt-3">&larr; Kembali ke daftar pesanan</a>
</div>
@endsection