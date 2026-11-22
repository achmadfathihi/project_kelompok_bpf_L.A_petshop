@extends('layouts.print')
@section('title', 'Struk ' . $order->invoice_no)

@push('css')
<style>
    @page { size: 80mm auto; margin: 0; }

    .struk-wrap { padding: 24px 12px 44px; display: flex; justify-content: center; }
    .struk { position: relative; width: 80mm; max-width: 100%; background: #fff; padding: 6mm 5mm 8mm;
             font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.4; color: #000;
             box-shadow: 0 8px 28px rgba(0, 0, 0, .18); }
    .struk::after { content: ''; position: absolute; left: 0; right: 0; bottom: -8px; height: 8px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpolygon points='0,0 12,0 6,8' fill='white'/%3E%3C/svg%3E") repeat-x; }

    .c { text-align: center; }
    .b { font-weight: 700; }
    .big { font-size: 15px; letter-spacing: .5px; }
    .dash { border-top: 1px dashed #000; margin: 6px 0; }
    .dbl { border-top: 3px double #000; margin: 6px 0; }
    .row { display: flex; justify-content: space-between; gap: 8px; }
    .row span:last-child { white-space: nowrap; text-align: right; }
    .name { word-break: break-word; }
    .ind { padding-left: 8px; }
    .tot { font-size: 14px; font-weight: 700; }

    @media print {
        body { background: #fff; }
        .struk-wrap { padding: 0; display: block; }
        .struk { box-shadow: none; width: 80mm; padding: 3mm 4mm 6mm; }
        .struk::after { display: none; }
    }
</style>
@endpush

@section('toolbar')
    <div class="toolbar no-print">
        <button type="button" class="primary" onclick="window.print()">Cetak Struk</button>
        <a href="{{ route('kasir.transaksi.create') }}">Transaksi Baru</a>
        <a href="{{ route('kasir.pesanan.show', $order) }}">Detail</a>
        @if (session('status')) <span class="info">{{ session('status') }}</span> @endif
    </div>
@endsection

@section('content')
@php
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
    $metode = ['tunai' => 'TUNAI', 'transfer' => 'TRANSFER', 'qris' => 'QRIS'][$order->payment_method] ?? strtoupper($order->payment_method);
@endphp

<div class="struk-wrap">
    <div class="struk">
        @if ($salinan) <div class="c b">*** SALINAN ***</div> @endif

        <div class="c b big">{{ strtoupper(config('app.name')) }}</div>
        <div class="c">{{ $alamat }}</div>
        @if ($telp) <div class="c">Telp {{ $telp }}</div> @endif

        <div class="dbl"></div>

        <div class="row"><span>No</span><span>{{ $order->invoice_no }}</span></div>
        <div class="row"><span>Tgl</span><span>{{ $order->paid_at?->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $order->cashier?->name }}</span></div>
        @if ($memberLabel) <div class="row"><span>Member</span><span>{{ $memberLabel }}</span></div> @endif

        <div class="dash"></div>

        @foreach ($order->items as $i)
            <div class="name">{{ $i->product_name }}</div>
            <div class="row ind"><span>{{ $i->qty }} x {{ $fmt($i->price) }}</span><span>{{ $fmt($i->subtotal) }}</span></div>
        @endforeach

        <div class="dash"></div>

        <div class="row"><span>{{ $order->items->count() }} item, {{ $order->items->sum('qty') }} barang</span><span></span></div>
        <div class="row"><span>Subtotal</span><span>{{ $fmt($order->items_total) }}</span></div>
        @if ($order->points_used > 0)
            <div class="row"><span>Potongan poin</span><span>-{{ $fmt($order->points_used) }}</span></div>
        @endif

        <div class="dash"></div>

        <div class="row tot"><span>TOTAL</span><span>{{ $fmt($total) }}</span></div>

        @if ($order->payment_method === 'tunai' && $order->cash_received !== null)
            <div class="row"><span>TUNAI</span><span>{{ $fmt($order->cash_received) }}</span></div>
            <div class="row b"><span>KEMBALI</span><span>{{ $fmt($kembali) }}</span></div>
        @else
            <div class="row"><span>{{ $metode }}</span><span>{{ $fmt($total) }}</span></div>
        @endif

        @if ($memberLabel)
            <div class="dash"></div>
            <div class="row"><span>Poin didapat</span><span>+{{ $fmt($order->points_earned) }}</span></div>
            @if ($saldo !== null) <div class="row"><span>Saldo poin</span><span>{{ $fmt($saldo) }}</span></div> @endif
        @endif

        <div class="dbl"></div>

        <div class="c">{!! nl2br(e($penutup)) !!}</div>
        <div class="c" style="margin-top:6px">Dicetak {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>
@endsection