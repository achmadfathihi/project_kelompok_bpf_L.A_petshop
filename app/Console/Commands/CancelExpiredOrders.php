<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Batalkan pesanan online yang ongkirnya tidak diisi atau pembayarannya melewati batas waktu';

    public function handle(OrderService $orders): int
    {
        $jumlah = $orders->cancelExpired();

        $this->info("Pesanan dibatalkan: {$jumlah}");

        return self::SUCCESS;
    }
}