<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['Owner Toko', 'owner@petshop.test',     'owner',     '081200000001'],
            ['Karyawan',   'karyawan@petshop.test',  'karyawan',  '081200000002'],
            ['Kasir',      'kasir@petshop.test',     'kasir',     '081200000003'],
            ['Pelanggan',  'pelanggan@petshop.test', 'pelanggan', '081200000004'],
        ];

        foreach ($akun as [$name, $email, $role, $phone]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name'     => $name,
                'role'     => $role,
                'phone'    => $phone,
                'password' => Hash::make('password123'),
            ])->save();
        }
    }
}