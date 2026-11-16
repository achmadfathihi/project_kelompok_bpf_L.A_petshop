<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Status yang menjadi urusan karyawan (sudah dibayar)
    private const PAID = ['diproses', 'dikirim', 'selesai'];

    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['siapkan', 'dikirim', 'selesai', 'semua'], true) ? $request->tab : 'siapkan';

        $orders = Order::with('customer')->withCount('items')
            ->where('channel', 'online')
            ->when($tab === 'siapkan', fn ($q) => $q->where('status', 'diproses')->orderBy('paid_at'))
            ->when($tab === 'dikirim', fn ($q) => $q->where('status', 'dikirim')->latest('shipped_at'))
            ->when($tab === 'selesai', fn ($q) => $q->where('status', 'selesai')->latest('completed_at'))
            ->when($tab === 'semua', fn ($q) => $q->whereIn('status', self::PAID)->latest('id'))
            ->paginate(15)
            ->withQueryString();

        return view('karyawan.pesanan.index', [
            'orders'         => $orders,
            'tab'            => $tab,
            'jumlahSiapkan'  => Order::where('channel', 'online')->where('status', 'diproses')->count(),
            'jumlahDikirim'  => Order::where('channel', 'online')->where('status', 'dikirim')->count(),
        ]);
    }

    public function show(Order $order)
    {
        abort_unless($order->channel === 'online' && in_array($order->status, self::PAID, true), 404);

        $order->load(['items.batches.batch', 'customer']);

        return view('karyawan.pesanan.show', compact('order'));
    }

    public function ship(Request $request, Order $order)
    {
        $data = $request->validate(['note' => 'required|string|max:200']);

        $this->orders->markShipped($order, $data['note']);

        return back()->with('status', 'Pesanan ditandai dikirim. Pelanggan sudah diberi tahu.');
    }

    public function complete(Order $order)
    {
        $this->orders->markCompleted($order);

        return back()->with('status', 'Pesanan ditandai selesai.');
    }
}