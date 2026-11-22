<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // [label, warna badge]
    public const STATUS = [
        'menunggu_ongkir'      => ['Menunggu ongkir', 'warning'],
        'menunggu_pembayaran'  => ['Menunggu pembayaran', 'info'],
        'menunggu_verifikasi'  => ['Menunggu verifikasi', 'primary'],
        'diproses'             => ['Diproses', 'primary'],
        'dikirim'              => ['Dikirim', 'info'],
        'selesai'              => ['Selesai', 'success'],
        'dibatalkan'           => ['Dibatalkan', 'secondary'],
    ];

    // Pelanggan hanya boleh membatalkan sebelum membayar
    public const CUSTOMER_CANCELLABLE = ['menunggu_ongkir', 'menunggu_pembayaran'];
    // Kasir boleh membatalkan sampai pembayaran diverifikasi
    public const CASHIER_CANCELLABLE = ['menunggu_ongkir', 'menunggu_pembayaran', 'menunggu_verifikasi'];
    public const MAX_REJECTIONS  = 3;   // penolakan bukti ke-3 membatalkan pesanan
    public const MIN_RETRY_HOURS = 6;   // waktu minimal untuk mengunggah ulang setelah ditolak
    public const SHIPPING_TIMEOUT_HOURS = 24;  // ongkir tidak diisi selama ini: pesanan dibatalkan otomatis
    public const AUTO_COMPLETE_DAYS     = 2;   // selesai otomatis setelah sekian hari sejak dikirim
    public const RECEIPT_REMINDER_HOURS = 24;  // pengingat "sudah sampai?" setelah sekian jam sejak dikirim
    public const MAX_UNPAID = 3;  // maksimal pesanan online yang belum diverifikasi pembayarannya per pelanggan

    // Aturan poin: tiap kelipatan Rp25.000 mendapat 300 poin
    public const POINT_STEP   = 25000;
    public const POINT_REWARD = 300;

    protected $fillable = [
        'invoice_no', 'customer_id', 'cashier_id', 'channel', 'status',
        'recipient_name', 'recipient_phone', 'address',
        'items_total', 'points_used', 'shipping_fee', 'points_earned',
        'pay_before', 'cancelled_at', 'cancel_reason', 'payment_method',
        'paid_at', 'shipping_note', 'shipped_at', 'completed_at', 'completed_auto','cash_received',
    ];

    protected $casts = [
        'pay_before'   => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at'   => 'datetime',
        'completed_at' => 'datetime',
        'completed_auto' => 'boolean',
    ];

    public static function pointsFor(int $amount): int
    {
        return intdiv(max($amount, 0), self::POINT_STEP) * self::POINT_REWARD;
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isOnline(): bool
    {
        return $this->channel === 'online';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'menunggu_pembayaran'
            && $this->pay_before
            && $this->pay_before->isPast();
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUS[$this->status][0] ?? $this->status);
    }

    protected function statusColor(): Attribute
    {
        return Attribute::get(fn () => self::STATUS[$this->status][1] ?? 'secondary');
    }

    // Harga barang setelah dipotong poin
    protected function itemsAfterPoints(): Attribute
    {
        return Attribute::get(fn () => $this->items_total - $this->points_used);
    }

    // Total yang dibayar pelanggan; null selama ongkir belum diisi
    protected function grandTotal(): Attribute
    {
        return Attribute::get(fn () => $this->shipping_fee === null
            ? null
            : $this->items_after_points + $this->shipping_fee);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, self::CUSTOMER_CANCELLABLE, true);
    }
}