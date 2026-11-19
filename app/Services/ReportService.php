<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // Status pesanan yang dihitung sebagai penjualan (pembayaran sudah valid)
    public const SOLD = ['diproses', 'dikirim', 'selesai'];

    /** Pesanan yang dihitung sebagai penjualan pada rentang tanggal pembayaran. */
    public function sold(Carbon $from, Carbon $to)
    {
        return Order::query()
            ->whereNotNull('paid_at')
            ->whereIn('status', self::SOLD)
            ->whereBetween('paid_at', [$from, $to]);
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $row = $this->sold($from, $to)->selectRaw(
            "COUNT(*) as jumlah,
             COALESCE(SUM(items_total), 0) as barang,
             COALESCE(SUM(points_used), 0) as potongan,
             COALESCE(SUM(CASE WHEN channel = 'online' THEN shipping_fee ELSE 0 END), 0) as ongkir,
             COALESCE(SUM(points_earned), 0) as poin_diberi"
        )->first();

        $channel = $this->sold($from, $to)
            ->selectRaw('channel, COUNT(*) as jumlah, SUM(items_total - points_used) as penjualan')
            ->groupBy('channel')->get()->keyBy('channel');

        $metode = $this->sold($from, $to)
            ->selectRaw('payment_method, COUNT(*) as jumlah, SUM(items_total - points_used) as penjualan')
            ->groupBy('payment_method')->get()->keyBy('payment_method');

        return [
            'jumlah'      => (int) $row->jumlah,
            'barang'      => (int) $row->barang,
            'potongan'    => (int) $row->potongan,
            'penjualan'   => (int) $row->barang - (int) $row->potongan,   // barang setelah potongan poin
            'ongkir'      => (int) $row->ongkir,                          // dicatat terpisah, bukan penjualan
            'poin_diberi' => (int) $row->poin_diberi,
            'channel'     => $channel,
            'metode'      => $metode,
        ];
    }

    public function daily(Carbon $from, Carbon $to)
    {
        return $this->sold($from, $to)
            ->selectRaw('DATE(paid_at) as tgl, COUNT(*) as jumlah, SUM(items_total - points_used) as penjualan')
            ->groupBy('tgl')->orderBy('tgl')->get();
    }

    public function topProducts(Carbon $from, Carbon $to, int $limit = 10)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotNull('orders.paid_at')
            ->whereIn('orders.status', self::SOLD)
            ->whereBetween('orders.paid_at', [$from, $to])
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, MAX(order_items.product_name) as name, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as total')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }
}