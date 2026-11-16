<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable()->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->restrictOnDelete(); // transaksi di toko
            $table->enum('channel', ['online', 'offline'])->default('online');
            $table->string('status', 30)->default('menunggu_ongkir')->index();

            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->text('address')->nullable();

            $table->unsignedInteger('items_total');                // total harga barang
            $table->unsignedInteger('points_used')->default(0);    // potongan poin (Rp)
            $table->unsignedInteger('shipping_fee')->nullable();   // diisi kasir (Tahap 4)
            $table->unsignedInteger('points_earned')->default(0);  // diisi saat pembayaran valid
            $table->timestamp('pay_before')->nullable();           // batas bayar (Tahap 4)

            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');                        // salinan nama saat dibeli
            $table->unsignedInteger('qty');
            $table->unsignedInteger('price');                      // salinan harga saat dibeli
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('order_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('stock_batches')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->timestamps();
        });

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);                            // earn | redeem | refund
            $table->integer('points');                             // positif = masuk, negatif = keluar
            $table->unsignedInteger('balance_after');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('order_item_batches');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};