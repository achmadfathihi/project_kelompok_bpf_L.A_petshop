<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:150',
            'brand'       => 'nullable|string|max:100',
            'size'        => 'nullable|string|max:50',
            'price'       => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:0',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    // Tab tempat produk berada, dipakai untuk mengarahkan kembali setelah simpan
    private function tabOf(Product $product): string
    {
        return $product->is_active ? 'aktif' : 'nonaktif';
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'nonaktif' ? 'nonaktif' : 'aktif';

        $products = Product::withStock()->with('category')->withCount('stockMovements')
            ->where('is_active', $tab === 'aktif')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('brand', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('category_id', $request->kategori))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('karyawan.produk.index', [
            'products'       => $products,
            'categories'     => Category::orderBy('name')->get(),
            'tab'            => $tab,
            'jumlahAktif'    => Product::where('is_active', true)->count(),
            'jumlahNonaktif' => Product::where('is_active', false)->count(),
        ]);
    }

    public function create()
    {
        return view('karyawan.produk.form', [
            'product'    => new Product(['is_active' => true, 'min_stock' => 5]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        unset($data['photo']);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('produk', 'public');
        }

        $product = Product::create($data); // stok dimulai dari 0, diisi lewat "Catat Stok"

        return redirect()->route('karyawan.produk.index', ['tab' => $this->tabOf($product)])
            ->with('status', 'Produk ditambahkan. Catat barang masuk untuk mengisi stoknya.');
    }

    public function edit(Product $product)
    {
        return view('karyawan.produk.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        unset($data['photo']);
        if ($request->hasFile('photo')) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $request->file('photo')->store('produk', 'public');
        }

        $product->update($data);

        return redirect()->route('karyawan.produk.index', ['tab' => $this->tabOf($product)])
            ->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->stockMovements()->exists()) {
            return back()->withErrors([
                'hapus' => "Produk \"{$product->name}\" sudah punya riwayat stok, jadi tidak bisa dihapus. "
                         . 'Nonaktifkan saja.',
            ]);
        }

        if ($product->photo) {
            Storage::disk('public')->delete($product->photo);
        }
        $product->delete();

        return back()->with('status', 'Produk dihapus.');
    }

    // Aktifkan <-> nonaktifkan dengan satu klik
    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', $product->is_active
            ? "Produk \"{$product->name}\" diaktifkan dan tampil di katalog."
            : "Produk \"{$product->name}\" dinonaktifkan. Tidak tampil di katalog, riwayat dan stoknya tetap tercatat.");
    }
}