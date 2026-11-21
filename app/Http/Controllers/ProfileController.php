<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profil.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => ['required', 'regex:/^[0-9+]{8,20}$/', Rule::unique('users', 'phone')->ignore(auth()->id())],
            'address' => 'nullable|string|max:500',
        ]);

        auth()->user()->update($data);

        return back()->with('status', 'Profil diperbarui.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        auth()->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('status', 'Password diperbarui.');
    }
}