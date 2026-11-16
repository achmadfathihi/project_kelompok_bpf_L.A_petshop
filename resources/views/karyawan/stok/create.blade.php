@extends('layouts.admin')
@section('title', 'Catat Stok')

@section('content')
    @include('layouts.admin.header', [
        'title'    => 'Catat Stok',
        'subtitle' => 'Catatan yang sudah disimpan tidak bisa diubah. Kalau salah, buat catatan koreksi.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('karyawan.stok.store') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-control-label">Produk</label>
                        <select name="product_id" class="form-control form-control-alternative" required>
                            <option value="">— pilih produk —</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id', $selected) == $p->id)>
                                    {{ $p->name }}{{ $p->size ? ' (' . $p->size . ')' : '' }} — stok {{ $p->stock }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label d-block">Jenis</label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="type_in" name="type" value="in" class="custom-control-input"
                                   @checked(old('type', 'in') === 'in')>
                            <label class="custom-control-label" for="type_in">Barang masuk</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="type_out" name="type" value="out" class="custom-control-input"
                                   @checked(old('type') === 'out')>
                            <label class="custom-control-label" for="type_out">Barang keluar</label>
                        </div>
                    </div>

                    <p class="text-sm text-muted" id="info-fefo" style="display:none">
                        Barang keluar otomatis mengambil dari batch yang paling dekat kedaluwarsanya.
                        Batch yang sudah kedaluwarsa tidak ikut diambil.
                    </p>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Jumlah</label>
                            <input type="number" name="qty" min="1" class="form-control form-control-alternative"
                                   value="{{ old('qty') }}" required>
                        </div>
                        <div class="col-md-4 form-group" id="box-expired">
                            <label class="form-control-label">Tanggal kedaluwarsa</label>
                            <input type="date" name="expired_at" class="form-control form-control-alternative"
                                value="{{ old('expired_at') }}" min="{{ today()->addDay()->format('Y-m-d') }}">
                            <small class="text-muted">Isi sesuai tanggal di kemasan. Kosongkan jika tidak ada.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Catatan <span id="note-wajib" class="text-danger" style="display:none">(wajib)</span></label>
                            <input type="text" name="note" class="form-control form-control-alternative"
                                   value="{{ old('note') }}" placeholder="contoh: rusak, koreksi">
                        </div>
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('karyawan.stok.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    // Kolom kedaluwarsa hanya untuk barang masuk; catatan wajib untuk barang keluar
    function ubahJenis() {
        var keluar = document.getElementById('type_out').checked;
        document.getElementById('box-expired').style.display = keluar ? 'none' : '';
        document.getElementById('note-wajib').style.display  = keluar ? '' : 'none';
        document.getElementById('info-fefo').style.display = keluar ? '' : 'none';
    }
    document.getElementById('type_in').addEventListener('change', ubahJenis);
    document.getElementById('type_out').addEventListener('change', ubahJenis);
    ubahJenis();
</script>
@endpush