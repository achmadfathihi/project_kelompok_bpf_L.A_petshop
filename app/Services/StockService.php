<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Barang masuk: membuat satu batch baru beserta catatan riwayatnya.
     */
    public function receive(Product $product, int $qty, ?string $expiredAt, int $userId, ?string $note = null): StockBatch
    {
        return DB::transaction(function () use ($product, $qty, $expiredAt, $userId, $note) {
            $batch = StockBatch::create([
                'product_id' => $product->id,
                'user_id'    => $userId,
                'qty_in'     => $qty,
                'qty_left'   => $qty,
                'expired_at' => $expiredAt,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'batch_id'   => $batch->id,
                'user_id'    => $userId,
                'type'       => 'in',
                'qty'        => $qty,
                'note'       => $note,
            ]);

            return $batch;
        });
    }

    /**
     * Barang keluar dengan aturan FEFO: batch yang paling dekat kedaluwarsanya diambil lebih dulu.
     * Batch yang sudah kedaluwarsa tidak ikut dihitung.
     *
     * @return array<int,int>  [batch_id => jumlah yang diambil]
     */
    public function issue(Product $product, int $qty, int $userId, string $note): array
    {
        return DB::transaction(function () use ($product, $qty, $userId, $note) {
            // Kunci produk supaya dua pengeluaran bersamaan antre, bukan saling tabrak
            Product::whereKey($product->id)->lockForUpdate()->first();

            $batches = StockBatch::where('product_id', $product->id)
                ->sellable()
                ->orderByRaw('expired_at IS NULL')   // yang tanpa kedaluwarsa terakhir
                ->orderBy('expired_at')              // lalu tanggal terdekat dulu
                ->orderBy('id')                      // tanggal sama: yang datang lebih dulu
                ->lockForUpdate()
                ->get();

            $available = (int) $batches->sum('qty_left');

            if ($available < $qty) {
                throw ValidationException::withMessages([
                    'qty' => "Stok layak jual {$product->name} hanya {$available}, tidak cukup untuk {$qty}.",
                ]);
            }

            $remaining  = $qty;
            $allocation = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($batch->qty_left, $remaining);

                $batch->decrement('qty_left', $take);

                StockMovement::create([
                    'product_id' => $product->id,
                    'batch_id'   => $batch->id,
                    'user_id'    => $userId,
                    'type'       => 'out',
                    'qty'        => $take,
                    'note'       => $note,
                ]);

                $allocation[$batch->id] = $take;
                $remaining -= $take;
            }

            return $allocation;
        });
    }

    /**
     * Mengeluarkan seluruh sisa satu batch tertentu (misalnya barang kedaluwarsa yang dibuang).
     */
    public function writeOff(StockBatch $batch, int $userId): void
    {
        DB::transaction(function () use ($batch, $userId) {
            $batch = StockBatch::lockForUpdate()->findOrFail($batch->id);

            if ($batch->qty_left <= 0) {
                return;
            }

            $expired = $batch->expired_at && $batch->expired_at->lt(today());

            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id'   => $batch->id,
                'user_id'    => $userId,
                'type'       => 'out',
                'qty'        => $batch->qty_left,
                'note'       => $expired ? 'Kedaluwarsa, dibuang' : 'Sisa batch dikeluarkan manual',
            ]);

            $batch->update(['qty_left' => 0]);
        });
    }

    /**
     * Mengembalikan barang ke batch asalnya (misalnya pesanan dibatalkan).
     */
    public function returnToBatch(int $batchId, int $qty, int $userId, string $note): void
    {
        DB::transaction(function () use ($batchId, $qty, $userId, $note) {
            $batch = StockBatch::lockForUpdate()->findOrFail($batchId);

            $batch->increment('qty_left', $qty);

            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id'   => $batch->id,
                'user_id'    => $userId,
                'type'       => 'in',
                'qty'        => $qty,
                'note'       => $note,
            ]);
        });
    }
}