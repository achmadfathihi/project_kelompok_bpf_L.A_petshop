<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Karyawan\CategoryController;
use App\Http\Controllers\Karyawan\DashboardController;
use App\Http\Controllers\Karyawan\ProductController;
use App\Http\Controllers\Karyawan\StockMovementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Karyawan\BatchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\OrderController as KasirOrderController;
use App\Http\Controllers\Kasir\PosController;
use App\Http\Controllers\Owner\SettingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\Karyawan\OrderController as KaryawanOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MemberController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Owner\StaffController;

// Katalog publik (bisa dilihat tanpa login)
Route::get('/', [CatalogController::class, 'index'])->name('beranda');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifikasi.baca-semua');
    Route::get('notifikasi/{notification}', [NotificationController::class, 'open'])->name('notifikasi.buka');

    // ===== Pelanggan =====
    Route::middleware('role:pelanggan')->group(function () {
        Route::get('keranjang', [CartController::class, 'index'])->name('keranjang.index');
        Route::post('keranjang/{product}', [CartController::class, 'add'])->name('keranjang.add');
        Route::patch('keranjang/{product}', [CartController::class, 'update'])->name('keranjang.update');
        Route::delete('keranjang/{product}', [CartController::class, 'remove'])->name('keranjang.remove');

        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('pesanan', [CustomerOrderController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/{order}', [CustomerOrderController::class, 'show'])->name('pesanan.show');
        Route::post('pesanan/{order}/batal', [CustomerOrderController::class, 'cancel'])->name('pesanan.batal');
        Route::post('pesanan/{order}/bayar', [PaymentController::class, 'store'])->name('pesanan.bayar');
        Route::post('pesanan/{order}/diterima', [CustomerOrderController::class, 'received'])->name('pesanan.diterima');

        Route::get('poin', [PointController::class, 'index'])->name('poin.index');

        Route::get('profil', [ProfileController::class, 'edit'])->name('profil.edit');
        Route::put('profil', [ProfileController::class, 'update'])->name('profil.update');
    });

    // ===== Karyawan (owner juga boleh) =====
    Route::middleware('role:karyawan,owner')->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('home');

        Route::resource('kategori', CategoryController::class)
            ->parameters(['kategori' => 'category'])->except('show');

        Route::resource('produk', ProductController::class)
            ->parameters(['produk' => 'product'])->except('show');

        Route::post('produk/{product}/status', [ProductController::class, 'toggle'])->name('produk.status');

        // Catatan stok: hanya tambah dan lihat (tidak bisa diubah/dihapus)
        Route::resource('stok', StockMovementController::class)->only(['index', 'create', 'store']);
        Route::get('batch', [BatchController::class, 'index'])->name('batch.index');
        Route::post('batch/{batch}/keluarkan', [BatchController::class, 'writeOff'])->name('batch.keluarkan');
        Route::get('pesanan', [KaryawanOrderController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/{order}', [KaryawanOrderController::class, 'show'])->name('pesanan.show');
        Route::post('pesanan/{order}/kirim', [KaryawanOrderController::class, 'ship'])->name('pesanan.kirim');
    });

    // ===== Kasir (owner juga boleh) =====
    Route::middleware('role:kasir,owner')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [KasirDashboardController::class, 'index'])->name('home');

        Route::get('pesanan', [KasirOrderController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/{order}', [KasirOrderController::class, 'show'])->name('pesanan.show');
        Route::post('pesanan/{order}/ongkir', [KasirOrderController::class, 'shipping'])->name('pesanan.ongkir');
        Route::post('pesanan/{order}/verifikasi', [KasirOrderController::class, 'verify'])->name('pesanan.verifikasi');
        Route::post('pesanan/{order}/tolak', [KasirOrderController::class, 'reject'])->name('pesanan.tolak');
        Route::post('pesanan/{order}/batal', [KasirOrderController::class, 'cancel'])->name('pesanan.batal');

        Route::get('transaksi/baru', [PosController::class, 'create'])->name('transaksi.create');
        Route::post('transaksi', [PosController::class, 'store'])->name('transaksi.store');
        Route::get('member', [PosController::class, 'member'])->middleware('throttle:60,1')->name('member');
    });

    // ===== Owner =====
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', [OwnerDashboardController::class, 'index'])->name('home');

        Route::get('laporan/penjualan', [ReportController::class, 'sales'])->name('laporan.penjualan');
        Route::get('laporan/stok', [ReportController::class, 'stock'])->name('laporan.stok');
        Route::get('laporan/kedaluwarsa', [ReportController::class, 'expiry'])->name('laporan.kedaluwarsa');

        Route::resource('staf', StaffController::class)->parameters(['staf' => 'user'])->except(['show', 'destroy']);
        Route::post('staf/{user}/status', [StaffController::class, 'toggle'])->name('staf.status');
        Route::post('staf/{user}/password', [StaffController::class, 'resetPassword'])->name('staf.password');

        Route::get('member', [MemberController::class, 'index'])->name('member.index');

        Route::get('pengaturan', [SettingController::class, 'edit'])->name('pengaturan.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
    });

    // Ganti password sendiri (semua staf)
    Route::middleware('role:karyawan,kasir,owner')->group(function () {
        Route::get('akun/password', [AccountController::class, 'edit'])->name('akun.password');
        Route::put('akun/password', [AccountController::class, 'update'])->name('akun.password.update');
    });

    // Bukti pembayaran (privat): hanya kasir, owner, dan pelanggan pemilik pesanan
    Route::get('bukti/{payment}', [PaymentProofController::class, 'show'])->name('bukti.show');
});