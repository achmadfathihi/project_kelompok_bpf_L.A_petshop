@extends('layouts.app')
@section('title', 'Pesanan ' . $order->invoice_no)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $order->invoice_no }}</h3>
        <span class="badge text-bg-{{ $order->status_color }} fs-6">{{ $order->status_label }}</span>
    </div>

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
            Pesanan Anda sedang dikirim lewat ojek online.
            @if ($order->shipping_note) <div class="small">Catatan toko: {{ $order->shipping_note }}</div> @endif

            <form method="POST" action="{{ route('pesanan.diterima', $order) }}" class="mt-2"
                onsubmit="return confirm('Pastikan barang sudah sampai dan sesuai. Tandai pesanan ini selesai? Setelah selesai, statusnya tidak bisa diubah sendiri.')">
                @csrf
                <button class="btn btn-success btn-sm">Pesanan Sudah Diterima</button>
            </form>

            @if ($order->shipped_at)
                <div class="small mt-2">
                    Jika tidak dikonfirmasi, pesanan ditandai selesai otomatis pada
                    {{ $order->shipped_at->copy()->addDays(\App\Models\Order::AUTO_COMPLETE_DAYS)->format('d/m/Y H:i') }}.
                </div>
            @endif
        </div>
    @endif

    @if ($order->status === 'selesai')
        <div class="alert alert-success">
            Pesanan selesai{{ $order->completed_auto ? ' (otomatis)' : '' }}. Terima kasih sudah berbelanja di {{ config('app.name') }}.
            <div class="small mt-1">Ada masalah dengan pesanan? Hubungi toko.</div>
        </div>
    @endif

    @if ($order->status === 'menunggu_pembayaran')
        <div class="card shadow-sm border-primary mb-4">
            <div class="card-body">
                <h5 class="mb-3">Pembayaran</h5>
                <p class="mb-1">
                    Total yang harus dibayar: <strong>@rp($order->grand_total)</strong>
                    (barang @rp($order->items_after_points) + ongkir @rp($order->shipping_fee)).
                </p>
                <p class="{{ $order->isOverdue() ? 'text-danger' : 'text-muted' }}">
                    Batas pembayaran: <strong>{{ $order->pay_before->format('d/m/Y H:i') }}</strong>
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
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold mb-1">Transfer Bank</div>
                                @if ($setting->get('bank_account'))
                                    <div>{{ $setting->get('bank_name') }}</div>
                                    <div class="fs-5">{{ $setting->get('bank_account') }}</div>
                                    <div class="text-muted small">a.n. {{ $setting->get('bank_holder') }}</div>
                                @else
                                    <div class="text-muted small">Data rekening belum diisi toko.</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
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
                            <label class="form-label">Foto/screenshot bukti pembayaran (jpg, png, webp, maks. 3 MB)</label>
                            <input type="file" name="proof" accept="image/*" class="form-control" required>
                        </div>
                        <button class="btn btn-primary">Kirim Bukti Pembayaran</button>
                    </form>
                @endunless
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $i)
                                <tr>
                                    <td>{{ $i->product_name }}</td>
                                    <td>@rp($i->price)</td>
                                    <td>{{ $i->qty }}</td>
                                    <td>@rp($i->subtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($order->isOnline())
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Dikirim ke</h6>
                    <div>{{ $order->recipient_name }} · {{ $order->recipient_phone }}</div>
                    <div class="text-muted">{{ $order->address }}</div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Rincian Pembayaran</h6>
                    <div class="d-flex justify-content-between"><span>Total barang</span><span>@rp($order->items_total)</span></div>
                    @if ($order->points_used > 0)
                        <div class="d-flex justify-content-between text-success">
                            <span>Potongan poin</span><span>−@rp($order->points_used)</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span>Ongkir</span>
                        <span>{{ $order->shipping_fee === null ? 'Belum dihitung' : 'Rp' . number_format($order->shipping_fee, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total bayar</span>
                        <span>{{ $order->grand_total === null ? 'Menunggu ongkir' : 'Rp' . number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>

                    @if ($order->status !== 'dibatalkan')
                        <div class="small text-muted mt-3">
                            @if ($order->points_earned > 0)
                                Anda mendapat <strong>{{ number_format($order->points_earned, 0, ',', '.') }} poin</strong> dari pesanan ini.
                            @elseif (in_array($order->status, ['diproses', 'dikirim', 'selesai']))
                                Pesanan ini tidak menghasilkan poin.
                            @else
                                Setelah pembayaran diverifikasi, Anda mendapat
                                <strong>{{ \App\Models\Order::pointsFor($order->items_after_points) }} poin</strong>.
                            @endif
                        </div>
                    @endif

                    @if ($order->status === 'dibatalkan')
                        <div class="small text-muted mt-3">
                            Dibatalkan pada {{ $order->cancelled_at?->format('d/m/Y H:i') }}. {{ $order->cancel_reason }}
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
    </div>

    <a href="{{ route('pesanan.index') }}" class="btn btn-link mt-3">&larr; Kembali ke daftar pesanan</a>
@endsection