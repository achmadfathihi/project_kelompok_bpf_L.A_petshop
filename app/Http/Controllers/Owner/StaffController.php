<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    private const ROLES = ['karyawan', 'kasir'];

    // Hanya karyawan dan kasir yang boleh diurus di halaman ini
    private function staffOrFail(User $user): void
    {
        abort_unless(in_array($user->role, self::ROLES, true), 404);
    }

    // Menghentikan semua sesi dan cookie "ingat saya" milik akun tertentu
    private function endSessions(User $user): void
    {
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'nonaktif' ? 'nonaktif' : 'aktif';

        return view('owner.staf.index', [
            'staff'          => User::whereIn('role', self::ROLES)->where('is_active', $tab === 'aktif')->orderBy('name')->get(),
            'tab'            => $tab,
            'jumlahAktif'    => User::whereIn('role', self::ROLES)->where('is_active', true)->count(),
            'jumlahNonaktif' => User::whereIn('role', self::ROLES)->where('is_active', false)->count(),
        ]);
    }

    public function create()
    {
        return view('owner.staf.form', ['staff' => new User(['role' => 'kasir'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => ['required', 'regex:/^[0-9+]{8,20}$/', 'unique:users,phone'],
            'role'     => 'required|in:karyawan,kasir',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User();
        $user->forceFill([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'role'      => $data['role'],
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ])->save();

        return redirect()->route('owner.staf.index')
            ->with('status', "Akun {$user->name} dibuat. Berikan email dan password awalnya kepada yang bersangkutan.");
    }

    public function edit(User $user)
    {
        $this->staffOrFail($user);

        return view('owner.staf.form', ['staff' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->staffOrFail($user);

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'regex:/^[0-9+]{8,20}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'role'  => 'required|in:karyawan,kasir',
        ]);

        $user->forceFill($data)->save();

        return redirect()->route('owner.staf.index')->with('status', 'Data akun diperbarui.');
    }

    public function toggle(User $user)
    {
        $this->staffOrFail($user);

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        if (! $user->is_active) {
            $this->endSessions($user);
        }

        return back()->with('status', $user->is_active
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan dan langsung dikeluarkan dari semua sesi.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->staffOrFail($user);

        $data = $request->validate(['password' => 'required|string|min:8|confirmed']);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        $this->endSessions($user);

        return back()->with('status', "Password {$user->name} diganti. Sesi lamanya dikeluarkan, jadi ia harus masuk lagi.");
    }
}