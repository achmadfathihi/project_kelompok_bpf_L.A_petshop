@extends('layouts.app')
@section('title', 'Keranjang')

@section('content')
    <h3 class="mb-3">Keranjang Belanja</h3>

    @if ($lines->isEmpty())
        <div class="alert alert-info">
            Keranjang masih kosong. <a href="{{ route('beranda') }}">Lihat katalog</a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Produk</th><th>Harga</th><th style="width:190px">Jumlah</th><th>Subtotal</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $l)
                            @php $p = $l['product']; @endphp
                            <tr class="{{ $l['ok'] ? '' : 'table-warning' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $p->photo_url }}" alt="" class="rounded me-3" style="width:56px;height:56px;object-fit:cover">
                                        <div>
                                            {{ $p->name }}
                                            <div class="small text-muted">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</div>
                                            @unless ($l['ok'])
                                                <div class="small text-danger">
                                                    {{ $p->stock > 0 ? 'Stok tinggal ' . $p->stock . '. Kurangi jumlahnya.' : 'Stok habis. Hapus dari keranjang.' }}
                                                </div>
                                            @endunless
                                        </div>
                                    </div>
                                </td>
                                <td>@rp($p->price)</td>
                                <td>
                                    <form method="POST" action="{{ route('keranjang.update', $p) }}" class="input-group input-group-sm">
                                        @csrf @method('PATCH')
                                        <input type="number" name="qty" value="{{ $l['qty'] }}" min="0" max="{{ max($p->stock, 1) }}" class="form-control">
                                        <button class="btn btn-outline-secondary">Ubah</button>
                                    </form>
                                </td>
                                <td>@rp($l['subtotal'])</td>
                                <td>
                                    <form method="POST" action="{{ route('keranjang.remove', $p) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="fs-5">Total barang: <strong>@rp($total)</strong></div>
                @if ($semuaOk)
                    <a href="{{ route('checkout.show') }}" class="btn btn-primary">Lanjut ke Checkout</a>
                @else
                    <button class="btn btn-primary" disabled>Perbaiki jumlah dulu</button>
                @endif
            </div>
        </div>
        <p class="text-muted small mt-2">Ongkir dihitung toko setelah pesanan dibuat. Pengiriman memakai ojek online.</p>
    @endif
@endsection