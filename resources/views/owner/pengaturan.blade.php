@extends('layouts.admin')
@section('title', 'Pengaturan Toko')

@section('content')
    @include('layouts.admin.header', ['title' => 'Pengaturan Toko', 'subtitle' => 'Data pembayaran yang ditampilkan kepada pelanggan.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('owner.pengaturan.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <h4 class="mb-3">Kontak Toko</h4>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Nomor telepon / WhatsApp</label>
                            <input type="text" name="store_phone" class="form-control form-control-alternative"
                                value="{{ old('store_phone', $setting->get('store_phone')) }}" placeholder="contoh: 0812 3456 7890">
                        </div>
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label class="form-control-label">Alamat lengkap toko (tampil di struk)</label>
                                <input type="text" name="store_address" class="form-control form-control-alternative"
                                    value="{{ old('store_address', $setting->get('store_address')) }}" placeholder="contoh: Jl. ..., Rumbai, Pekanbaru">
                            </div>
                            <div class="col-md-8 form-group">
                                <label class="form-control-label">Pesan penutup struk</label>
                                <input type="text" name="receipt_footer" class="form-control form-control-alternative"
                                    value="{{ old('receipt_footer', $setting->get('receipt_footer')) }}" placeholder="Terima kasih sudah berbelanja!">
                            </div>
                        </div>
                    </div>
                    <h4 class="mb-3">Transfer Bank</h4>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Nama bank</label>
                            <input type="text" name="bank_name" class="form-control form-control-alternative" value="{{ old('bank_name', $setting->get('bank_name')) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Nomor rekening</label>
                            <input type="text" name="bank_account" class="form-control form-control-alternative" value="{{ old('bank_account', $setting->get('bank_account')) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Atas nama</label>
                            <input type="text" name="bank_holder" class="form-control form-control-alternative" value="{{ old('bank_holder', $setting->get('bank_holder')) }}">
                        </div>
                    </div>

                    <h4 class="mt-3 mb-3">QRIS</h4>
                    <div class="form-group">
                        <label class="form-control-label">Gambar QRIS (jpg/png/webp, maks. 2 MB)</label>
                        <input type="file" name="qris_image" accept="image/*" class="form-control-file">
                        @if ($setting->get('qris_image'))
                            <img src="{{ asset('storage/' . $setting->get('qris_image')) }}" alt="QRIS" class="img-thumbnail mt-2" style="max-height:180px">
                            <small class="text-muted d-block">Biarkan kosong jika tidak ingin mengganti.</small>
                        @endif
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection