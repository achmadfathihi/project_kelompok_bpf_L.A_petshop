<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = User::where('role', 'pelanggan')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('email', 'like', '%' . $request->q . '%')
                      ->orWhere('phone', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('owner.member.index', [
            'members'      => $members,
            'totalMember'  => User::where('role', 'pelanggan')->count(),
            'poinBeredar'  => (int) User::where('role', 'pelanggan')->sum('points'),
        ]);
    }
}