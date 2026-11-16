<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(CartService $cart)
    {
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('keranjang.index')->withErrors(['keranjang' => 'Keranjang masih kosong.']);
        }

        if (! $lines->every(fn ($l) => $l['ok'])) {
            return redirect()->route('keranjang.index')
                ->withErrors(['keranjang' => 'Ada produk yang stoknya tidak cukup. Sesuaikan jumlahnya dulu.']);
        }

        $subtotal = $lines->sum('subtotal');
        $user     = auth()->user();

        return view('checkout.show', [
            'lines'     => $lines,
            'subtotal'  => $subtotal,
            'user'      => $user,
            'maxPoints' => min($user->points, $subtotal),
        ]);
    }

    public function store(Request $request, CartService $cart, OrderService $orders)
    {
        $data = $request->validate([
            'recipient_name'  => 'required|string|max:100',
            'recipient_phone' => ['required', 'regex:/^[0-9+]{8,20}$/'],
            'address'         => 'required|string|min:10|max:500',
        ]);

        $lines = $cart->lines();

        if ($lines->isEmpty() || ! $lines->every(fn ($l) => $l['ok'])) {
            return redirect()->route('keranjang.index')
                ->withErrors(['keranjang' => 'Isi keranjang berubah. Periksa kembali sebelum checkout.']);
        }

        $order = $orders->place(
            auth()->user(),
            $cart->raw(),
            $data,
            $request->boolean('use_points')
        );

        if ($request->boolean('simpan_alamat')) {
            auth()->user()->update(['address' => $data['address']]);
        }

        $cart->clear();

        return redirect()->route('pesanan.show', $order)
            ->with('status', 'Pesanan dibuat. Toko akan menghitung ongkir, lalu Anda bisa membayar.');
    }
}