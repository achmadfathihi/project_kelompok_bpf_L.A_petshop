<?php

namespace App\Http\Controllers;

use App\Models\PointTransaction;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['masuk', 'dipakai'], true) ? $request->tab : 'semua';

        $history = PointTransaction::with('order')
            ->where('user_id', auth()->id())
            ->when($tab === 'masuk', fn ($q) => $q->whereIn('type', ['earn', 'refund']))
            ->when($tab === 'dipakai', fn ($q) => $q->where('type', 'redeem'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totals = PointTransaction::where('user_id', auth()->id())
            ->selectRaw('type, SUM(points) as total')->groupBy('type')->pluck('total', 'type');

        return view('poin.index', [
            'saldo'   => auth()->user()->points,
            'history' => $history,
            'tab'     => $tab,
            'didapat' => (int) ($totals['earn'] ?? 0),
            'dipakai' => max(abs((int) ($totals['redeem'] ?? 0)) - (int) ($totals['refund'] ?? 0), 0),
        ]);
    }
}