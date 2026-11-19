@extends('layouts.admin')
@section('title', 'Laporan Penjualan')

@section('content')
    @include('layouts.admin.header', ['title' => 'Laporan Penjualan'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <label class="mr-2">Dari</label>
                    <input type="date" name="dari" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm mr-3">
                    <label class="mr-2">Sampai</label>
                    <input type="date" name="sampai" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm mr-3">
                    <button class="btn btn-sm btn-primary">Tampilkan</button>
                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" onclick="window.print()">Cetak</button>
                </form>
                @foreach ($shortcuts as $label => $q)
                    <a href="{{ route('owner.laporan.penjualan', $q) }}" class="btn btn-sm btn-secondary">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="d-none d-print-block mb-3">
            <h3 class="mb-0">{{ config('app.name') }}</h3>
            <div>Laporan Penjualan, {{ $from->format('d/m/Y') }} s.d. {{ $to->format('d/m/Y') }}</div>
        </div>

        <div class="row">
            @include('layouts.admin.stat', ['label' => 'Penjualan', 'value' => 'Rp' . number_format($s['penjualan'], 0, ',', '.'), 'note' => 'barang setelah potongan poin', 'icon' => 'fas fa-coins', 'color' => 'bg-green'])
            @include('layouts.admin.stat', ['label' => 'Transaksi', 'value' => $s['jumlah'], 'icon' => 'fas fa-receipt', 'color' => 'bg-info'])
            @include('layouts.admin.stat', ['label' => 'Potongan Poin', 'value' => 'Rp' . number_format($s['potongan'], 0, ',', '.'), 'note' => 'sudah dikurangkan dari penjualan', 'icon' => 'fas fa-percent', 'color' => 'bg-warning'])
            @include('layouts.admin.stat', ['label' => 'Ongkir Diterima', 'value' => 'Rp' . number_format($s['ongkir'], 0, ',', '.'), 'note' => 'dicatat terpisah, bukan penjualan', 'icon' => 'fas fa-truck', 'color' => 'bg-default'])
        </div>

        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header border-0"><h3 class="mb-0">Per Jenis dan Metode Bayar</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>Kelompok</th><th class="text-right">Transaksi</th><th class="text-right">Penjualan</th></tr></thead>
                            <tbody>
                                @foreach (['online' => 'Online', 'offline' => 'Di toko'] as $k => $lbl)
                                    <tr>
                                        <td>{{ $lbl }}</td>
                                        <td class="text-right">{{ $s['channel']->get($k)?->jumlah ?? 0 }}</td>
                                        <td class="text-right">@rp($s['channel']->get($k)?->penjualan ?? 0)</td>
                                    </tr>
                                @endforeach
                                @foreach (['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $k => $lbl)
                                    <tr class="text-muted">
                                        <td>Bayar: {{ $lbl }}</td>
                                        <td class="text-right">{{ $s['metode']->get($k)?->jumlah ?? 0 }}</td>
                                        <td class="text-right">@rp($s['metode']->get($k)?->penjualan ?? 0)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header border-0"><h3 class="mb-0">Produk Terlaris</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light"><tr><th>Produk</th><th class="text-right">Terjual</th><th class="text-right">Nilai barang</th></tr></thead>
                            <tbody>
                                @forelse ($top as $t)
                                    <tr><td>{{ $t->name }}</td><td class="text-right">{{ $t->qty }}</td><td class="text-right">@rp($t->total)</td></tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada penjualan pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header border-0"><h3 class="mb-0">Per Hari</h3></div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light"><tr><th>Tanggal</th><th class="text-right">Transaksi</th><th class="text-right">Penjualan</th></tr></thead>
                    <tbody>
                        @forelse ($daily as $d)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($d->tgl)->format('d/m/Y') }}</td>
                                <td class="text-right">{{ $d->jumlah }}</td>
                                <td class="text-right">@rp($d->penjualan)</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada penjualan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="small text-muted">
            Yang dihitung: pesanan online yang pembayarannya sudah valid (diproses, dikirim, selesai) dan transaksi di toko, menurut tanggal pembayaran.
            Pesanan batal dan yang belum dibayar tidak dihitung. Poin diberikan pada periode ini: {{ number_format($s['poin_diberi'], 0, ',', '.') }}.
        </p>
    </div>
@endsection