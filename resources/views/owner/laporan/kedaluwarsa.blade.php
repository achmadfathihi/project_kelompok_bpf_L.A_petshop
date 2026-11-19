@extends('layouts.admin')
@section('title', 'Laporan Kedaluwarsa')

@section('content')
    @include('layouts.admin.header', ['title' => 'Laporan Kedaluwarsa', 'subtitle' => 'Batch yang masih ada sisanya, urut dari yang paling dekat kedaluwarsa.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="row">
            @include('layouts.admin.stat', ['label' => 'Batch Sudah Lewat', 'value' => $jumlahLewat, 'note' => 'masih tercatat ada sisa', 'icon' => 'fas fa-ban', 'color' => 'bg-danger'])
            @include('layouts.admin.stat', ['label' => 'Unit Sudah Lewat', 'value' => $unitLewat, 'icon' => 'fas fa-box-open', 'color' => 'bg-warning'])
            @include('layouts.admin.stat', ['label' => 'Perkiraan Nilai', 'value' => 'Rp' . number_format($nilaiLewat, 0, ',', '.'), 'note' => 'menurut harga jual', 'icon' => 'fas fa-coins', 'color' => 'bg-default'])
        </div>

        <div class="card shadow">
            <div class="card-header border-0 no-print">
                @php $pilihan = ['lewat' => 'Sudah lewat', '7' => '≤ 7 hari', '30' => '≤ 30 hari', '90' => '≤ 90 hari', 'semua' => 'Semua']; @endphp
                <ul class="nav nav-pills d-inline-flex">
                    @foreach ($pilihan as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $rentang === (string) $key ? 'active' : '' }}" href="{{ route('owner.laporan.kedaluwarsa', ['rentang' => $key]) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
                <button type="button" class="btn btn-sm btn-outline-primary float-right" onclick="window.print()">Cetak</button>
            </div>

            <div class="d-none d-print-block px-4 pt-3">
                <h3 class="mb-0">{{ config('app.name') }}</h3>
                <div>Laporan Kedaluwarsa, {{ now()->format('d/m/Y H:i') }}</div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>Produk</th><th>Kategori</th><th>Masuk</th><th class="text-right">Sisa</th><th>Kedaluwarsa</th><th>Status</th><th class="text-right">Perkiraan nilai</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr>
                                <td>{{ $b->product->name }}</td>
                                <td>{{ $b->product->category->name }}</td>
                                <td>{{ $b->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">{{ $b->qty_left }}</td>
                                <td>{{ $b->expired_at->format('d/m/Y') }}</td>
                                <td>
                                    @if ($b->days_left < 0) <span class="badge badge-danger">Lewat {{ abs($b->days_left) }} hari</span>
                                    @elseif ($b->days_left <= 14) <span class="badge badge-warning">{{ $b->days_left }} hari lagi</span>
                                    @else <span class="badge badge-info">{{ $b->days_left }} hari lagi</span> @endif
                                </td>
                                <td class="text-right">@rp($b->qty_left * $b->product->price)</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada batch pada rentang ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="small text-muted mt-3 no-print">
            Batch yang sudah lewat dikeluarkan lewat menu Karyawan, Batch &amp; Kedaluwarsa, tombol "Keluarkan sisa".
        </p>
    </div>
@endsection