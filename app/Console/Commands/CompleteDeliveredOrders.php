<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CompleteDeliveredOrders extends Command
{
    protected $signature = 'orders:complete-delivered';

    protected $description = 'Ingatkan pelanggan untuk konfirmasi penerimaan, dan tandai selesai otomatis pesanan yang lama berstatus dikirim';

    public function handle(OrderService $orders): int
    {
        $orders->remindReceipt();

        $jumlah = $orders->completeDelivered();

        $this->info("Pesanan selesai otomatis: {$jumlah}");

        return self::SUCCESS;
    }
}