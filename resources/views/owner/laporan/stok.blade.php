@extends('layouts.admin')
@section('title', 'Laporan Stok')

@section('content')
    @include('layouts.admin.header', ['title' => 'Laporan Stok', 'subtitle' => 'Produk aktif. Stok yang dihitung hanya barang yang masih layak jual.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0 no-print">
                @php
                    $filters = ['semua' => ['Semua', $counts['habis'] + $counts['menipis'] + $counts['aman']], 'habis' => ['Habis', $counts['habis']], 'menipis' => ['Menipis', $counts['menipis']], 'aman' => ['Aman', $counts['aman']]];
                @endphp
                <ul class="nav nav-pills d-inline-flex">
                    @foreach ($filters as $key => [$label, $jumlah])
                        <li class="nav-item">
                            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('owner.laporan.stok', ['status' => $key]) }}">
                                {{ $label }} <span class="badge badge-pill ml-1 {{ $status === $key ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlah }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <button type="button" class="btn btn-sm btn-outline-primary float-right" onclick="window.print()">Cetak</button>
            </div>

            <div class="d-none d-print-block px-4 pt-3">
                <h3 class="mb-0">{{ config('app.name') }}</h3>
                <div>Laporan Stok, {{ now()->format('d/m/Y H:i') }}</div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>Produk</th><th>Kategori</th><th class="text-right">Stok layak jual</th><th class="text-right">Batas menipis</th><th>Status</th><th class="text-right">Sudah kedaluwarsa (belum dikeluarkan)</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $p)
                            <tr>
                                <td>{{ $p->name }}<small class="text-muted d-block">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</small></td>
                                <td>{{ $p->category->name }}</td>
                                <td class="text-right font-weight-bold">{{ $p->stock }}</td>
                                <td class="text-right">{{ $p->min_stock }}</td>
                                <td>
                                    @if ($p->stock_status === 'habis') <span class="badge badge-danger">Habis</span>
                                    @elseif ($p->stock_status === 'menipis') <span class="badge badge-warning">Menipis</span>
                                    @else <span class="badge badge-success">Aman</span> @endif
                                </td>
                                <td class="text-right">{{ (int) $p->expired_qty > 0 ? (int) $p->expired_qty : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection