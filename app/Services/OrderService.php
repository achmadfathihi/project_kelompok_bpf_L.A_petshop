<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Payment;

class OrderService
{
    public function __construct(private StockService $stock, private Notifier $notifier)
    {
    }

    /**
     * Membuat pesanan online.
     *
     * @param array<int,int> $cart       [product_id => qty]
     * @param array          $ship       recipient_name, recipient_phone, address
     * @param bool           $usePoints  true = pakai poin sebanyak mungkin (maksimal sebesar harga barang)
     */
    public function place(User $customer, array $cart, array $ship, bool $usePoints): Order
    {
        // Urutan produk dibuat tetap supaya dua checkout bersamaan tidak saling menunggu (deadlock)
        ksort($cart);

        return DB::transaction(function () use ($customer, $cart, $ship, $usePoints) {

            // Kunci baris pelanggan supaya saldo poin tidak bisa dipakai ganda
            $user = User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            
            $belumDibayar = Order::where('customer_id', $user->id)
                ->where('channel', 'online')
                ->whereIn('status', ['menunggu_ongkir', 'menunggu_pembayaran', 'menunggu_verifikasi'])
                ->count();

            if ($belumDibayar >= Order::MAX_UNPAID) {
                throw ValidationException::withMessages([
                    'keranjang' => 'Anda masih punya ' . $belumDibayar . ' pesanan yang belum selesai dibayar. '
                                . 'Selesaikan atau batalkan salah satunya dulu.',
                ]);
            }

            $products = Product::whereIn('id', array_keys($cart))
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'keranjang' => 'Ada produk di keranjang yang sudah tidak dijual. Periksa keranjang Anda.',
                ]);
            }

            $itemsTotal = 0;
            foreach ($cart as $id => $qty) {
                $itemsTotal += $products[$id]->price * $qty;
            }

            // Dihitung di server: semua poin yang ada, tapi tidak melebihi harga barang
            $pointsUsed = $usePoints ? min((int) $user->points, $itemsTotal) : 0;

            $order = Order::create([
                'customer_id'     => $user->id,
                'channel'         => 'online',
                'status'          => 'menunggu_ongkir',
                'recipient_name'  => $ship['recipient_name'],
                'recipient_phone' => $ship['recipient_phone'],
                'address'         => $ship['address'],
                'items_total'     => $itemsTotal,
                'points_used'     => $pointsUsed,
            ]);

            $order->update([
                'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($cart as $id => $qty) {
                $product = $products[$id];

                // Mengambil stok dari batch dengan aturan FEFO (melempar error kalau stok tidak cukup)
                $allocation = $this->stock->issue($product, $qty, $user->id, 'Pesanan ' . $order->invoice_no);

                $item = $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'qty'          => $qty,
                    'price'        => $product->price,
                    'subtotal'     => $product->price * $qty,
                ]);

                foreach ($allocation as $batchId => $taken) {
                    $item->batches()->create(['batch_id' => $batchId, 'qty' => $taken]);
                }
            }

            if ($pointsUsed > 0) {
                $user->decrement('points', $pointsUsed);

                PointTransaction::create([
                    'user_id'       => $user->id,
                    'order_id'      => $order->id,
                    'type'          => 'redeem',
                    'points'        => -$pointsUsed,
                    'balance_after' => $user->points,
                    'note'          => 'Dipakai untuk pesanan ' . $order->invoice_no,
                ]);
            }

            $this->notifier->toRole(
                'kasir',
                'Pesanan baru ' . $order->invoice_no,
                'Menunggu ongkir untuk dikirim ke ' . $order->recipient_name . '.',
                route('kasir.pesanan.show', $order, false)
            );

            return $order;
        });
    }

    /**
     * Membatalkan pesanan: stok kembali ke batch asalnya, poin dikembalikan.
     *
     * @param array<int,string> $allowedStatuses status yang boleh dibatalkan
     * @param bool $notifyCustomer false kalau pelanggan sendiri yang membatalkan
     */
    public function cancel(Order $order, string $reason, int $actorId, array $allowedStatuses, bool $notifyCustomer = true): void
    {
        DB::transaction(function () use ($order, $reason, $actorId, $allowedStatuses, $notifyCustomer) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan ini sudah tidak bisa dibatalkan.',
                ]);
            }

            foreach ($order->items()->with('batches')->get() as $item) {
                foreach ($item->batches as $row) {
                    $this->stock->returnToBatch(
                        $row->batch_id,
                        $row->qty,
                        $actorId,
                        'Pembatalan ' . $order->invoice_no
                    );
                }
            }

            if ($order->points_used > 0 && $order->customer_id) {
                $user = User::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
                $user->increment('points', $order->points_used);

                PointTransaction::create([
                    'user_id'       => $user->id,
                    'order_id'      => $order->id,
                    'type'          => 'refund',
                    'points'        => $order->points_used,
                    'balance_after' => $user->points,
                    'note'          => 'Pesanan ' . $order->invoice_no . ' dibatalkan',
                ]);
            }

            // Bukti bayar yang belum diperiksa ikut ditutup
            $order->payments()->where('status', 'menunggu')->update([
                'status'        => 'ditolak',
                'reject_reason' => 'Pesanan dibatalkan',
                'verified_by'   => $actorId,
                'verified_at'   => now(),
            ]);

            $order->update([
                'status'        => 'dibatalkan',
                'cancelled_at'  => now(),
                'cancel_reason' => $reason,
            ]);

            if ($notifyCustomer) {
                $this->notifier->to(
                    $order->customer_id,
                    'Pesanan dibatalkan',
                    $order->invoice_no . ': ' . $reason,
                    route('pesanan.show', $order, false)
                );
            }
        });
    }

    /** Karyawan menandai pesanan sudah dikirim lewat ojek online. */
    public function markShipped(Order $order, string $note): void
    {
        DB::transaction(function () use ($order, $note) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['diproses']);

            $order->update(['status' => 'dikirim', 'shipping_note' => $note, 'shipped_at' => now()]);

            $this->notifier->to(
                $order->customer_id,
                'Pesanan dikirim',
                $order->invoice_no . ' sedang dalam pengiriman. ' . $note,
                route('pesanan.show', $order, false)
            );
        });
    }

    /** Pelanggan mengonfirmasi barang sudah diterima. */
    public function confirmReceived(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['dikirim']);

            $order->update(['status' => 'selesai', 'completed_at' => now(), 'completed_auto' => false]);
        });
    }

    /**
     * Menandai selesai otomatis pesanan yang sudah AUTO_COMPLETE_DAYS hari berstatus dikirim.
     *
     * @return int jumlah pesanan yang ditandai selesai
     */
    public function completeDelivered(): int
    {
        $due = Order::where('channel', 'online')
            ->where('status', 'dikirim')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subDays(Order::AUTO_COMPLETE_DAYS))
            ->get();

        $jumlah = 0;

        foreach ($due as $order) {
            try {
                DB::transaction(function () use ($order) {
                    $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                    $this->mustBe($locked, ['dikirim']);

                    $locked->update(['status' => 'selesai', 'completed_at' => now(), 'completed_auto' => true]);

                    $telp = \App\Models\Setting::find('store_phone')?->value;

                    $this->notifier->to(
                        $locked->customer_id,
                        'Pesanan selesai otomatis',
                        $locked->invoice_no . ' ditandai selesai karena sudah lebih dari '
                            . Order::AUTO_COMPLETE_DAYS . ' hari sejak dikirim. Ada masalah dengan pesanan? Hubungi toko'
                            . ($telp ? ' di ' . $telp : '') . '.',
                        route('pesanan.show', $locked, false)
                    );
                });

                $jumlah++;
            } catch (ValidationException $e) {
                // Pelanggan baru saja menekan tombol diterima, lewati
            }
        }

        return $jumlah;
    }

    /**
     * Mengingatkan pelanggan untuk mengonfirmasi penerimaan. Satu pengingat per pesanan
     * (dijaga oleh dedupe_key, jadi aman dijalankan berulang).
     */
    public function remindReceipt(): void
    {
        $due = Order::where('channel', 'online')
            ->where('status', 'dikirim')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subHours(Order::RECEIPT_REMINDER_HOURS))
            ->where('shipped_at', '>', now()->subDays(Order::AUTO_COMPLETE_DAYS))
            ->get();

        foreach ($due as $order) {
            $this->notifier->to(
                $order->customer_id,
                'Pesanan sudah sampai?',
                $order->invoice_no . ': mohon konfirmasi jika barang sudah diterima. Jika tidak, pesanan selesai otomatis pada '
                    . $order->shipped_at->copy()->addDays(Order::AUTO_COMPLETE_DAYS)->format('d/m/Y H:i') . '.',
                route('pesanan.show', $order, false),
                'pengingat-terima-' . $order->id
            );
        }
    }

    /**
     * Membatalkan pesanan yang kedaluwarsa:
     *  - ongkir tidak diisi toko dalam SHIPPING_TIMEOUT_HOURS jam sejak pesanan dibuat
     *  - pembayaran melewati batas waktu (pay_before)
     * Pesanan "menunggu verifikasi" tidak disentuh, karena itu menunggu kasir.
     *
     * @return int jumlah pesanan yang dibatalkan
     */
    public function cancelExpired(): int
    {
        $stale = Order::where('channel', 'online')
            ->where(function ($q) {
                $q->where(fn ($w) => $w->where('status', 'menunggu_ongkir')
                        ->where('created_at', '<', now()->subHours(Order::SHIPPING_TIMEOUT_HOURS)))
                ->orWhere(fn ($w) => $w->where('status', 'menunggu_pembayaran')
                        ->whereNotNull('pay_before')
                        ->where('pay_before', '<', now()));
            })
            ->get();

        $jumlah = 0;

        foreach ($stale as $order) {
            $alasan = $order->status === 'menunggu_ongkir'
                ? 'Dibatalkan otomatis karena ongkir belum sempat dihitung toko dalam ' . Order::SHIPPING_TIMEOUT_HOURS . ' jam. Mohon maaf, silakan pesan kembali.'
                : 'Dibatalkan otomatis karena batas waktu pembayaran terlewati.';

            try {
                // Dicatat atas nama pelanggan karena kolom pencatat stok tidak boleh kosong
                $this->cancel($order, $alasan, $order->customer_id, [$order->status]);
                $jumlah++;
            } catch (ValidationException $e) {
                // Status berubah di saat yang sama (misalnya baru dibayar), lewati
            }
        }

        return $jumlah;
    }

    private function mustBe(Order $order, array $statuses): void
    {
        if (! in_array($order->status, $statuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status pesanan sudah berubah. Muat ulang halaman.',
            ]);
        }
    }

    /** Kasir mengisi (atau memperbaiki) ongkir. */
    public function setShipping(Order $order, int $fee): void
    {
        DB::transaction(function () use ($order, $fee) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['menunggu_ongkir', 'menunggu_pembayaran']);

            $pertama = $order->status === 'menunggu_ongkir';
            $data    = ['shipping_fee' => $fee, 'status' => 'menunggu_pembayaran'];

            // Batas bayar 24 jam dimulai saat ongkir pertama kali diisi
            if ($pertama) {
                $data['pay_before'] = now()->addHours(24);
            }

            $order->update($data);

            $this->notifier->to(
                $order->customer_id,
                $pertama ? 'Ongkir sudah diisi' : 'Ongkir diperbarui',
                $order->invoice_no . ': total bayar Rp' . number_format($order->grand_total, 0, ',', '.')
                    . '. Bayar sebelum ' . $order->pay_before->format('d/m/Y H:i') . '.',
                route('pesanan.show', $order, false)
            );
        });
    }

    /** Pelanggan mengirim bukti bayar. */
    public function submitPayment(Order $order, string $method, string $proofPath): Payment
    {
        return DB::transaction(function () use ($order, $method, $proofPath) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['menunggu_pembayaran']);

            if ($order->pay_before && $order->pay_before->isPast()) {
                throw ValidationException::withMessages([
                    'proof' => 'Batas waktu pembayaran sudah lewat. Hubungi toko.',
                ]);
            }

            $payment = $order->payments()->create([
                'method'     => $method,
                'amount'     => $order->grand_total,
                'proof_path' => $proofPath,
                'status'     => 'menunggu',
            ]);

            $order->update(['status' => 'menunggu_verifikasi', 'payment_method' => $method]);

            $this->notifier->toRole(
                'kasir',
                'Bukti pembayaran baru',
                $order->invoice_no . ' menunggu diverifikasi.',
                route('kasir.pesanan.show', $order, false)
            );

            return $payment;
        });
    }

    /** Kasir menyatakan pembayaran valid: pesanan diproses dan poin masuk. */
    public function verifyPayment(Order $order, int $actorId): void
    {
        DB::transaction(function () use ($order, $actorId) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['menunggu_verifikasi']);

            $payment = $order->payments()->where('status', 'menunggu')->latest('id')->lockForUpdate()->first();

            if (! $payment) {
                throw ValidationException::withMessages(['status' => 'Bukti pembayaran tidak ditemukan.']);
            }

            $payment->update(['status' => 'valid', 'verified_by' => $actorId, 'verified_at' => now()]);

            // 300 poin per kelipatan Rp25.000, dari harga barang setelah potongan poin (tanpa ongkir)
            $earned = $order->customer_id ? Order::pointsFor($order->items_after_points) : 0;

            $order->update(['status' => 'diproses', 'paid_at' => now(), 'points_earned' => $earned]);

            if ($earned > 0) {
                $user = User::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
                $user->increment('points', $earned);

                PointTransaction::create([
                    'user_id'       => $user->id,
                    'order_id'      => $order->id,
                    'type'          => 'earn',
                    'points'        => $earned,
                    'balance_after' => $user->points,
                    'note'          => 'Belanja ' . $order->invoice_no,
                ]);
            }

            $this->notifier->to(
                $order->customer_id,
                'Pembayaran diverifikasi',
                $order->invoice_no . ' sedang disiapkan toko.' . ($earned > 0 ? ' Anda mendapat ' . number_format($earned, 0, ',', '.') . ' poin.' : ''),
                route('pesanan.show', $order, false)
            );

            $this->notifier->toRole(
                'karyawan',
                'Pesanan perlu disiapkan',
                $order->invoice_no . ' sudah dibayar.',
                route('karyawan.pesanan.show', $order, false)
            );
        });
    }

    /**
     * Kasir menolak bukti: pelanggan boleh mengunggah ulang.
     * Penolakan ke-MAX_REJECTIONS membatalkan pesanan.
     *
     * @return bool true kalau pesanan ikut dibatalkan
     */
    public function rejectPayment(Order $order, string $reason, int $actorId): bool
    {
        return DB::transaction(function () use ($order, $reason, $actorId) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->mustBe($order, ['menunggu_verifikasi']);

            $order->payments()->where('status', 'menunggu')->update([
                'status'        => 'ditolak',
                'reject_reason' => $reason,
                'verified_by'   => $actorId,
                'verified_at'   => now(),
            ]);

            $rejected = $order->payments()->where('status', 'ditolak')->count();

            if ($rejected >= Order::MAX_REJECTIONS) {
                $alasan = 'Bukti pembayaran ditolak ' . Order::MAX_REJECTIONS . ' kali. Alasan terakhir: ' . $reason;

                // Pemberitahuan dikirim di sini, jadi cancel() tidak perlu mengirim lagi
                $this->cancel($order, $alasan, $actorId, ['menunggu_verifikasi'], false);

                $this->notifier->to(
                    $order->customer_id,
                    'Pesanan dibatalkan',
                    $order->invoice_no . ': ' . $alasan,
                    route('pesanan.show', $order, false)
                );

                return true;
            }

            // Waktu tidak menumpuk: ambil yang lebih lama antara batas awal dan "sekarang + 6 jam"
            $minimal   = now()->addHours(Order::MIN_RETRY_HOURS);
            $payBefore = ($order->pay_before && $order->pay_before->greaterThan($minimal))
                ? $order->pay_before
                : $minimal;

            $order->update(['status' => 'menunggu_pembayaran', 'pay_before' => $payBefore]);

            $this->notifier->to(
                $order->customer_id,
                'Bukti pembayaran ditolak',
                $order->invoice_no . ': ' . $reason . ' Sisa kesempatan kirim bukti: ' . (Order::MAX_REJECTIONS - $rejected) . '.',
                route('pesanan.show', $order, false)
            );

            return false;
        });
    }

    /**
     * Transaksi pembeli yang datang langsung ke toko. Langsung lunas dan selesai.
     *
     * @param array<int,int> $cart [product_id => qty]
     */
    public function placeOffline(User $cashier, array $cart, ?User $member, bool $usePoints, string $method): Order
    {
        ksort($cart);

        return DB::transaction(function () use ($cashier, $cart, $member, $usePoints, $method) {
            $user = $member ? User::whereKey($member->id)->lockForUpdate()->firstOrFail() : null;

            $products = Product::whereIn('id', array_keys($cart))->where('is_active', true)->get()->keyBy('id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'items' => 'Ada produk yang tidak ditemukan atau sudah tidak dijual.',
                ]);
            }

            $itemsTotal = 0;
            foreach ($cart as $id => $qty) {
                $itemsTotal += $products[$id]->price * $qty;
            }

            $pointsUsed = ($user && $usePoints) ? min((int) $user->points, $itemsTotal) : 0;
            $earned     = $user ? Order::pointsFor($itemsTotal - $pointsUsed) : 0;

            $order = Order::create([
                'customer_id'    => $user?->id,
                'cashier_id'     => $cashier->id,
                'channel'        => 'offline',
                'status'         => 'selesai',
                'payment_method' => $method,
                'items_total'    => $itemsTotal,
                'points_used'    => $pointsUsed,
                'shipping_fee'   => 0,
                'points_earned'  => $earned,
                'paid_at'        => now(),
            ]);

            $order->update([
                'invoice_no' => 'TKO-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($cart as $id => $qty) {
                $product    = $products[$id];
                $allocation = $this->stock->issue($product, $qty, $cashier->id, 'Penjualan ' . $order->invoice_no);

                $item = $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'qty'          => $qty,
                    'price'        => $product->price,
                    'subtotal'     => $product->price * $qty,
                ]);

                foreach ($allocation as $batchId => $taken) {
                    $item->batches()->create(['batch_id' => $batchId, 'qty' => $taken]);
                }
            }

            if ($user) {
                if ($pointsUsed > 0) {
                    $user->decrement('points', $pointsUsed);
                    PointTransaction::create([
                        'user_id' => $user->id, 'order_id' => $order->id, 'type' => 'redeem',
                        'points' => -$pointsUsed, 'balance_after' => $user->points,
                        'note' => 'Dipakai untuk transaksi ' . $order->invoice_no,
                    ]);
                }

                if ($earned > 0) {
                    $user->increment('points', $earned);
                    PointTransaction::create([
                        'user_id' => $user->id, 'order_id' => $order->id, 'type' => 'earn',
                        'points' => $earned, 'balance_after' => $user->points,
                        'note' => 'Belanja ' . $order->invoice_no,
                    ]);
                }
            }

            return $order;
        });
    }
}