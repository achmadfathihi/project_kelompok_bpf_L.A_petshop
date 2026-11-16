<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
        </svg>
        @if ($notifUnread > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $notifUnread > 9 ? '9+' : $notifUnread }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width:320px;max-width:92vw">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>Notifikasi</strong>
            @if ($notifUnread > 0)
                <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                    @csrf
                    <button class="btn btn-link btn-sm p-0">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @forelse ($notifList as $n)
            <a href="{{ route('notifikasi.buka', $n) }}" class="dropdown-item text-wrap border-bottom py-2 {{ $n->read_at ? '' : 'bg-light fw-semibold' }}">
                <div>{{ $n->title }}</div>
                @if ($n->body) <div class="small text-muted fw-normal">{{ $n->body }}</div> @endif
                <div class="small text-muted fw-normal">{{ $n->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <div class="px-3 py-3 text-muted small">Belum ada notifikasi.</div>
        @endforelse
    </div>
</li>