@extends('layouts.admin')
@section('title', 'Stok Masuk/Keluar')

@section('content')
    @include('layouts.admin.header', ['title' => 'Riwayat Stok'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <a href="{{ route('karyawan.stok.index') }}" class="btn btn-sm {{ request('jenis') ? 'btn-outline-primary' : 'btn-primary' }}">Semua</a>
                        <a href="{{ route('karyawan.stok.index', ['jenis' => 'in']) }}" class="btn btn-sm {{ request('jenis') === 'in' ? 'btn-primary' : 'btn-outline-primary' }}">Masuk</a>
                        <a href="{{ route('karyawan.stok.index', ['jenis' => 'out']) }}" class="btn btn-sm {{ request('jenis') === 'out' ? 'btn-primary' : 'btn-outline-primary' }}">Keluar</a>
                    </div>
                    <div class="col text-right">
                        <a href="{{ route('karyawan.stok.create') }}" class="btn btn-sm btn-primary">Catat Stok</a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Batch (kedaluwarsa)</th><th>Catatan</th><th>Dicatat oleh</th></tr>
                </thead>
                <tbody>
                    @forelse ($movements as $m)
                        <tr>
                            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $m->product->name }}</td>
                            <td>
                                @if ($m->type === 'in') <span class="badge badge-success">Masuk</span>
                                @else <span class="badge badge-warning">Keluar</span> @endif
                            </td>
                            <td>{{ $m->qty }}</td>
                            <td>
                                @if ($m->batch)
                                    #{{ $m->batch->id }} · {{ $m->batch->expired_at?->format('d/m/Y') ?? 'tanpa kedaluwarsa' }}
                                @else - @endif
                            </td>
                            <td>{{ $m->note ?? '-' }}</td>
                            <td>{{ $m->user->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada catatan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div class="card-footer py-4">
                {{ $movements->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection