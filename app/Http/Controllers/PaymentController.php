<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order, OrderService $orders)
    {
        abort_unless($order->customer_id === auth()->id(), 404);

        $data = $request->validate([
            'method' => 'required|in:transfer,qris',
            'proof'  => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        // Disimpan di disk privat, tidak bisa dibuka lewat alamat langsung
        $path = $request->file('proof')->store('bukti-bayar', 'local');

        try {
            $orders->submitPayment($order, $data['method'], $path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return redirect()->route('pesanan.show', $order)
            ->with('status', 'Bukti pembayaran terkirim. Mohon tunggu verifikasi dari kasir.');
    }
}