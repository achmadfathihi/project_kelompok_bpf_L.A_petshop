<li class="nav-item dropdown">
    <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
       aria-label="Notifikasi" style="position:relative">
        <i class="fas fa-bell fa-lg"></i>
        @if ($notifUnread > 0)
            <span class="badge badge-danger badge-pill" style="position:absolute;top:-2px;right:-4px;font-size:.6rem">{{ $notifUnread > 9 ? '9+' : $notifUnread }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right p-0" style="min-width:320px;max-width:92vw">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong class="text-dark">Notifikasi</strong>
            @if ($notifUnread > 0)
                <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                    @csrf
                    <button class="btn btn-link btn-sm p-0">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @forelse ($notifList as $n)
            <a href="{{ route('notifikasi.buka', $n) }}" class="dropdown-item border-bottom py-2 {{ $n->read_at ? '' : 'bg-secondary' }}"
               style="white-space:normal">
                <div class="{{ $n->read_at ? '' : 'font-weight-bold' }}">{{ $n->title }}</div>
                @if ($n->body) <div class="small text-muted">{{ $n->body }}</div> @endif
                <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <div class="px-3 py-3 text-muted small">Belum ada notifikasi.</div>
        @endforelse
    </div>
</li>