<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('size')->nullable();               // contoh: 1 kg, 10 L
            $table->unsignedInteger('price');                 // rupiah, tanpa desimal
            $table->unsignedInteger('stock')->default(0);     // hanya diubah lewat catatan stok
            $table->unsignedInteger('min_stock')->default(5); // batas "stok menipis"
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
