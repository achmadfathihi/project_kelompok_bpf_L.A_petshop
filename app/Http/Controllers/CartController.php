<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        $lines = $cart->lines();

        return view('keranjang.index', [
            'lines' => $lines,
            'total' => $lines->sum('subtotal'),
            'semuaOk' => $lines->every(fn ($l) => $l['ok']),
        ]);
    }

    public function add(Request $request, Product $product, CartService $cart)
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate(['qty' => 'nullable|integer|min:1|max:100']);
        $want  = (int) ($data['qty'] ?? 1);
        $stock = $product->stock;

        if ($stock <= 0) {
            return back()->withErrors(['keranjang' => "{$product->name} sedang habis."]);
        }

        $current = $cart->raw()[$product->id] ?? 0;
        $new     = min($current + $want, $stock);

        $cart->set($product->id, $new);

        return back()->with('status', $new < $current + $want
            ? "Jumlah disesuaikan dengan stok yang tersedia ({$stock})."
            : 'Ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $product, CartService $cart)
    {
        $data = $request->validate(['qty' => 'required|integer|min:0|max:100']);

        $cart->set($product->id, min($data['qty'], $product->stock));

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function remove(Product $product, CartService $cart)
    {
        $cart->remove($product->id);

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }
}