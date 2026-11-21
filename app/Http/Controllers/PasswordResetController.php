<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Reset lewat email hanya untuk pelanggan yang akunnya aktif. Akun staf direset owner.
    private function scope(): array
    {
        return ['role' => 'pelanggan', 'is_active' => true];
    }

    public function request()
    {
        return view('auth.lupa-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink(['email' => $request->email] + $this->scope());
        } catch (\Throwable $e) {
            report($e);

            // Saat pengembangan, tampilkan kesalahannya supaya mudah dilacak (misalnya Mailpit mati)
            if (config('app.debug')) {
                throw $e;
            }
        }

        // Pesan sama untuk semua kemungkinan: email terdaftar, tidak terdaftar, akun staf, atau terlalu sering
        return back()->with('status', 'Jika email itu terdaftar sebagai akun pelanggan, tautan untuk mengatur ulang password sudah dikirim. Periksa kotak masuk Anda.');
    }

    public function form(Request $request, string $token)
    {
        return view('auth.atur-ulang-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token') + $this->scope(),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Keluarkan semua sesi lama pelanggan itu
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil diganti. Silakan masuk dengan password baru.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
    }
}