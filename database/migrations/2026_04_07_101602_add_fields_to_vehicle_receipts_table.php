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
            $table->string('receipt_path')->nullable()->after('pdf_path');
            $table->string('check_no')->nullable()->after('receipt_path');
            $table->date('check_date')->nullable()->after('check_no');
            $table->decimal('amount', 10, 2)->nullable()->after('check_date');

            $table->string('payment_method')->nullable()->after('amount');
            $table->string('bank_name')->nullable()->after('payment_method');
            $table->string('bank_account')->nullable()->after('bank_name');

            $table->text('remarks')->nullable()->after('bank_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_receipts', function (Blueprint $table) {
            //
        });
    }
};
