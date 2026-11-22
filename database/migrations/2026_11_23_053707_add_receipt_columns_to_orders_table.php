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
    Schema::table('orders', function (Blueprint $table) {
        $table->unsignedInteger('cash_received')->nullable()->after('payment_method');
        $table->unsignedSmallInteger('receipt_prints')->default(0)->after('cash_received');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['cash_received', 'receipt_prints']);
    });
}
};
