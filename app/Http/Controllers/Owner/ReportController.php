<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request, ReportService $reports)
    {
        $request->validate([
            'dari'   => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
        ]);

        $from = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfDay();

        $fmt = fn (Carbon $d) => $d->format('Y-m-d');

        return view('owner.laporan.penjualan', [
            'from'      => $from,
            'to'        => $to,
            's'         => $reports->summary($from, $to),
            'daily'     => $reports->daily($from, $to),
            'top'       => $reports->topProducts($from, $to, 10),
            'shortcuts' => [
                'Hari ini'   => ['dari' => $fmt(today()), 'sampai' => $fmt(today())],
                '7 hari'     => ['dari' => $fmt(today()->subDays(6)), 'sampai' => $fmt(today())],
                'Bulan ini'  => ['dari' => $fmt(now()->startOfMonth()), 'sampai' => $fmt(today())],
                'Bulan lalu' => ['dari' => $fmt(now()->subMonthNoOverflow()->startOfMonth()), 'sampai' => $fmt(now()->subMonthNoOverflow()->endOfMonth())],
            ],
        ]);
    }

    public function stock(Request $request)
    {
        $status = in_array($request->status, ['habis', 'menipis', 'aman'], true) ? $request->status : 'semua';

        $all = Product::withStock()->with('category')
            ->withSum(['batches as expired_qty' => fn ($q) => $q->where('qty_left', '>', 0)
                ->whereNotNull('expired_at')->whereDate('expired_at', '<', today())], 'qty_left')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $counts = [
            'habis'   => $all->filter(fn ($p) => $p->stock_status === 'habis')->count(),
            'menipis' => $all->filter(fn ($p) => $p->stock_status === 'menipis')->count(),
            'aman'    => $all->filter(fn ($p) => $p->stock_status === 'tersedia')->count(),
        ];

        $products = $status === 'semua'
            ? $all
            : $all->filter(fn ($p) => $p->stock_status === ($status === 'aman' ? 'tersedia' : $status))->values();

        return view('owner.laporan.stok', compact('products', 'counts', 'status'));
    }

    public function expiry(Request $request)
    {
        $rentang = in_array($request->rentang, ['lewat', '7', '30', '90', 'semua'], true) ? $request->rentang : '30';

        $batches = StockBatch::with('product.category')
            ->where('qty_left', '>', 0)
            ->whereNotNull('expired_at')
            ->when($rentang === 'lewat', fn ($q) => $q->whereDate('expired_at', '<', today()))
            ->when(in_array($rentang, ['7', '30', '90'], true), fn ($q) => $q->whereDate('expired_at', '<=', today()->addDays((int) $rentang)))
            ->orderBy('expired_at')
            ->get();

        $lewat = $batches->filter(fn ($b) => $b->days_left < 0);

        return view('owner.laporan.kedaluwarsa', [
            'batches'     => $batches,
            'rentang'     => $rentang,
            'jumlahLewat' => $lewat->count(),
            'unitLewat'   => (int) $lewat->sum('qty_left'),
            'nilaiLewat'  => (int) $lewat->sum(fn ($b) => $b->qty_left * $b->product->price),
        ]);
    }
}