@extends('layouts.admin')
@section('title', 'Dashboard Karyawan')

@section('content')
    @include('layouts.admin.header', [
        'title'    => 'Dashboard Karyawan',
        'subtitle' => 'Pantau stok dan barang yang mendekati kedaluwarsa.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')
                @if ($perluDisiapkan > 0)
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span><strong>{{ $perluDisiapkan }}</strong> pesanan online sudah dibayar dan perlu disiapkan.</span>
                <a href="{{ route('karyawan.pesanan.index') }}" class="btn btn-sm btn-primary">Lihat</a>
            </div>
        @endif

        @php
            $cards = [
                ['Produk Aktif',  $totalProduk,    'fas fa-box',                'bg-info'],
                ['Stok Habis',    $habis,          'fas fa-ban',                'bg-danger'],
                ['Stok Menipis',  $menipis,        'fas fa-exclamation-triangle', 'bg-warning'],
                ['Hampir Kedaluwarsa', $expiring->count(), 'fas fa-hourglass-half', 'bg-orange'],
            ];
        @endphp

        <div class="row">
            @foreach ($cards as [$label, $value, $icon, $color])
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ $label }}</h5>
                                    <span class="h2 font-weight-bold mb-0">{{ $value }}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape {{ $color }} text-white rounded-circle shadow">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            {{-- Stok habis / menipis --}}
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header border-0"><h3 class="mb-0">Stok Habis &amp; Menipis</h3></div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr><th>Produk</th><th>Stok</th><th>Batas</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStock as $p)
                                    <tr>
                                        <td>{{ $p->name }} <small class="text-muted d-block">{{ $p->category->name }}</small></td>
                                        <td>
                                            @if ($p->stock == 0)
                                                <span class="badge badge-danger">Habis</span>
                                            @else
                                                <span class="badge badge-warning">{{ $p->stock }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $p->min_stock }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('karyawan.stok.create', ['product_id' => $p->id]) }}" class="btn btn-sm btn-primary">Catat masuk</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Semua stok aman.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kedaluwarsa per batch --}}
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col"><h3 class="mb-0">Batch Mendekati Kedaluwarsa (≤ 30 hari)</h3></div>
                            <div class="col text-right">
                                <a href="{{ route('karyawan.batch.index') }}" class="btn btn-sm btn-outline-primary">Semua batch</a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr><th>Produk</th><th>Sisa</th><th>Kedaluwarsa</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($expiring as $b)
                                    <tr>
                                        <td>{{ $b->product->name }}</td>
                                        <td>{{ $b->qty_left }}</td>
                                        <td>{{ $b->expired_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($b->days_left < 0)
                                                <span class="badge badge-danger">Lewat {{ abs($b->days_left) }} hari</span>
                                            @elseif ($b->days_left <= 14)
                                                <span class="badge badge-warning">{{ $b->days_left }} hari lagi</span>
                                            @else
                                                <span class="badge badge-info">{{ $b->days_left }} hari lagi</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('karyawan.batch.keluarkan', $b) }}" class="d-inline"
                                                onsubmit="return confirm('Keluarkan sisa {{ $b->qty_left }} buah dari stok?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Keluarkan</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada batch yang mendekati kedaluwarsa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection