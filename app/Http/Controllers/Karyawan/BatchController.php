<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use App\Services\StockService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $batches = StockBatch::with('product')
            ->when(! $request->boolean('semua'), fn ($q) => $q->where('qty_left', '>', 0))
            ->orderByRaw('expired_at IS NULL')
            ->orderBy('expired_at')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('karyawan.batch.index', compact('batches'));
    }

    public function writeOff(StockBatch $batch, StockService $stock)
    {
        $stock->writeOff($batch, auth()->id());

        return back()->with('status', 'Sisa batch sudah dikeluarkan dari stok.');
    }
}