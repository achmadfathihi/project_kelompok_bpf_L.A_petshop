<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'brand', 'size', 'price',
        'min_stock', 'photo', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Menghitung stok layak jual dari batch, dimuat sekaligus untuk banyak produk.
    // Pakai: Product::withStock()->get()
    public function scopeWithStock($query)
    {
        return $query->withSum(['batches as stock' => fn ($q) => $q->sellable()], 'qty_left');
    }

    // $product->stock : stok layak jual (jumlah sisa batch yang belum kedaluwarsa).
    // Kalau produk dimuat tanpa withStock(), dihitung langsung dari database.
    protected function stock(): Attribute
    {
        return Attribute::get(function ($value, array $attributes) {
            if (array_key_exists('stock', $attributes)) {
                return (int) $value;
            }

            return (int) $this->batches()->sellable()->sum('qty_left');
        });
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->photo) {
                return asset('storage/' . $this->photo);
            }

            return 'data:image/svg+xml;utf8,' . rawurlencode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300">'
                . '<rect width="100%" height="100%" fill="#e9ecef"/>'
                . '<text x="50%" y="50%" fill="#8898aa" font-size="20" text-anchor="middle" '
                . 'dominant-baseline="middle" font-family="Arial">Belum ada foto</text></svg>'
            );
        });
    }

    // habis | menipis | tersedia
    protected function stockStatus(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->stock <= 0                => 'habis',
            $this->stock <= $this->min_stock => 'menipis',
            default                          => 'tersedia',
        });
    }
}