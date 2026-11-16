<?php

namespace App\Http\Controllers;

use App\Models\PointTransaction;

class PointController extends Controller
{
    public function index()
    {
        $history = PointTransaction::with('order')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->paginate(15);

        return view('poin.index', [
            'saldo'   => auth()->user()->points,
            'history' => $history,
        ]);
    }
}