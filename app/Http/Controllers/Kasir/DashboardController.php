<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $omzetHariIni = Order::whereDate('paid_at', today())
            ->where('status', '!=', 'dibatalkan')
            ->get()
            ->sum(fn ($o) => $o->items_after_points + ($o->shipping_fee ?? 0));

        return view('kasir.dashboard', [
            'perluOngkir'     => Order::where('status', 'menunggu_ongkir')->count(),
            'perluVerifikasi' => Order::where('status', 'menunggu_verifikasi')->count(),
            'transaksiHariIni' => Order::whereDate('paid_at', today())->where('status', '!=', 'dibatalkan')->count(),
            'omzetHariIni'    => $omzetHariIni,
            'antrian'         => Order::with('customer')
                ->whereIn('status', ['menunggu_ongkir', 'menunggu_verifikasi'])
                ->orderBy('created_at')->limit(8)->get(),
        ]);
    }
}