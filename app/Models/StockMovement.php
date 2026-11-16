<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'batch_id', 'user_id', 'type', 'qty', 'note'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}