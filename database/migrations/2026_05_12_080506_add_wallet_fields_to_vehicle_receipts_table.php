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
        Schema::table('vehicle_receipts', function (Blueprint $table) {
            $table->string('wallet_type')->nullable();
            $table->string('wallet_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_receipts', function (Blueprint $table) {
            $table->dropColumn(['wallet_type', 'wallet_number']);
        });
    }
};
