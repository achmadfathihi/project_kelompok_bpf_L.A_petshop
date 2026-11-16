@extends('layouts.admin')
@section('title', 'Produk')

@section('content')
    @include('layouts.admin.header', [
        'title'    => 'Produk',
        'subtitle' => $tab === 'aktif'
            ? 'Produk yang dijual dan tampil di katalog.'
            : 'Produk yang tidak dijual. Tidak tampil di katalog, tetapi riwayat dan stoknya tetap tercatat.',
    ])

    <div class="container-fluid mt--7">
        @include('layouts.admin.alerts')

        <div class="card shadow">
            <div class="card-header border-0">
                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'aktif' ? 'active' : '' }}" href="{{ route('karyawan.produk.index') }}">
                            Aktif
                            <span class="badge badge-pill ml-1 {{ $tab === 'aktif' ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlahAktif }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'nonaktif' ? 'active' : '' }}" href="{{ route('karyawan.produk.index', ['tab' => 'nonaktif']) }}">
                            Nonaktif
                            <span class="badge badge-pill ml-1 {{ $tab === 'nonaktif' ? 'bg-white text-primary' : 'badge-primary' }}">{{ $jumlahNonaktif }}</span>
                        </a>
                    </li>
                </ul>

                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <form method="GET" class="form-inline">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2 mb-2 mb-lg-0" placeholder="Cari nama / merek">
                            <select name="kategori" class="form-control form-control-sm mr-2 mb-2 mb-lg-0">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(request('kategori') == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-primary mb-2 mb-lg-0">Cari</button>
                        </form>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-2 mt-lg-0">
                        <a href="{{ route('karyawan.produk.create') }}" class="btn btn-sm btn-primary">Tambah Produk</a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr><th>Foto</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $p)
                            <tr>
                                <td><img src="{{ $p->photo_url }}" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover"></td>
                                <td>
                                    {{ $p->name }}
                                    <small class="text-muted d-block">{{ $p->brand }}{{ $p->size ? ' · ' . $p->size : '' }}</small>
                                </td>
                                <td>{{ $p->category->name }}</td>
                                <td>@rp($p->price)</td>
                                <td>
                                    {{ $p->stock }}
                                    @if ($tab === 'aktif')
                                        @if ($p->stock_status === 'habis') <span class="badge badge-danger">Habis</span>
                                        @elseif ($p->stock_status === 'menipis') <span class="badge badge-warning">Menipis</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    <a href="{{ route('karyawan.produk.edit', $p) }}" class="btn btn-sm btn-outline-primary">Ubah</a>

                                    @if ($tab === 'aktif')
                                        <form method="POST" action="{{ route('karyawan.produk.status', $p) }}" class="d-inline"
                                              onsubmit="return confirm('Nonaktifkan produk ini? Produk hilang dari katalog dan keranjang pelanggan, tetapi riwayat dan stoknya tetap tercatat.')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning">Nonaktifkan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('karyawan.produk.status', $p) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">Aktifkan</button>
                                        </form>
                                    @endif

                                    @if ($p->stock_movements_count == 0)
                                        <form method="POST" action="{{ route('karyawan.produk.destroy', $p) }}" class="d-inline"
                                              onsubmit="return confirm('Hapus permanen produk ini? Produk belum punya riwayat, jadi aman dihapus.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    {{ $tab === 'aktif' ? 'Belum ada produk aktif.' : 'Tidak ada produk nonaktif.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer py-4">
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection