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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['pelanggan','karyawan','kasir','owner'])
                ->default('pelanggan')->after('email');
            $table->string('phone', 20)->nullable()->unique()->after('role');
            $table->text('address')->nullable()->after('phone');
            $table->unsignedInteger('points')->default(0)->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','phone','address','points']);
        });
    }
};
