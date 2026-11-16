<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('karyawan.kategori.index', compact('categories'));
    }

    public function create()
    {
        return view('karyawan.kategori.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        Category::create($data);

        return redirect()->route('karyawan.kategori.index')->with('status', 'Kategori ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('karyawan.kategori.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update($data);

        return redirect()->route('karyawan.kategori.index')->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'hapus' => "Kategori \"{$category->name}\" masih berisi produk, jadi tidak bisa dihapus.",
            ]);
        }

        $category->delete();

        return redirect()->route('karyawan.kategori.index')->with('status', 'Kategori dihapus.');
    }
}