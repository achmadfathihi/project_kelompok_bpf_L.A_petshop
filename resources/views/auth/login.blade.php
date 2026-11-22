@extends('layouts.auth')
@section('title', 'Masuk')

@section('content')
    <h1 class="h3 mb-1">Selamat datang kembali</h1>
    <p class="text-muted mb-4">Masuk untuk berbelanja, melihat pesanan, dan mengecek poin Anda.</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small">Lupa password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" autocomplete="current-password" required
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="lihat"
                       onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'">
                <label class="form-check-label small" for="lihat">Tampilkan password</label>
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
        </div>

        <button class="btn btn-primary w-100 py-2">Masuk</button>
    </form>

    <p class="text-center mt-4 mb-0">Belum punya akun? <a href="{{ route('register') }}" class="fw-semibold">Daftar sekarang</a></p>
@endsection