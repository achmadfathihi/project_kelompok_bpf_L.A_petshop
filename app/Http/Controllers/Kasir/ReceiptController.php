<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Setting;

class ReceiptController extends Controller
{
    public function show(Order $order)
    {
        abort_unless($order->channel === 'offline' && $order->status === 'selesai', 404);

        $order->load(['items', 'customer', 'cashier']);

        // Pembukaan pertama dianggap cetakan asli, berikutnya bertanda SALINAN
        $salinan = $order->receipt_prints > 0;
        $order->increment('receipt_prints');

        // Nama dan nomor member disamarkan, karena struk sering terbaca orang lain
        $memberLabel = null;
        if ($order->customer) {
            $bagian = preg_split('/\s+/', trim($order->customer->name));
            $memberLabel = $bagian[0]
                . (isset($bagian[1]) ? ' ' . mb_strtoupper(mb_substr($bagian[1], 0, 1)) . '.' : '')
                . ($order->customer->phone ? ' (****' . substr($order->customer->phone, -4) . ')' : '');
        }

        // Saldo poin saat transaksi itu, bukan saldo hari ini
        $saldo = $order->customer
            ? PointTransaction::where('order_id', $order->id)->latest('id')->value('balance_after')
            : null;

        $total   = $order->items_after_points;
        $kembali = ($order->payment_method === 'tunai' && $order->cash_received !== null)
            ? $order->cash_received - $total
            : null;

        $setting = Setting::pluck('value', 'key');

        return view('kasir.struk', [
            'order'       => $order,
            'salinan'     => $salinan,
            'memberLabel' => $memberLabel,
            'saldo'       => $saldo,
            'total'       => $total,
            'kembali'     => $kembali,
            'alamat'      => $setting->get('store_address') ?: 'Rumbai, Pekanbaru',
            'telp'        => $setting->get('store_phone'),
            'penutup'     => $setting->get('receipt_footer') ?: 'Terima kasih sudah berbelanja!',
        ]);
    }
}