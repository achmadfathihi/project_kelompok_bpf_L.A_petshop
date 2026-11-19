@extends('layouts.admin')
@section('title', $staff->exists ? 'Ubah Akun Staf' : 'Tambah Akun Staf')

@section('content')
    @include('layouts.admin.header', ['title' => $staff->exists ? 'Ubah Akun Staf' : 'Tambah Akun Staf'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ $staff->exists ? route('owner.staf.update', $staff) : route('owner.staf.store') }}">
                    @csrf
                    @if ($staff->exists) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Nama</label>
                            <input type="text" name="name" class="form-control form-control-alternative" value="{{ old('name', $staff->name) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Role</label>
                            <select name="role" class="form-control form-control-alternative" required>
                                <option value="kasir" @selected(old('role', $staff->role) === 'kasir')>Kasir</option>
                                <option value="karyawan" @selected(old('role', $staff->role) === 'karyawan')>Karyawan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Email (untuk masuk)</label>
                            <input type="email" name="email" class="form-control form-control-alternative" value="{{ old('email', $staff->email) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Nomor HP</label>
                            <input type="text" name="phone" class="form-control form-control-alternative" value="{{ old('phone', $staff->phone) }}" required>
                        </div>
                    </div>

                    @unless ($staff->exists)
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-control-label">Password awal (min. 8 karakter)</label>
                                <input type="password" name="password" class="form-control form-control-alternative" required autocomplete="new-password">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-control-label">Ulangi password</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-alternative" required autocomplete="new-password">
                            </div>
                        </div>
                        <p class="small text-muted">Staf bisa menggantinya sendiri lewat menu Ganti Password setelah masuk.</p>
                    @endunless

                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('owner.staf.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>

        @if ($staff->exists)
            <div class="card shadow">
                <div class="card-body">
                    <h4>Reset Password</h4>
                    <p class="small text-muted">Untuk staf yang lupa password. Semua sesi lamanya dikeluarkan, jadi ia harus masuk lagi dengan password baru.</p>
                    <form method="POST" action="{{ route('owner.staf.password', $staff) }}"
                          onsubmit="return confirm('Ganti password akun ini?')">
                        @csrf
                        <div class="row">
                            <div class="col-md-5 form-group">
                                <input type="password" name="password" class="form-control" placeholder="Password baru (min. 8 karakter)" required autocomplete="new-password">
                            </div>
                            <div class="col-md-5 form-group">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" required autocomplete="new-password">
                            </div>
                            <div class="col-md-2 form-group">
                                <button class="btn btn-outline-danger btn-block">Ganti</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection