<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function index(Request $request)
    {
        $movements = StockMovement::with(['product', 'batch', 'user'])
            ->when(in_array($request->jenis, ['in', 'out']), fn ($q) => $q->where('type', $request->jenis))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('karyawan.stok.index', compact('movements'));
    }

    public function create(Request $request)
    {
        return view('karyawan.stok.create', [
            'products' => Product::withStock()->orderBy('name')->get(),
            'selected' => $request->product_id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out',
            'qty'        => 'required|integer|min:1|max:100000',
            'expired_at' => 'nullable|date|after:today',
            'note'       => 'nullable|string|max:255|required_if:type,out',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'in') {
            $this->stock->receive($product, $data['qty'], $data['expired_at'] ?? null, auth()->id(), $data['note'] ?? null);
        } else {
            $this->stock->issue($product, $data['qty'], auth()->id(), $data['note']);
        }

        return redirect()->route('karyawan.stok.index')->with('status', 'Catatan stok tersimpan.');
    }
}