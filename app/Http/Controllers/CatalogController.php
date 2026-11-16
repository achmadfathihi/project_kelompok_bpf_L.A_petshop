<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::withStock()->with('category')
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('brand', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->kategori))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('beranda', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}