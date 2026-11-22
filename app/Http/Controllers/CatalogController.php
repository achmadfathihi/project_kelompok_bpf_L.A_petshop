<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $urut = in_array($request->urut, ['nama', 'termurah', 'termahal'], true) ? $request->urut : 'nama';

        $products = Product::withStock()->with('category')
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('brand', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->kategori))
            ->when($urut === 'termurah', fn ($q) => $q->orderBy('price'))
            ->when($urut === 'termahal', fn ($q) => $q->orderByDesc('price'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('beranda', [
            'products'   => $products,
            'urut'       => $urut,
            'categories' => Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('name')->get(),
        ]);
    }
}