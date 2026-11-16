<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    public const TYPES = [
        'earn'   => ['Poin didapat', 'success'],
        'redeem' => ['Dipakai', 'warning'],
        'refund' => ['Dikembalikan', 'info'],
    ];

    protected $fillable = ['user_id', 'order_id', 'type', 'points', 'balance_after', 'note'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}