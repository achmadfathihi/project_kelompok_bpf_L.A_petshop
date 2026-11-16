<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_note')->nullable()->after('address'); // nama driver / plat / no. ojol
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('completed_at')->nullable()->after('shipped_at');
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();           // alamat relatif, contoh: /pesanan/5
            $table->string('dedupe_key')->nullable();    // mencegah notifikasi ganda (ringkasan harian)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->unique(['user_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_note', 'shipped_at', 'completed_at']);
        });
    }
};