@extends('layouts.auth')
@section('title', 'Atur Ulang Password')

@section('content')
    <h1 class="h3 mb-1">Buat password baru</h1>
    <p class="text-muted mb-4">Pilih password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" readonly required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password baru <span class="text-muted fw-normal">(min. 8 karakter)</span></label>
            <input id="password" type="password" name="password" autocomplete="new-password" autofocus required
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Ulangi password baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="form-control">
        </div>

        <button class="btn btn-primary w-100 py-2">Simpan Password</button>
    </form>
@endsection