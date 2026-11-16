<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained();      // karyawan yang menerima barang
            $table->unsignedInteger('qty_in');                // jumlah saat masuk
            $table->unsignedInteger('qty_left');              // sisa sekarang
            $table->date('expired_at')->nullable();           // tanggal di kemasan
            $table->timestamps();

            $table->index(['product_id', 'expired_at']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('expired_at');
            $table->foreignId('batch_id')->nullable()->after('product_id')
                  ->constrained('stock_batches')->restrictOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
            $table->date('expired_at')->nullable();
        });

        Schema::dropIfExists('stock_batches');
    }
};