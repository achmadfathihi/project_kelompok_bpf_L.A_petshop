@extends('layouts.admin')
@section('title', $order->invoice_no)

@section('content')
    @include('layouts.admin.header', ['title' => $order->invoice_no, 'subtitle' => $order->isOnline() ? 'Pesanan online' : 'Transaksi di toko'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="row">
            <div class="col-xl-7 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Isi Pesanan</h3>
                        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
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

                <div class="card shadow">
                    <div class="card-body">
                        <h4>Pelanggan</h4>
                        <div>{{ $order->customer?->name ?? 'Pembeli umum (bukan member)' }}</div>
                        @if ($order->isOnline())
                            <hr>
                            <h4>Dikirim ke</h4>
                            <div>{{ $order->recipient_name }} · {{ $order->recipient_phone }}</div>
                            <div class="text-muted">{{ $order->address }}</div>
                        @else
                            <div class="text-muted small mt-2">
                                Dilayani {{ $order->cashier?->name }} · {{ $order->created_at->format('d/m/Y H:i') }}
                                · bayar {{ strtoupper($order->payment_method) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-5 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Rincian Pembayaran</h4>
                        <div class="d-flex justify-content-between"><span>Total barang</span><span>@rp($order->items_total)</span></div>
                        @if ($order->points_used > 0)
                            <div class="d-flex justify-content-between text-success"><span>Potongan poin</span><span>−@rp($order->points_used)</span></div>
                        @endif
                        @if ($order->isOnline())
                            <div class="d-flex justify-content-between">
                                <span>Ongkir</span>
                                <span>{{ $order->shipping_fee === null ? 'Belum diisi' : 'Rp' . number_format($order->shipping_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total bayar</span>
                            <span>{{ $order->grand_total === null ? '-' : 'Rp' . number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                        @if ($order->customer_id)
                            <div class="small text-muted mt-3">
                                Poin untuk member:
                                {{ $order->points_earned > 0
                                    ? '+' . number_format($order->points_earned, 0, ',', '.') . ' (sudah masuk)'
                                    : (in_array($order->status, ['dibatalkan']) ? '-' : \App\Models\Order::pointsFor($order->items_after_points) . ' (masuk setelah pembayaran valid)') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ===== TINDAKAN KASIR ===== --}}
                @if (in_array($order->status, ['menunggu_ongkir', 'menunggu_pembayaran']))
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4>{{ $order->status === 'menunggu_ongkir' ? 'Isi Ongkir' : 'Ubah Ongkir' }}</h4>
                            <p class="small text-muted">Cek tarif ojek online ke alamat pelanggan, lalu isi nominalnya. Batas bayar 24 jam dihitung sejak ongkir pertama diisi.</p>
                            <form method="POST" action="{{ route('kasir.pesanan.ongkir', $order) }}">
                                @csrf
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="number" name="shipping_fee" min="0" class="form-control"
                                           value="{{ old('shipping_fee', $order->shipping_fee) }}" required>
                                </div>
                                <button class="btn btn-primary btn-block">Simpan Ongkir</button>
                            </form>
                            @if ($order->status === 'menunggu_pembayaran' && $order->pay_before)
                                <div class="small mt-2 {{ $order->isOverdue() ? 'text-danger' : 'text-muted' }}">
                                    Batas bayar: {{ $order->pay_before->format('d/m/Y H:i') }}{{ $order->isOverdue() ? ' (sudah lewat)' : '' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($order->status === 'menunggu_verifikasi')
                    @php $bukti = $order->payments->where('status', 'menunggu')->last(); @endphp
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4>Verifikasi Pembayaran</h4>
                            @if ($bukti)
                                <div class="small text-muted mb-2">
                                    Metode: {{ strtoupper($bukti->method) }} · Nominal seharusnya <strong>@rp($bukti->amount)</strong>
                                </div>
                                <a href="{{ route('bukti.show', $bukti) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('bukti.show', $bukti) }}" alt="Bukti pembayaran" class="img-fluid rounded border mb-3">
                                </a>
                                <form method="POST" action="{{ route('kasir.pesanan.verifikasi', $order) }}" class="mb-3"
                                      onsubmit="return confirm('Pastikan nominal dan uang sudah masuk. Lanjutkan menyatakan valid?')">
                                    @csrf
                                    <button class="btn btn-success btn-block">Pembayaran Valid</button>
                                </form>
                                @php $jumlahDitolak = $order->payments->where('status', 'ditolak')->count(); @endphp
                                <form method="POST" action="{{ route('kasir.pesanan.tolak', $order) }}">
                                    @csrf
                                    <input type="text" name="reason" class="form-control mb-2" maxlength="200" required
                                        placeholder="Alasan penolakan, contoh: nominal kurang">
                                    <button class="btn btn-outline-danger btn-block">Tolak Bukti</button>
                                    <div class="small mt-2 {{ $jumlahDitolak + 1 >= \App\Models\Order::MAX_REJECTIONS ? 'text-danger' : 'text-muted' }}">
                                        Sudah ditolak {{ $jumlahDitolak }}×.
                                        @if ($jumlahDitolak + 1 >= \App\Models\Order::MAX_REJECTIONS)
                                            Menolak sekarang akan <strong>membatalkan pesanan</strong>.
                                        @else
                                            Penolakan ke-{{ \App\Models\Order::MAX_REJECTIONS }} membatalkan pesanan otomatis.
                                        @endif
                                    </div>
                                </form>
                            @else
                                <div class="text-muted">Bukti pembayaran tidak ditemukan.</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if (in_array($order->status, \App\Models\Order::CASHIER_CANCELLABLE, true))
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4>Batalkan Pesanan</h4>
                            <p class="small text-muted">Stok kembali ke batch asalnya dan poin yang dipakai dikembalikan.</p>
                            <form method="POST" action="{{ route('kasir.pesanan.batal', $order) }}"
                                  onsubmit="return confirm('Batalkan pesanan ini?')">
                                @csrf
                                <input type="text" name="reason" class="form-control mb-2" maxlength="200" required placeholder="Alasan pembatalan">
                                <button class="btn btn-outline-danger btn-block">Batalkan Pesanan</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($order->status === 'dibatalkan')
                    <div class="alert alert-secondary">Dibatalkan: {{ $order->cancel_reason }}</div>
                @endif

                @if ($order->payments->isNotEmpty())
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4 class="mb-3">Riwayat Pembayaran</h4>
                            @foreach ($order->payments->sortByDesc('id') as $p)
                                <div class="mb-2 small">
                                    <span class="badge badge-{{ $p->status === 'valid' ? 'success' : ($p->status === 'ditolak' ? 'danger' : 'warning') }}">{{ ucfirst($p->status) }}</span>
                                    {{ strtoupper($p->method) }} · {{ $p->created_at->format('d/m/Y H:i') }}
                                    @if ($p->reject_reason) <div class="text-danger">{{ $p->reject_reason }}</div> @endif
                                    @if ($p->verifier) <div class="text-muted">Diperiksa: {{ $p->verifier->name }}</div> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @unless ($order->isOnline())
                    <button type="button" class="btn btn-outline-primary btn-block" onclick="window.print()">Cetak Struk</button>
                @endunless
            </div>
        </div>

        <a href="{{ route('kasir.pesanan.index') }}" class="btn btn-link">&larr; Kembali ke daftar</a>
    </div>
@endsection