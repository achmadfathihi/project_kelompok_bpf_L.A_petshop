@extends('layouts.admin')

@section('title', $judul)

@section('content')
    @include('layouts.admin.header', [
        'title'    => $judul,
        'subtitle' => 'Halaman ini akan diisi pada tahap berikutnya.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <h3 class="mb-0">{{ $judul }}</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    Login sebagai <strong>{{ auth()->user()->name }}</strong>
                    ({{ auth()->user()->role }})
                </p>
            </div>
        </div>

        <footer class="footer">
            <div class="copyright text-center text-xl-left text-muted">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </div>
        </footer>
    </div>
@endsection