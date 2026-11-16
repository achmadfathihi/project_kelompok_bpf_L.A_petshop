@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
    <h3 class="mb-3">Pesanan Saya</h3>

    @if ($orders->isEmpty())
        <div class="alert alert-info">Belum ada pesanan. <a href="{{ route('beranda') }}">Mulai belanja</a></div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>No. Pesanan</th><th>Tanggal</th><th>Jenis barang</th><th>Total barang</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $o)
                            <tr>
                                <td>{{ $o->invoice_no }}</td>
                                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $o->items_count }}</td>
                                <td>@rp($o->items_total)</td>
                                <td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
                                <td class="text-end"><a href="{{ route('pesanan.show', $o) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $orders->links('pagination::bootstrap-5') }}</div>
        </div>
    @endif
@endsection