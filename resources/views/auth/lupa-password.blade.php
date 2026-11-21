@extends('layouts.app')
@section('title', 'Lupa Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-2">Lupa Password</h4>
                <p class="text-muted small">
                    Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.
                    Fitur ini untuk akun pelanggan. Staf toko menghubungi owner.
                </p>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>
                    <button class="btn btn-primary w-100">Kirim Tautan</button>
                </form>
                <p class="mt-3 mb-0 small"><a href="{{ route('login') }}">&larr; Kembali ke halaman masuk</a></p>
            </div>
        </div>
    </div>
</div>
@endsection