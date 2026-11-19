@extends('layouts.admin')
@section('title', 'Akun Staf')

@section('content')
    @include('layouts.admin.header', ['title' => 'Akun Staf', 'subtitle' => 'Akun karyawan dan kasir. Akun dinonaktifkan, bukan dihapus, supaya riwayat kerjanya tetap utuh.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'aktif' ? 'active' : '' }}" href="{{ route('owner.staf.index') }}">
                                    Aktif <span class="badge badge-pill ml-1 {{ $tab === 'aktif' ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlahAktif }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'nonaktif' ? 'active' : '' }}" href="{{ route('owner.staf.index', ['tab' => 'nonaktif']) }}">
                                    Nonaktif <span class="badge badge-pill ml-1 {{ $tab === 'nonaktif' ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlahNonaktif }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-2 mt-lg-0">
                        <a href="{{ route('owner.staf.create') }}" class="btn btn-sm btn-primary">Tambah Akun</a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light"><tr><th>Nama</th><th>Email</th><th>Nomor HP</th><th>Role</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($staff as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->phone }}</td>
                                <td><span class="badge badge-{{ $s->role === 'kasir' ? 'warning' : 'info' }}">{{ ucfirst($s->role) }}</span></td>
                                <td class="text-right text-nowrap">
                                    <a href="{{ route('owner.staf.edit', $s) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                                    <form method="POST" action="{{ route('owner.staf.status', $s) }}" class="d-inline"
                                          @if ($s->is_active) onsubmit="return confirm('Nonaktifkan akun ini? Ia langsung keluar dari semua sesi dan tidak bisa masuk lagi.')" @endif>
                                        @csrf
                                        @if ($s->is_active)
                                            <button class="btn btn-sm btn-outline-warning">Nonaktifkan</button>
                                        @else
                                            <button class="btn btn-sm btn-outline-success">Aktifkan</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">{{ $tab === 'aktif' ? 'Belum ada akun staf aktif.' : 'Tidak ada akun nonaktif.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection