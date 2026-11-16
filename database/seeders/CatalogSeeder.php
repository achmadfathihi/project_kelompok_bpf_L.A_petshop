<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $staf  = User::where('email', 'karyawan@petshop.test')->firstOrFail();
        $stock = app(StockService::class);

        // [kategori, nama, merek, ukuran, harga, [[jumlah, tanggal kedaluwarsa], ...]]
        // Tanggal contoh dibuat untuk uji pada 7 November 2026. Ubah jika Anda mencoba di waktu lain.
        $data = [
            ['Makanan Kucing', 'Makanan Kucing Tuna',      'Merek A', '1 kg',      58000, [[20, '2027-05-20']]],
            ['Makanan Kucing', 'Makanan Anak Kucing',      'Merek B', '2 kg',     215000, [[5, '2026-11-30'], [15, '2027-04-10']]],
            ['Makanan Anjing', 'Makanan Anjing Daging',    'Merek C', '1,5 kg',    72000, [[4, '2026-11-01'], [12, '2027-06-01']]], // batch pertama sudah lewat
            ['Vitamin',        'Vitamin Bulu Kucing',      'Merek D', '60 ml',     45000, []],                                      // habis
            ['Pasir Kucing',   'Pasir Gumpal Wangi',       'Merek E', '10 L',      65000, [[4, null]]],                             // menipis, tanpa kedaluwarsa
            ['Vitamin',        'Suplemen Tulang Anjing',   'Merek F', '30 tablet', 90000, [[6, '2026-11-16']]],                     // hampir kedaluwarsa
        ];

        foreach ($data as [$kategori, $nama, $merek, $ukuran, $harga, $batches]) {
            $category = Category::firstOrCreate(['name' => $kategori]);

            $product = Product::firstOrCreate(
                ['name' => $nama],
                [
                    'category_id' => $category->id,
                    'brand'       => $merek,
                    'size'        => $ukuran,
                    'price'       => $harga,
                    'min_stock'   => 5,
                    'is_active'   => true,
                ]
            );

            if (! $product->wasRecentlyCreated) {
                continue;
            }

            foreach ($batches as [$jumlah, $tanggal]) {
                $stock->receive($product, $jumlah, $tanggal, $staf->id, 'Stok awal (data contoh)');
            }

            // Contoh FEFO: 2 bungkus terjual dari batch yang paling dekat kedaluwarsa
            if ($nama === 'Makanan Anak Kucing') {
                $stock->issue($product, 2, $staf->id, 'Contoh pengeluaran');
            }
        }
    }
}