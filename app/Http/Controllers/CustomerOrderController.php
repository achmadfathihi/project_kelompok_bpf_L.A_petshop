<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    private function ownOrFail(Order $order): void
    {
        // 404, bukan 403, supaya orang lain tidak bisa menebak nomor pesanan
        abort_unless($order->customer_id === auth()->id(), 404);
    }

    public function index(Request $request)
    {
        // Kelompok status untuk tab
        $groups = [
            'bayar'   => ['menunggu_ongkir', 'menunggu_pembayaran', 'menunggu_verifikasi'],
            'proses'  => ['diproses'],
            'kirim'   => ['dikirim'],
            'selesai' => ['selesai'],
            'batal'   => ['dibatalkan'],
        ];

        $tab = in_array($request->tab, array_keys($groups), true) ? $request->tab : 'semua';

        $perStatus = Order::where('customer_id', auth()->id())
            ->selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        $counts = ['semua' => (int) $perStatus->sum()];
        foreach ($groups as $key => $statuses) {
            $counts[$key] = (int) collect($statuses)->sum(fn ($s) => $perStatus->get($s, 0));
        }

        $orders = Order::where('customer_id', auth()->id())
            ->with('items.product')->withCount('items')
            ->when($tab !== 'semua', fn ($q) => $q->whereIn('status', $groups[$tab]))
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('pesanan.index', compact('orders', 'tab', 'counts'));
    }

    public function show(Order $order)
    {
        $this->ownOrFail($order);

        return view('pesanan.show', [
            'order'   => $order->load(['items.product', 'payments']),
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