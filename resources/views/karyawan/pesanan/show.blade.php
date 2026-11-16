@extends('layouts.admin')
@section('title', $order->invoice_no)

@section('content')
    @include('layouts.admin.header', ['title' => $order->invoice_no, 'subtitle' => 'Pesanan online'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="row">
            <div class="col-xl-7 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Barang yang Disiapkan</h3>
                        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>Produk</th><th>Jumlah</th><th>Ambil dari batch</th></tr></thead>
                            <tbody>
                                @foreach ($order->items as $i)
                                    <tr>
                                        <td>{{ $i->product_name }}</td>
                                        <td><strong>{{ $i->qty }}</strong></td>
                                        <td class="small">
                                            @foreach ($i->batches as $b)
                                                <div>
                                                    {{ $b->qty }} ×
                                                    {{ $b->batch->expired_at ? 'kedaluwarsa ' . $b->batch->expired_at->format('d/m/Y') : 'tanpa kedaluwarsa' }}
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <h4>Kirim ke</h4>
                        <div class="font-weight-bold">{{ $order->recipient_name }} · {{ $order->recipient_phone }}</div>
                        <div class="text-muted">{{ $order->address }}</div>
                        <div class="small text-muted mt-2">Ongkir dibayar pelanggan: @rp($order->shipping_fee)</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5 mb-4">
                @if ($order->status === 'diproses')
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4>Tandai Dikirim</h4>
                            <p class="small text-muted">
                                Setelah barang dipacking dan ojek online dipesan, isi catatan pengiriman (nama driver, plat, atau nomor pesanan ojol).
                                Pelanggan akan diberi tahu.
                            </p>
                            <form method="POST" action="{{ route('karyawan.pesanan.kirim', $order) }}"
                                  onsubmit="return confirm('Tandai pesanan ini sudah dikirim?')">
                                @csrf
                                <input type="text" name="note" class="form-control mb-2" maxlength="200" required
                                       value="{{ old('note') }}" placeholder="Contoh: Driver Budi, BM 1234 XY">
                                <button class="btn btn-primary btn-block">Tandai Dikirim</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($order->status === 'dikirim')
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h4>Dalam Pengiriman</h4>
                            <div class="small text-muted mb-1">Dikirim {{ $order->shipped_at?->format('d/m/Y H:i') }}</div>
                            <div class="mb-3">{{ $order->shipping_note }}</div>
                            <form method="POST" action="{{ route('karyawan.pesanan.selesai', $order) }}"
                                  onsubmit="return confirm('Pastikan barang sudah sampai ke pelanggan. Tandai selesai?')">
                                @csrf
                                <button class="btn btn-success btn-block">Barang Sudah Sampai, Tandai Selesai</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($order->status === 'selesai')
                    <div class="alert alert-success">
                        Selesai {{ $order->completed_at?->format('d/m/Y H:i') }}.
                        @if ($order->shipping_note) <div class="small">Pengiriman: {{ $order->shipping_note }}</div> @endif
                    </div>
                @endif

                <button type="button" class="btn btn-outline-primary btn-block" onclick="window.print()">Cetak Daftar Barang</button>
            </div>
        </div>

        <a href="{{ route('karyawan.pesanan.index') }}" class="btn btn-link">&larr; Kembali ke daftar</a>
    </div>
@endsection