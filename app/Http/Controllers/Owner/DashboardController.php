<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockBatch;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function index(ReportService $reports)
    {
        $awalBulan = now()->startOfMonth();
        $akhirHari = now()->endOfDay();

        $produk = Product::withStock()->where('is_active', true)->get();

        $berjalan = Order::where('channel', 'online')
            ->whereIn('status', ['menunggu_ongkir', 'menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim'])
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $batchAktif = StockBatch::where('qty_left', '>', 0)->whereNotNull('expired_at');

        return view('owner.dashboard', [
            'hari'      => $reports->summary(today()->startOfDay(), $akhirHari),
            'bulan'     => $reports->summary($awalBulan, $akhirHari),
            'berjalan'  => $berjalan,
            'habis'     => $produk->filter(fn ($p) => $p->stock_status === 'habis')->count(),
            'menipis'   => $produk->filter(fn ($p) => $p->stock_status === 'menipis')->count(),
            'lewat'     => (clone $batchAktif)->whereDate('expired_at', '<', today())->count(),
            'segera'    => (clone $batchAktif)->whereDate('expired_at', '>=', today())->whereDate('expired_at', '<=', today()->addDays(30))->count(),
            'top'       => $reports->topProducts($awalBulan, $akhirHari, 5),
            'terbaru'   => Order::with('customer')->whereNotNull('paid_at')->whereIn('status', ReportService::SOLD)
                ->latest('paid_at')->limit(6)->get(),
        ]);
    }
}