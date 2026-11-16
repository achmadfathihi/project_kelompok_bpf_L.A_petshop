@extends('layouts.admin')
@section('title', $product->exists ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
    @include('layouts.admin.header', [
        'title'    => $product->exists ? 'Ubah Produk' : 'Tambah Produk',
        'subtitle' => 'Stok tidak diisi di sini. Catat lewat menu Stok Masuk/Keluar.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data"
                      action="{{ $product->exists ? route('karyawan.produk.update', $product) : route('karyawan.produk.store') }}">
                    @csrf
                    @if ($product->exists) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Nama produk</label>
                            <input type="text" name="name" class="form-control form-control-alternative"
                                   value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Kategori</label>
                            <select name="category_id" class="form-control form-control-alternative" required>
                                <option value="">— pilih —</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Merek</label>
                            <input type="text" name="brand" class="form-control form-control-alternative"
                                   value="{{ old('brand', $product->brand) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Ukuran / isi</label>
                            <input type="text" name="size" class="form-control form-control-alternative"
                                   value="{{ old('size', $product->size) }}" placeholder="contoh: 1 kg, 10 L">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Harga (Rp)</label>
                            <input type="number" name="price" min="0" class="form-control form-control-alternative"
                                   value="{{ old('price', $product->price) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Batas stok menipis</label>
                            <input type="number" name="min_stock" min="0" class="form-control form-control-alternative"
                                   value="{{ old('min_stock', $product->min_stock) }}" required>
                            <small class="text-muted">Stok ≤ angka ini ditandai "menipis".</small>
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="form-control-label">Foto produk (jpg/png/webp, maks. 2 MB)</label>
                            <input type="file" name="photo" accept="image/*" class="form-control-file">
                            @if ($product->exists && $product->photo)
                                <img src="{{ $product->photo_url }}" alt="" class="rounded mt-2" style="height:90px">
                                <small class="text-muted d-block">Biarkan kosong kalau tidak ingin mengganti foto.</small>
                            @endif
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox mb-4">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                               @checked(old('is_active', $product->is_active))>
                        <label class="custom-control-label" for="is_active">Aktif (tampil di katalog)</label>
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('karyawan.produk.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection