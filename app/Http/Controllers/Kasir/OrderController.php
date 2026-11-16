<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['tindakan', 'online', 'toko', 'semua'], true) ? $request->tab : 'tindakan';

        $orders = Order::with('customer')
            ->when($tab === 'tindakan', fn ($q) => $q->whereIn('status', ['menunggu_ongkir', 'menunggu_verifikasi']))
            ->when($tab === 'online', fn ($q) => $q->where('channel', 'online'))
            ->when($tab === 'toko', fn ($q) => $q->where('channel', 'offline'))
            ->when($request->filled('q'), fn ($q) => $q->where('invoice_no', 'like', '%' . $request->q . '%'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('kasir.pesanan.index', [
            'orders'          => $orders,
            'tab'             => $tab,
            'perluOngkir'     => Order::where('status', 'menunggu_ongkir')->count(),
            'perluVerifikasi' => Order::where('status', 'menunggu_verifikasi')->count(),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'customer', 'cashier', 'payments.verifier']);

        return view('kasir.pesanan.show', compact('order'));
    }

    public function shipping(Request $request, Order $order)
    {
        $data = $request->validate(['shipping_fee' => 'required|integer|min:0|max:1000000']);

        $this->orders->setShipping($order, (int) $data['shipping_fee']);

        return back()->with('status', 'Ongkir disimpan. Pelanggan sekarang bisa membayar.');
    }

    public function verify(Order $order)
    {
        $this->orders->verifyPayment($order, auth()->id());

        return back()->with('status', 'Pembayaran valid. Pesanan masuk ke tahap diproses dan poin pelanggan sudah ditambahkan.');
    }

    public function reject(Request $request, Order $order)
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);

        $dibatalkan = $this->orders->rejectPayment($order, $data['reason'], auth()->id());

        return back()->with('status', $dibatalkan
            ? 'Bukti ditolak untuk ke-' . Order::MAX_REJECTIONS . ' kalinya, jadi pesanan dibatalkan otomatis. Stok dan poin sudah dikembalikan.'
            : 'Bukti ditolak. Pelanggan bisa mengunggah ulang.');
    }

    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);

        $this->orders->cancel($order, 'Dibatalkan oleh kasir: ' . $data['reason'], auth()->id(), Order::CASHIER_CANCELLABLE);

        return back()->with('status', 'Pesanan dibatalkan. Stok dan poin sudah dikembalikan.');
    }
}