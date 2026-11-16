<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // DATA CONTOH. Ganti lewat menu Pengaturan Toko (login owner)
        $contoh = [
            'bank_name'    => 'Bank Contoh',
            'bank_account' => '1234567890',
            'bank_holder'  => 'L. A Petshop Rumbai',
        ];

        foreach ($contoh as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}