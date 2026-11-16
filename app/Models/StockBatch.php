<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $fillable = ['product_id', 'user_id', 'qty_in', 'qty_left', 'expired_at'];

    protected $casts = ['expired_at' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Batch yang masih ada sisa DAN belum lewat tanggal kedaluwarsa
    public function scopeSellable($query)
    {
        return $query->where('qty_left', '>', 0)
            ->where(function ($w) {
                $w->whereNull('expired_at')
                  ->orWhereDate('expired_at', '>=', today());
            });
    }

    // Sisa hari sampai kedaluwarsa (negatif = sudah lewat), null jika tanpa kedaluwarsa
    protected function daysLeft(): Attribute
    {
        return Attribute::get(fn () => $this->expired_at
            ? (int) today()->diffInDays($this->expired_at, false)
            : null);
    }

    // habis | kedaluwarsa | segera | baik
    protected function status(): Attribute
    {
        return Attribute::get(function () {
            if ($this->qty_left <= 0)        return 'habis';
            if ($this->days_left === null)   return 'baik';
            if ($this->days_left < 0)        return 'kedaluwarsa';
            if ($this->days_left <= 30)      return 'segera';
            return 'baik';
        });
    }
}