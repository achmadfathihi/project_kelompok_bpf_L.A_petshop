<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('pay_before');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20);                       // transfer | qris
            $table->unsignedInteger('amount');                  // nominal yang harus dibayar
            $table->string('proof_path');                       // lokasi file bukti (privat)
            $table->string('status', 20)->default('menunggu');  // menunggu | valid | ditolak
            $table->string('reject_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paid_at']);
        });
    }
};