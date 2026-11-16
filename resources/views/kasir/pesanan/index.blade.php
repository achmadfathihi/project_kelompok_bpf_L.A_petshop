@extends('layouts.admin')
@section('title', 'Pesanan')

@section('content')
    @include('layouts.admin.header', ['title' => 'Pesanan & Transaksi'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <ul class="nav nav-pills mb-3">
                    @php
                        $tabs = [
                            'tindakan' => ['Perlu tindakan', $perluOngkir + $perluVerifikasi],
                            'online'   => ['Online', null],
                            'toko'     => ['Di toko', null],
                            'semua'    => ['Semua', null],
                        ];
                    @endphp
                    @foreach ($tabs as $key => [$label, $jumlah])
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('kasir.pesanan.index', ['tab' => $key]) }}">
                                {{ $label }}
                                @if ($jumlah)
                                    <span class="badge badge-pill ml-1 {{ $tab === $key ? 'bg-white text-primary' : 'badge-danger' }}">{{ $jumlah }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form method="GET" class="form-inline">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nomor pesanan">
                    <button class="btn btn-sm btn-primary">Cari</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>No.</th><th>Waktu</th><th>Jenis</th><th>Pelanggan</th><th>Total</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr>
                                <td>{{ $o->invoice_no }}</td>
                                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($o->isOnline()) <span class="badge badge-info">Online</span>
                                    @else <span class="badge badge-secondary">Di toko</span> @endif
                                </td>
                                <td>{{ $o->customer?->name ?? 'Pembeli umum' }}</td>
                                <td>{{ $o->grand_total === null ? 'Menunggu ongkir' : 'Rp' . number_format($o->grand_total, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
                                <td class="text-right"><a href="{{ route('kasir.pesanan.show', $o) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer py-4">{{ $orders->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
@endsection