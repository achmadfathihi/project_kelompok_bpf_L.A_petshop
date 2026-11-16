<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'url', 'dedupe_key', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];
}