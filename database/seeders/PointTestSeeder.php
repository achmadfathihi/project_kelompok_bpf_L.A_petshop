<?php

namespace Database\Seeders;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'pelanggan@petshop.test')->firstOrFail();

        if (PointTransaction::where('user_id', $user->id)->exists()) {
            return; // jangan menambah dua kali
        }

        $user->forceFill(['points' => 1500])->save();

        PointTransaction::create([
            'user_id'       => $user->id,
            'type'          => 'earn',
            'points'        => 1500,
            'balance_after' => 1500,
            'note'          => 'Poin awal (data uji)',
        ]);
    }
}