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
        Schema::table('vehicle_owners', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();

            $table->string('wallet_name')->nullable();
            $table->string('wallet_number')->nullable();

            // percentage commission retained by company
            $table->decimal('commission_rate', 8, 2)
                ->default(0)
                ->after('wallet_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_owners', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_code',
                'bank_account_name',
                'bank_account_number',
                'wallet_name',
                'wallet_number',
                'commission_rate',
            ]);
        });
    }
};
