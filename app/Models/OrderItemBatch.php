<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemBatch extends Model
{
    protected $fillable = ['order_item_id', 'batch_id', 'qty'];

    public function batch()
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }
}