@extends('layouts.admin')
@section('title', 'Ganti Password')

@section('content')
    @include('layouts.admin.header', ['title' => 'Ganti Password'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-body" style="max-width:520px">
                <form method="POST" action="{{ route('akun.password.update') }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="form-control-label">Password saat ini</label>
                        <input type="password" name="current_password" class="form-control form-control-alternative" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Password baru (min. 8 karakter)</label>
                        <input type="password" name="password" class="form-control form-control-alternative" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Ulangi password baru</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-alternative" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection