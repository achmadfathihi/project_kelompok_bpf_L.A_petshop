<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;

class CustomerOrderController extends Controller
{
    private function ownOrFail(Order $order): void
    {
        // 404, bukan 403, supaya orang lain tidak bisa menebak nomor pesanan
        abort_unless($order->customer_id === auth()->id(), 404);
    }

    public function index()
    {
        $orders = Order::where('customer_id', auth()->id())
            ->withCount('items')
            ->latest('id')
            ->paginate(10);

        return view('pesanan.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->ownOrFail($order);

        return view('pesanan.show', [
            'order'   => $order->load(['items', 'payments']),
            'setting' => \App\Models\Setting::pluck('value', 'key'),
        ]);
    }

    public function cancel(Order $order, OrderService $orders)
    {
        $this->ownOrFail($order);

        $orders->cancel($order, 'Dibatalkan oleh pelanggan', auth()->id(), Order::CUSTOMER_CANCELLABLE, false);

        return redirect()->route('pesanan.show', $order)->with('status', 'Pesanan dibatalkan. Stok dan poin sudah dikembalikan.');
    }

    public function received(Order $order, OrderService $orders)
    {
        $this->ownOrFail($order);

        $orders->confirmReceived($order);

        return redirect()->route('pesanan.show', $order)->with('status', 'Terima kasih! Pesanan ditandai selesai.');
    }
}