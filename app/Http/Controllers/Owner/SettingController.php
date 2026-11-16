<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('owner.pengaturan', ['setting' => Setting::pluck('value', 'key')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder'  => 'nullable|string|max:100',
            'qris_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        foreach (['bank_name', 'bank_account', 'bank_holder'] as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        if ($request->hasFile('qris_image')) {
            $old = Setting::find('qris_image')?->value;
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::put('qris_image', $request->file('qris_image')->store('pengaturan', 'public'));
        }

        return back()->with('status', 'Pengaturan toko disimpan.');
    }
}