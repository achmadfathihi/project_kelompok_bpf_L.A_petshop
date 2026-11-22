@extends('layouts.app')
@section('title', 'Poin Saya')

@section('wide')
@php
    $user    = auth()->user();
    $step    = \App\Models\Order::POINT_STEP;
    $reward  = \App\Models\Order::POINT_REWARD;
    $angka   = fn ($n) => number_format($n, 0, ',', '.');
    $jenis   = [
        'earn'   => ['i-star',    'ps-hist-earn',   'Poin didapat'],
        'redeem' => ['i-gift',    'ps-hist-redeem', 'Dipakai'],
        'refund' => ['i-refresh', 'ps-hist-refund', 'Dikembalikan'],
    ];
    $tabs = ['semua' => 'Semua', 'masuk' => 'Poin masuk', 'dipakai' => 'Dipakai'];
@endphp

@include('layouts.partials.page-head', ['judul' => 'Poin Saya', 'sub' => 'Kumpulkan poin di setiap belanja dan pakai sebagai potongan harga.'])

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="ps-pointcard">
                <svg class="ps-pointcard-paw" aria-hidden="true"><use href="#i-paw"/></svg>
                <div class="ps-pointcard-brand">Member {{ config('app.name') }}</div>
                <div class="ps-pointcard-name">{{ $user->name }}</div>
                <div class="ps-pointcard-label">Saldo poin</div>
                <div class="ps-pointcard-balance">{{ $angka($saldo) }} <small>poin</small></div>
                <span class="ps-pointcard-value">Setara potongan <strong>Rp{{ $angka($saldo) }}</strong></span>
                <div class="ps-pointcard-foot">Member sejak {{ $user->created_at->translatedFormat('F Y') }}</div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-6"><div class="ps-mini"><small>Total didapat</small><strong class="text-success">+{{ $angka($didapat) }}</strong></div></div>
                <div class="col-6"><div class="ps-mini"><small>Total dipakai</small><strong style="color:#A94508">{{ $angka($dipakai) }}</strong></div></div>
            </div>

            <div class="ps-panel mt-4">
                <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-gift"/></svg> Cara Kerja Poin</div>
                <ul class="ps-how">
                    <li>
                        <span class="ps-how-icon"><svg width="20" height="20"><use href="#i-cart"/></svg></span>
                        <div><strong>Belanja</strong><div class="small text-muted">Setiap kelipatan Rp{{ $angka($step) }} dari harga barang mendapat {{ $angka($reward) }} poin.</div></div>
                    </li>
                    <li>
                        <span class="ps-how-icon"><svg width="20" height="20"><use href="#i-check"/></svg></span>
                        <div><strong>Poin masuk</strong><div class="small text-muted">Otomatis setelah pembayaran diverifikasi kasir.</div></div>
                    </li>
                    <li>
                        <span class="ps-how-icon"><svg width="20" height="20"><use href="#i-tag"/></svg></span>
                        <div><strong>Pakai saat checkout</strong><div class="small text-muted">1 poin = potongan Rp1, hanya untuk harga barang (tidak termasuk ongkir).</div></div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="ps-panel">
                <div class="ps-panel-title"><svg width="20" height="20"><use href="#i-clock"/></svg> Riwayat Poin</div>

                <div class="ps-tabs">
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('poin.index', ['tab' => $key]) }}" class="ps-tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                @forelse ($history as $h)
                    @php [$ic, $kelas, $label] = $jenis[$h->type] ?? ['i-star', 'ps-hist-earn', $h->type]; @endphp
                    <div class="ps-hist">
                        <span class="ps-hist-icon {{ $kelas }}"><svg width="20" height="20"><use href="#{{ $ic }}"/></svg></span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $h->note ?: $label }}</div>
                            <div class="small text-muted">
                                {{ $label }} · {{ $h->created_at->translatedFormat('d M Y, H:i') }}
                                @if ($h->order) · <a href="{{ route('pesanan.show', $h->order) }}">Lihat pesanan</a> @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="ps-hist-points {{ $h->points < 0 ? 'neg' : 'pos' }}">{{ $h->points > 0 ? '+' : '' }}{{ $angka($h->points) }}</div>
                            <div class="small text-muted">Saldo {{ $angka($h->balance_after) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <div class="ps-empty-icon" style="width:72px;height:72px"><svg width="32" height="32"><use href="#i-star"/></svg></div>
                        Belum ada riwayat poin{{ $tab === 'semua' ? '. Poin muncul setelah pembayaran pesanan Anda diverifikasi.' : ' di tab ini.' }}
                    </div>
                @endforelse

                <div class="mt-3 d-flex justify-content-center">{{ $history->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection