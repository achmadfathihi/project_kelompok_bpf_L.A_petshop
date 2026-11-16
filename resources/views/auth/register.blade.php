@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Daftar Akun Pelanggan</h4>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (min. 8 karakter)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Daftar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection