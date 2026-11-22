@extends('layouts.print')
@section('title', 'Daftar Barang ' . $order->invoice_no)

@push('css')
<style>
    @page { size: A4; margin: 14mm; }
    .sheet { max-width: 190mm; margin: 1.5rem auto; background: #fff; padding: 14mm; box-shadow: 0 4px 20px rgba(0, 0, 0, .12); }
    h1 { font-size: 20px; margin: 0; }
    h2 { font-size: 15px; margin: 18px 0 4px; }
    .muted { color: #444; font-size: 12px; }
    .meta { display: flex; justify-content: space-between; gap: 24px; margin-top: 14px; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 13px; }
    th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
    th { background: #EEE; }
    .chk { width: 34px; text-align: center; }
    .box { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #000; }
    .sign { display: flex; justify-content: space-between; margin-top: 36px; font-size: 13px; text-align: center; }
    .sign div { width: 40%; }
    .sign .line { margin-top: 58px; border-top: 1px solid #000; padding-top: 4px; }
    @media print { .sheet { margin: 0; padding: 0; box-shadow: none; max-width: none; } th { background: #fff; } }
</style>
@endpush

@section('toolbar')
    <div class="toolbar no-print">
        <button type="button" class="primary" onclick="window.print()">Cetak</button>
        <a href="{{ route('karyawan.pesanan.show', $order) }}">Kembali ke pesanan</a>
        <span class="info">Saat mencetak, matikan "Header dan footer" di jendela cetak browser.</span>
    </div>
@endsection

@section('content')
<div class="sheet">
    <h1>{{ config('app.name') }}</h1>
    <div class="muted">Daftar Penyiapan Barang</div>

    <div class="meta">
        <div>
            <strong>No. pesanan:</strong> {{ $order->invoice_no }}<br>
            <strong>Dibayar:</strong> {{ $order->paid_at?->format('d/m/Y H:i') }}<br>
            <strong>Dicetak:</strong> {{ now()->format('d/m/Y H:i') }}
        </div>
        <div>
            <strong>Penerima:</strong> {{ $order->recipient_name }}<br>
            <strong>Telepon:</strong> {{ $order->recipient_phone }}<br>
            <strong>Alamat:</strong> {{ $order->address }}
        </div>
    </div>

    <table>
        <thead>
            <tr><th class="chk">&#10003;</th><th>Produk</th><th style="width:60px">Jumlah</th><th>Ambil dari batch</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $i)
                <tr>
                    <td class="chk"><span class="box"></span></td>
                    <td>{{ $i->product_name }}</td>
                    <td><strong>{{ $i->qty }}</strong></td>
                    <td>
                        @foreach ($i->batches as $b)
                            {{ $b->qty }} &times; {{ $b->batch->expired_at ? 'kedaluwarsa ' . $b->batch->expired_at->format('d/m/Y') : 'tanpa kedaluwarsa' }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Catatan pengiriman (ojek online)</h2>
    <div style="border-bottom: 1px solid #000; height: 22px"></div>
    <div style="border-bottom: 1px solid #000; height: 22px"></div>

    <div class="sign">
        <div><div class="line">Disiapkan oleh</div></div>
        <div><div class="line">Driver / penerima</div></div>
    </div>
</div>
@endsection