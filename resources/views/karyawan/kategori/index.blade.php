@extends('layouts.admin')
@section('title', 'Kategori')

@section('content')
    @include('layouts.admin.header', ['title' => 'Kategori Produk'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col"><h3 class="mb-0">Daftar Kategori</h3></div>
                    <div class="col text-right">
                        <a href="{{ route('karyawan.kategori.create') }}" class="btn btn-sm btn-primary">Tambah Kategori</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>Nama</th><th>Jumlah Produk</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->products_count }}</td>
                                <td class="text-right">
                                    <a href="{{ route('karyawan.kategori.edit', $c) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                                    <form method="POST" action="{{ route('karyawan.kategori.destroy', $c) }}" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection