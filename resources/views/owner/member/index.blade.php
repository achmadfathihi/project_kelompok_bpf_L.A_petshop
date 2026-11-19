@extends('layouts.admin')
@section('title', 'Daftar Member')

@section('content')
    @include('layouts.admin.header', ['title' => 'Daftar Member', 'subtitle' => 'Pelanggan yang sudah mendaftar. Hanya untuk dilihat.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="row">
            @include('layouts.admin.stat', ['label' => 'Total Member', 'value' => $totalMember, 'icon' => 'fas fa-users', 'color' => 'bg-info'])
            @include('layouts.admin.stat', ['label' => 'Poin Beredar', 'value' => number_format($poinBeredar, 0, ',', '.'), 'note' => 'senilai potongan Rp' . number_format($poinBeredar, 0, ',', '.'), 'icon' => 'fas fa-star', 'color' => 'bg-warning'])
        </div>

        <div class="card shadow">
            <div class="card-header border-0">
                <form method="GET" class="form-inline">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Cari nama, email, atau nomor HP">
                    <button class="btn btn-sm btn-primary">Cari</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light"><tr><th>Nama</th><th>Email</th><th>Nomor HP</th><th class="text-right">Poin</th><th>Daftar</th></tr></thead>
                    <tbody>
                        @forelse ($members as $m)
                            <tr>
                                <td>{{ $m->name }}</td>
                                <td>{{ $m->email }}</td>
                                <td>{{ $m->phone }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($m->points, 0, ',', '.') }}</td>
                                <td>{{ $m->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Member tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-4">{{ $members->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
@endsection