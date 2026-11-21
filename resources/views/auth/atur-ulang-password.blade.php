@extends('layouts.app')
@section('title', 'Atur Ulang Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Buat Password Baru</h4>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" required readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password baru (min. 8 karakter)</label>
                        <input type="password" name="password" class="form-control" required autofocus autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi password baru</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary w-100">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection