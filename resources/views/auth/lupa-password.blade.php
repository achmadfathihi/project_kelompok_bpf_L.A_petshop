@extends('layouts.auth')
@section('title', 'Lupa Password')

@section('content')
    <h1 class="h3 mb-1">Lupa password?</h1>
    <p class="text-muted mb-4">
        Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.
        Fitur ini untuk akun pelanggan. Staf toko menghubungi owner.
    </p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-4">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button class="btn btn-primary w-100 py-2">Kirim Tautan</button>
    </form>

    <p class="text-center mt-4 mb-0"><a href="{{ route('login') }}">&larr; Kembali ke halaman masuk</a></p>
@endsection