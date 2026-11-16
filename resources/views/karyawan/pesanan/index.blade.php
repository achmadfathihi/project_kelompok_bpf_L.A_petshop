@extends('layouts.admin')
@section('title', 'Pesanan Online')

@section('content')
    @include('layouts.admin.header', ['title' => 'Pesanan Online', 'subtitle' => 'Pesanan yang sudah dibayar: siapkan, kirim lewat ojek online, lalu tandai selesai.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <ul class="nav nav-pills">
                    @php
                        $tabs = [
                            'siapkan' => ['Perlu disiapkan', $jumlahSiapkan],
                            'dikirim' => ['Dikirim', $jumlahDikirim],
                            'selesai' => ['Selesai', null],
                            'semua'   => ['Semua', null],
                        ];
                    @endphp
                    @foreach ($tabs as $key => [$label, $jumlah])
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('karyawan.pesanan.index', ['tab' => $key]) }}">
                                {{ $label }}
                                @if ($jumlah)
                                    <span class="badge badge-pill ml-1 {{ $tab === $key ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlah }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>No. Pesanan</th><th>Dibayar</th><th>Penerima</th><th>Jenis barang</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr>
                                <td>{{ $o->invoice_no }}</td>
                                <td>{{ $o->paid_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $o->recipient_name }}</td>
                                <td>{{ $o->items_count }}</td>
                                <td><span class="badge badge-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('karyawan.pesanan.show', $o) }}" class="btn btn-sm btn-outline-primary">
                                        {{ $o->status === 'diproses' ? 'Siapkan' : 'Detail' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer py-4">{{ $orders->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
@endsection