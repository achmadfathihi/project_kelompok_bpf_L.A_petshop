@extends('layouts.app')
@section('title', 'Poin Saya')

@section('content')
    <div class="p-4 mb-4 bg-white rounded shadow-sm">
        <div class="text-muted">Saldo poin Anda</div>
        <div class="display-5 fw-bold">{{ number_format($saldo, 0, ',', '.') }}</div>
        <div class="text-muted small">
            Setara potongan Rp{{ number_format($saldo, 0, ',', '.') }}.
            Setiap belanja kelipatan Rp25.000 mendapat 300 poin, dan 1 poin = Rp1 potongan harga.
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Riwayat Poin</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Tanggal</th><th>Keterangan</th><th>Jenis</th><th class="text-end">Poin</th><th class="text-end">Saldo</th></tr></thead>
                <tbody>
                    @forelse ($history as $h)
                        @php [$label, $warna] = \App\Models\PointTransaction::TYPES[$h->type] ?? [$h->type, 'secondary']; @endphp
                        <tr>
                            <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $h->note }}</td>
                            <td><span class="badge text-bg-{{ $warna }}">{{ $label }}</span></td>
                            <td class="text-end {{ $h->points < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $h->points > 0 ? '+' : '' }}{{ number_format($h->points, 0, ',', '.') }}
                            </td>
                            <td class="text-end">{{ number_format($h->balance_after, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat poin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $history->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection