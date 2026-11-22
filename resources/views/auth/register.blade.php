@extends('layouts.auth')
@section('title', 'Daftar')

@section('content')
    <h1 class="h3 mb-1">Buat akun member</h1>
    <p class="text-muted mb-4">Gratis. Dapatkan poin di setiap belanja dan pantau pesanan Anda.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" autofocus required
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="phone">Nomor HP</label>
            <input id="phone" type="tel" inputmode="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required
                   class="form-control @error('phone') is-invalid @enderror" placeholder="contoh: 081234567890">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password <span class="text-muted fw-normal">(min. 8 karakter)</span></label>
            <input id="password" type="password" name="password" autocomplete="new-password" required
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Ulangi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="form-control">
        </div>

        <button class="btn btn-primary w-100 py-2">Daftar</button>
    </form>

    <p class="text-center mt-4 mb-0">Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold">Masuk</a></p>
@endsection