@extends('layouts.admin')
@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
    @include('layouts.admin.header', ['title' => $category->exists ? 'Ubah Kategori' : 'Tambah Kategori'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-body">
                <form method="POST"
                      action="{{ $category->exists ? route('karyawan.kategori.update', $category) : route('karyawan.kategori.store') }}">
                    @csrf
                    @if ($category->exists) @method('PUT') @endif

                    <div class="form-group">
                        <label class="form-control-label">Nama kategori</label>
                        <input type="text" name="name" class="form-control form-control-alternative"
                               value="{{ old('name', $category->name) }}" required autofocus>
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('karyawan.kategori.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection