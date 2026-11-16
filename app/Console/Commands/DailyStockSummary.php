<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockBatch;
use App\Services\Notifier;
use Illuminate\Console\Command;

class DailyStockSummary extends Command
{
    protected $signature = 'stok:ringkasan';

    protected $description = 'Kirim ringkasan stok menipis dan batch hampir kedaluwarsa ke karyawan (maksimal sekali sehari)';

    public function handle(Notifier $notifier): int
    {
        $menipis = Product::withStock()
            ->where('is_active', true)
            ->havingRaw('COALESCE(stock, 0) <= min_stock')
            ->get()
            ->count();

        $segera = StockBatch::where('qty_left', '>', 0)
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '<=', today()->addDays(30))
            ->count();

        if ($menipis === 0 && $segera === 0) {
            return self::SUCCESS;
        }

        $bagian = [];
        if ($menipis > 0) {
            $bagian[] = "{$menipis} produk stok habis/menipis";
        }
        if ($segera > 0) {
            $bagian[] = "{$segera} batch kedaluwarsa dalam 30 hari";
        }

        $notifier->toRole(
            'karyawan',
            'Ringkasan stok hari ini',
            implode(', ', $bagian) . '.',
            route('karyawan.home', [], false),
            'ringkasan-stok-' . today()->toDateString()   // satu kali per hari
        );

        $this->info('Ringkasan terkirim: ' . implode(', ', $bagian));

        return self::SUCCESS;
    }
}