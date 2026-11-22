<div class="ps-pagehead">
    <div class="container">
        <nav class="ps-crumb" aria-label="Jejak halaman">
            <a href="{{ route('beranda') }}">Beranda</a> &rsaquo;
            @isset($induk) <a href="{{ $indukUrl }}">{{ $induk }}</a> &rsaquo; @endisset
            {{ $judul }}
        </nav>
        <h1>{{ $judul }}</h1>
        @isset($sub) <p class="text-muted mb-0 mt-1">{{ $sub }}</p> @endisset
    </div>
</div>