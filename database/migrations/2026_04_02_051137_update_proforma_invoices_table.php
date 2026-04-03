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

        Schema::table('proforma_invoices', function (Blueprint $table) {

            // Add customer_id
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->string('file_no')->nullable();
            // Make existing fields nullable
            $table->unsignedBigInteger('vehicle_booking_id')->nullable()->change();
            $table->decimal('rate_per_day', 10, 2)->nullable()->change();
            $table->decimal('sub_total', 10, 2)->nullable()->change();
            $table->date('from_date')->nullable()->change();
            $table->date('to_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {

            // Drop customer_id
            $table->dropColumn('customer_id');
            // Revert nullable changes
            $table->unsignedBigInteger('vehicle_booking_id')->nullable(false)->change();
            $table->decimal('rate_per_day', 10, 2)->nullable(false)->change();
            $table->decimal('sub_total', 10, 2)->nullable(false)->change();
        });
    }
};
