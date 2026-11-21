@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Profil Saya</h4>
                    <form method="POST" action="{{ route('profil.update') }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat utama</label>
                            <textarea name="address" rows="3" class="form-control"
                                      placeholder="Terisi otomatis saat checkout">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <button class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Ganti Password</h5>
                    <form method="POST" action="{{ route('profil.password') }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Password saat ini</label>
                            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password baru (min. 8 karakter)</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ulangi password baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <button class="btn btn-outline-primary">Ganti Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection