@extends('layouts.admin')
@section('title', 'Batch & Kedaluwarsa')

@section('content')
    @include('layouts.admin.header', [
        'title'    => 'Batch & Kedaluwarsa',
        'subtitle' => 'Urut dari yang paling dekat kedaluwarsa. Barang keluar otomatis mengambil dari atas.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <a href="{{ route('karyawan.batch.index') }}" class="btn btn-sm {{ request('semua') ? 'btn-outline-primary' : 'btn-primary' }}">Yang masih ada sisa</a>
                <a href="{{ route('karyawan.batch.index', ['semua' => 1]) }}" class="btn btn-sm {{ request('semua') ? 'btn-primary' : 'btn-outline-primary' }}">Semua batch</a>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>Produk</th><th>Masuk</th><th>Jumlah masuk</th><th>Sisa</th><th>Kedaluwarsa</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr>
                                <td>{{ $b->product->name }}</td>
                                <td>{{ $b->created_at->format('d/m/Y') }}</td>
                                <td>{{ $b->qty_in }}</td>
                                <td><strong>{{ $b->qty_left }}</strong></td>
                                <td>{{ $b->expired_at?->format('d/m/Y') ?? 'Tanpa kedaluwarsa' }}</td>
                                <td>
                                    @switch($b->status)
                                        @case('habis') <span class="badge badge-secondary">Habis</span> @break
                                        @case('kedaluwarsa') <span class="badge badge-danger">Lewat {{ abs($b->days_left) }} hari</span> @break
                                        @case('segera') <span class="badge badge-warning">{{ $b->days_left }} hari lagi</span> @break
                                        @default <span class="badge badge-success">Baik</span>
                                    @endswitch
                                </td>
                                <td class="text-right">
                                    @if ($b->qty_left > 0)
                                        <form method="POST" action="{{ route('karyawan.batch.keluarkan', $b) }}" class="d-inline"
                                              onsubmit="return confirm('Keluarkan seluruh sisa batch ini ({{ $b->qty_left }} buah)?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">Keluarkan sisa</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada batch.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer py-4">
                {{ $batches->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection