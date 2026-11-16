@extends('layouts.admin')
@section('title', 'Transaksi di Toko')

@section('content')
    @include('layouts.admin.header', ['title' => 'Transaksi di Toko', 'subtitle' => 'Untuk pembeli yang datang langsung. Barang keluar otomatis dari batch yang paling dekat kedaluwarsa.'])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <form method="POST" action="{{ route('kasir.transaksi.store') }}" id="form-pos">
            @csrf
            <div id="hidden-items"></div>
            <input type="hidden" name="member_id" id="member_id" value="">

            <div class="row">
                <div class="col-xl-7 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <input type="text" id="cari" class="form-control" placeholder="Cari produk (nama atau merek)" autocomplete="off">
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light"><tr><th>Produk</th><th>Harga</th><th>Stok</th><th></th></tr></thead>
                                <tbody id="daftar-produk"></tbody>
                            </table>
                        </div>
                        <div class="card-footer small text-muted" id="info-hasil"></div>
                    </div>
                </div>

                <div class="col-xl-5 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header"><h3 class="mb-0">Keranjang</h3></div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light"><tr><th>Produk</th><th style="width:90px">Jml</th><th>Subtotal</th><th></th></tr></thead>
                                <tbody id="isi-keranjang"></tbody>
                            </table>
                        </div>
                        <div class="card-footer text-muted small" id="kosong">Belum ada produk dipilih.</div>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <label class="form-control-label">Member (opsional)</label>
                            <div class="input-group">
                                <input type="text" id="member_q" class="form-control" placeholder="Nomor HP atau email member" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btn-cari-member">Cari</button>
                                </div>
                            </div>
                            <div class="small mt-1" id="member_info"></div>

                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" class="custom-control-input" id="use_points" name="use_points" value="1" disabled>
                                <label class="custom-control-label" for="use_points">Pakai poin member</label>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-control-label">Metode bayar</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="tunai">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span id="s-subtotal">Rp0</span></div>
                            <div class="d-flex justify-content-between text-success"><span>Potongan poin</span><span id="s-poin">Rp0</span></div>
                            <div class="d-flex justify-content-between font-weight-bold h4 mt-2"><span>Total bayar</span><span id="s-bayar">Rp0</span></div>
                            <div class="small text-muted" id="s-dapat"></div>

                            <button class="btn btn-primary btn-block mt-3" id="btn-simpan">Simpan Transaksi</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
<script>
(function () {
    var products = @json($products);
    var STEP = {{ \App\Models\Order::POINT_STEP }}, REWARD = {{ \App\Models\Order::POINT_REWARD }};
    var urlMember = @json(route('kasir.member'));

    var byId = {};
    products.forEach(function (p) { byId[p.id] = p; });

    var cart = {};
    var member = null;

    var daftar = document.getElementById('daftar-produk');
    var isi = document.getElementById('isi-keranjang');
    var cari = document.getElementById('cari');
    var usePoints = document.getElementById('use_points');

    function rupiah(n) { return 'Rp' + n.toLocaleString('id-ID'); }
    function el(tag, text, cls) {
        var e = document.createElement(tag);
        if (text !== undefined) e.textContent = text;
        if (cls) e.className = cls;
        return e;
    }

    function renderProduk() {
        var q = cari.value.toLowerCase();
        var hasil = products.filter(function (p) {
            return (p.name + ' ' + (p.brand || '')).toLowerCase().indexOf(q) !== -1;
        });
        daftar.innerHTML = '';
        hasil.slice(0, 40).forEach(function (p) {
            var tr = el('tr');
            var td1 = el('td');
            td1.appendChild(el('div', p.name));
            td1.appendChild(el('small', (p.brand || '') + (p.size ? ' · ' + p.size : ''), 'text-muted'));
            var td4 = el('td', undefined, 'text-right');
            var b = el('button', 'Tambah', 'btn btn-sm btn-primary');
            b.type = 'button';
            b.addEventListener('click', function () { tambah(p.id); });
            td4.appendChild(b);
            tr.appendChild(td1);
            tr.appendChild(el('td', rupiah(p.price)));
            tr.appendChild(el('td', String(p.stock)));
            tr.appendChild(td4);
            daftar.appendChild(tr);
        });
        document.getElementById('info-hasil').textContent =
            hasil.length === 0 ? 'Produk tidak ditemukan.' :
            (hasil.length > 40 ? 'Menampilkan 40 dari ' + hasil.length + ' produk. Persempit pencarian.' : '');
    }

    function tambah(id) {
        cart[id] = Math.min((cart[id] || 0) + 1, byId[id].stock);
        renderKeranjang();
    }

    function renderKeranjang() {
        isi.innerHTML = '';
        var ids = Object.keys(cart);
        document.getElementById('kosong').style.display = ids.length ? 'none' : '';

        ids.forEach(function (id) {
            var p = byId[id];
            var tr = el('tr');
            tr.appendChild(el('td', p.name));

            var td2 = el('td');
            var inp = el('input', undefined, 'form-control form-control-sm');
            inp.type = 'number'; inp.min = 0; inp.max = p.stock; inp.value = cart[id];
            inp.addEventListener('change', function () {
                var n = parseInt(inp.value) || 0;
                if (n <= 0) { delete cart[id]; } else { cart[id] = Math.min(n, p.stock); }
                renderKeranjang();
            });
            td2.appendChild(inp);
            tr.appendChild(td2);

            tr.appendChild(el('td', rupiah(p.price * cart[id])));

            var td4 = el('td', undefined, 'text-right');
            var x = el('button', '×', 'btn btn-sm btn-outline-danger');
            x.type = 'button';
            x.addEventListener('click', function () { delete cart[id]; renderKeranjang(); });
            td4.appendChild(x);
            tr.appendChild(td4);

            isi.appendChild(tr);
        });
        hitung();
    }

    function hitung() {
        var subtotal = 0;
        Object.keys(cart).forEach(function (id) { subtotal += byId[id].price * cart[id]; });

        var pakai = (member && usePoints.checked) ? Math.min(member.points, subtotal) : 0;
        var setelah = subtotal - pakai;
        var dapat = member ? Math.floor(setelah / STEP) * REWARD : 0;

        document.getElementById('s-subtotal').textContent = rupiah(subtotal);
        document.getElementById('s-poin').textContent = pakai > 0 ? '−' + rupiah(pakai) : 'Rp0';
        document.getElementById('s-bayar').textContent = rupiah(setelah);
        document.getElementById('s-dapat').textContent = member
            ? 'Member akan mendapat ' + dapat.toLocaleString('id-ID') + ' poin.' : '';
    }

    function hapusMember() {
        member = null;
        document.getElementById('member_id').value = '';
        usePoints.checked = false;
        usePoints.disabled = true;
    }

    function cariMember() {
        var q = document.getElementById('member_q').value.trim();
        var info = document.getElementById('member_info');
        if (!q) { return; }

        fetch(urlMember + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.found) {
                    member = d;
                    document.getElementById('member_id').value = d.id;
                    usePoints.disabled = d.points <= 0;
                    info.className = 'small mt-1 text-success';
                    info.textContent = d.name + ' · poin ' + d.points.toLocaleString('id-ID');
                } else {
                    hapusMember();
                    info.className = 'small mt-1 text-danger';
                    info.textContent = 'Member tidak ditemukan. Isi nomor HP atau email yang persis sama.';
                }
                hitung();
            })
            .catch(function () {
                info.className = 'small mt-1 text-danger';
                info.textContent = 'Gagal mencari member. Coba lagi.';
            });
    }

    document.getElementById('btn-cari-member').addEventListener('click', cariMember);
    document.getElementById('member_q').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); cariMember(); }
    });
    document.getElementById('member_q').addEventListener('input', function () {
        if (member) { hapusMember(); document.getElementById('member_info').textContent = ''; hitung(); }
    });
    usePoints.addEventListener('change', hitung);
    cari.addEventListener('input', renderProduk);
    cari.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });

    document.getElementById('form-pos').addEventListener('submit', function (e) {
        var ids = Object.keys(cart);
        if (ids.length === 0) { e.preventDefault(); alert('Pilih minimal satu produk.'); return; }

        var box = document.getElementById('hidden-items');
        box.innerHTML = '';
        ids.forEach(function (id) {
            var i = document.createElement('input');
            i.type = 'hidden'; i.name = 'items[' + id + ']'; i.value = cart[id];
            box.appendChild(i);
        });

        // Cegah klik ganda yang bisa mencatat dua penjualan
        document.getElementById('btn-simpan').disabled = true;
    });

    renderProduk();
    renderKeranjang();
})();
</script>
@endpush