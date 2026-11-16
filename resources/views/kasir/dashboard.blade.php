@extends('layouts.admin')
@section('title', 'Dashboard Kasir')

@section('content')
    @include('layouts.admin.header', ['title' => 'Dashboard Kasir', 'subtitle' => 'Pekerjaan yang menunggu dan ringkasan hari ini.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        @php
            $cards = [
                ['Perlu Ongkir',        $perluOngkir,      'fas fa-truck',        'bg-warning', route('kasir.pesanan.index')],
                ['Perlu Verifikasi',    $perluVerifikasi,  'fas fa-receipt',      'bg-danger',  route('kasir.pesanan.index')],
                ['Transaksi Hari Ini',  $transaksiHariIni, 'fas fa-shopping-bag', 'bg-info',    route('kasir.pesanan.index', ['tab' => 'semua'])],
                ['Pemasukan Hari Ini',  'Rp' . number_format($omzetHariIni, 0, ',', '.'), 'fas fa-coins', 'bg-green', route('kasir.pesanan.index', ['tab' => 'semua'])],
            ];
        @endphp

        <div class="row">
            @foreach ($cards as [$label, $value, $icon, $color, $url])
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="{{ $url }}" class="text-dark">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">{{ $label }}</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $value }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape {{ $color }} text-white rounded-circle shadow"><i class="{{ $icon }}"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card shadow">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Antrian (paling lama di atas)</h3>
                <a href="{{ route('kasir.transaksi.create') }}" class="btn btn-sm btn-primary">Transaksi di Toko</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light"><tr><th>No. Pesanan</th><th>Dibuat</th><th>Pelanggan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($antrian as $o)
                            <tr>
                                <td>{{ $o->invoice_no }}</td>
                                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $o->customer?->name ?? '-' }}</td>
                                <td><span class="badge badge-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
                                <td class="text-right"><a href="{{ route('kasir.pesanan.show', $o) }}" class="btn btn-sm btn-outline-primary">Proses</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pesanan yang menunggu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection