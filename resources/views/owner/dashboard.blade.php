@extends('layouts.admin')
@section('title', 'Dashboard Owner')

@section('content')
    @include('layouts.admin.header', ['title' => 'Dashboard Owner', 'subtitle' => 'Ringkasan toko. Penjualan dihitung dari barang setelah potongan poin, tanpa ongkir.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="row">
            @include('layouts.admin.stat', ['label' => 'Penjualan Hari Ini', 'value' => 'Rp' . number_format($hari['penjualan'], 0, ',', '.'), 'note' => $hari['jumlah'] . ' transaksi', 'icon' => 'fas fa-coins', 'color' => 'bg-green'])
            @include('layouts.admin.stat', ['label' => 'Penjualan Bulan Ini', 'value' => 'Rp' . number_format($bulan['penjualan'], 0, ',', '.'), 'note' => $bulan['jumlah'] . ' transaksi', 'icon' => 'fas fa-chart-line', 'color' => 'bg-primary'])
            @include('layouts.admin.stat', ['label' => 'Pesanan Online Berjalan', 'value' => $berjalan->sum(), 'note' => 'belum selesai', 'icon' => 'fas fa-truck', 'color' => 'bg-info'])
            @include('layouts.admin.stat', ['label' => 'Stok Perlu Perhatian', 'value' => $habis + $menipis, 'note' => $habis . ' habis, ' . $menipis . ' menipis', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'bg-warning'])
        </div>

        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header border-0"><h3 class="mb-0">Pesanan Online Berjalan</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <tbody>
                                @foreach (['menunggu_ongkir', 'menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim'] as $st)
                                    <tr>
                                        <td><span class="badge badge-{{ \App\Models\Order::STATUS[$st][1] }}">{{ \App\Models\Order::STATUS[$st][0] }}</span></td>
                                        <td class="text-right font-weight-bold">{{ $berjalan->get($st, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header border-0"><h3 class="mb-0">Kedaluwarsa</h3></div>
                    <div class="card-body pt-0">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Batch sudah lewat tanggal (masih ada sisa)</span>
                            <span class="badge badge-{{ $lewat ? 'danger' : 'success' }}">{{ $lewat }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Batch kedaluwarsa dalam 30 hari</span>
                            <span class="badge badge-{{ $segera ? 'warning' : 'success' }}">{{ $segera }}</span>
                        </div>
                        <a href="{{ route('owner.laporan.kedaluwarsa') }}" class="btn btn-sm btn-outline-primary">Lihat laporan kedaluwarsa</a>
                        <a href="{{ route('owner.laporan.stok') }}" class="btn btn-sm btn-outline-primary">Lihat laporan stok</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header border-0"><h3 class="mb-0">Terlaris Bulan Ini</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>Produk</th><th class="text-right">Terjual</th></tr></thead>
                            <tbody>
                                @forelse ($top as $t)
                                    <tr><td>{{ $t->name }}</td><td class="text-right">{{ $t->qty }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">Belum ada penjualan bulan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header border-0"><h3 class="mb-0">Transaksi Terbaru</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>No.</th><th>Jenis</th><th>Bayar</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($terbaru as $o)
                                    <tr>
                                        <td>{{ $o->invoice_no }}</td>
                                        <td>{{ $o->isOnline() ? 'Online' : 'Di toko' }}</td>
                                        <td>@rp($o->items_after_points)</td>
                                        <td class="text-right"><a href="{{ route('kasir.pesanan.show', $o) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection