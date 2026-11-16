<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart';

    /** @return array<int,int> [product_id => qty] */
    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    public function set(int $productId, int $qty): void
    {
        $cart = $this->raw();

        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }

        session([self::KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $this->set($productId, 0);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Isi keranjang beserta data produk terbaru.
     * 'ok' bernilai false kalau stok sekarang tidak lagi cukup untuk jumlah di keranjang.
     */
    public function lines(): Collection
    {
        $cart = $this->raw();

        if (! $cart) {
            return collect();
        }

        $products = Product::withStock()->with('category')
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $lines = collect();

        foreach ($cart as $id => $qty) {
            $product = $products->get($id);

            if (! $product) {          // produk sudah dihapus atau dinonaktifkan
                $this->remove($id);
                continue;
            }

            $lines->push([
                'product'  => $product,
                'qty'      => $qty,
                'subtotal' => $product->price * $qty,
                'ok'       => $qty > 0 && $product->stock >= $qty,
            ]);
        }

        return $lines;
    }
}