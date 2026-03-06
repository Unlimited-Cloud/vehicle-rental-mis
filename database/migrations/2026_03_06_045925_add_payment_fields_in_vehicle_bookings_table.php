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
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->decimal('rate_per_day', 10, 2)->nullable()->after('notes');
            $table->decimal('sub_total', 10, 2)->nullable()->after('rate_per_day');
            $table->string('tax_amount_type', 10, 2)->nullable()->after('sub_total');
            $table->decimal('tax', 10, 2)->nullable()->after('tax_amount_type');
            $table->string('discount_amount_type', 10, 2)->nullable()->after('tax');
            $table->decimal('discount', 10, 2)->nullable()->after('discount_amount_type');
            $table->decimal('total_amount', 10, 2)->nullable()->after('discount');
            $table->integer('payment_status')->default(0)->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'rate_per_day', 'sub_total','tax_amount_type', 'tax','discount_amount_type', 'discount', 'total_amount', 'payment_status'
            ]);
        });
    }
};
