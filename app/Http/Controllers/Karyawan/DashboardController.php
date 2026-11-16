<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        // Produk aktif yang stok layak jualnya habis atau di bawah batas
        $lowStock = Product::withStock()->with('category')
            ->where('is_active', true)
            ->havingRaw('COALESCE(stock, 0) <= min_stock')
            ->orderByRaw('COALESCE(stock, 0)')
            ->get();

        // Batch yang masih ada sisa dan kedaluwarsanya ≤ 30 hari lagi (atau sudah lewat)
        $expiring = StockBatch::with('product')
            ->where('qty_left', '>', 0)
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '<=', today()->addDays(30))
            ->orderBy('expired_at')
            ->get();

        return view('karyawan.dashboard', [
            'totalProduk' => Product::where('is_active', true)->count(),
            'habis'       => $lowStock->where('stock', 0)->count(),
            'menipis'     => $lowStock->where('stock', '>', 0)->count(),
            'lowStock'    => $lowStock,
            'expiring'    => $expiring,
            'perluDisiapkan' => Order::where('channel', 'online')->where('status', 'diproses')->count(),
        ]);
    }
}