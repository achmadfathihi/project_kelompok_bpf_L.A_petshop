@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
    <h3 class="mb-3">Checkout</h3>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Alamat Pengiriman</h5>

                        <div class="mb-3">
                            <label class="form-label">Nama penerima</label>
                            <input type="text" name="recipient_name" class="form-control"
                                   value="{{ old('recipient_name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP penerima</label>
                            <input type="text" name="recipient_phone" class="form-control"
                                   value="{{ old('recipient_phone', $user->phone) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat lengkap</label>
                            <textarea name="address" rows="3" class="form-control" required
                                      placeholder="Jalan, nomor rumah, RT/RW, patokan">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="simpan_alamat" name="simpan_alamat" value="1"
                                   @checked(old('simpan_alamat', ! $user->address))>
                            <label class="form-check-label" for="simpan_alamat">Simpan sebagai alamat utama di akun saya</label>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-1">Poin</h5>
                        <p class="text-muted small mb-3">
                            Saldo poin Anda: <strong>{{ number_format($user->points, 0, ',', '.') }}</strong>
                            (1 poin = potongan Rp1). Poin hanya memotong harga barang, bukan ongkir.
                        </p>

                        @if ($maxPoints > 0)
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" id="use_points"
                                       name="use_points" value="1" @checked(old('use_points'))>
                                <label class="form-check-label" for="use_points">Pakai poin saya</label>
                            </div>
                            <div class="small text-muted mt-2">
                                Poin yang akan terpakai: <strong>{{ number_format($maxPoints, 0, ',', '.') }}</strong>
                                @if ($maxPoints < $user->points)
                                    <br>Sisa {{ number_format($user->points - $maxPoints, 0, ',', '.') }} poin tetap tersimpan,
                                    karena potongan tidak boleh melebihi harga barang.
                                @endif
                            </div>
                        @else
                            <div class="text-muted">Anda belum punya poin untuk dipakai.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Ringkasan Pesanan</h5>

                        @foreach ($lines as $l)
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $l['product']->name }} × {{ $l['qty'] }}</span>
                                <span>@rp($l['subtotal'])</span>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between"><span>Total barang</span><span>@rp($subtotal)</span></div>
                        <div class="d-flex justify-content-between text-success">
                            <span>Potongan poin</span><span id="potongan">Rp0</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-2">
                            <span>Barang setelah poin</span><span id="setelah">@rp($subtotal)</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-1">
                            <span>Ongkir</span><span>Dihitung toko</span>
                        </div>
                        <button class="btn btn-primary w-100 mt-3">Buat Pesanan</button>
                        <p class="text-muted small mt-2 mb-0">
                            Stok langsung disisihkan untuk Anda. Pembayaran dilakukan setelah toko mengisi ongkir.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function () {
            var subtotal = {{ (int) $subtotal }};
            var maks = {{ (int) $maxPoints }};
            var saklar = document.getElementById('use_points');

            function rupiah(n) { return 'Rp' + n.toLocaleString('id-ID'); }

            function hitung() {
                var p = (saklar && saklar.checked) ? maks : 0;
                document.getElementById('potongan').textContent = p > 0 ? '−' + rupiah(p) : 'Rp0';
                document.getElementById('setelah').textContent = rupiah(subtotal - p);
            }

            if (saklar) saklar.addEventListener('change', hitung);
            hitung();
        })();
    </script>
@endsection