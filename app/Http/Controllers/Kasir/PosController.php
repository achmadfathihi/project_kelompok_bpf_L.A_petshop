<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function create()
    {
        $products = Product::withStock()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($p) => $p->stock > 0)
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'brand' => $p->brand,
                'size' => $p->size, 'price' => $p->price, 'stock' => $p->stock,
            ])
            ->values();

        return view('kasir.transaksi.create', compact('products'));
    }

    // Mencari member dengan nomor HP atau email yang persis sama
    public function member(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $user = $q === '' ? null : User::where('role', 'pelanggan')
            ->where(fn ($w) => $w->where('phone', $q)->orWhere('email', $q))
            ->first();

        return response()->json($user
            ? ['found' => true, 'id' => $user->id, 'name' => $user->name, 'points' => (int) $user->points]
            : ['found' => false]);
    }

    public function store(Request $request, OrderService $orders)
    {
        $data = $request->validate([
            'items'          => 'required|array|min:1',
            'items.*'        => 'required|integer|min:1|max:1000',
            'member_id'      => 'nullable|integer',
            'payment_method' => 'required|in:tunai,transfer,qris',
            'cash_received'  => 'nullable|integer|min:0|max:100000000',
        ]);

        $cart = collect($data['items'])
            ->mapWithKeys(fn ($qty, $id) => [(int) $id => (int) $qty])
            ->all();

        $member = null;
        if (! empty($data['member_id'])) {
            $member = User::where('role', 'pelanggan')->find($data['member_id']);

            if (! $member) {
                throw ValidationException::withMessages(['member_id' => 'Member tidak ditemukan.']);
            }
        }

        $order = $orders->placeOffline(
            auth()->user(),
            $cart,
            $member,
            $request->boolean('use_points'),
            $data['payment_method'],
            isset($data['cash_received']) ? (int) $data['cash_received'] : null
        );

        // Langsung ke struk, seperti kasir pada umumnya
        return redirect()->route('kasir.struk', $order)
            ->with('status', 'Transaksi ' . $order->invoice_no . ' tersimpan.');
    }
}