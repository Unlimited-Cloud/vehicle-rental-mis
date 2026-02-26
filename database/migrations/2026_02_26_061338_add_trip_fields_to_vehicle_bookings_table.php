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
            $table->unsignedBigInteger('customer_id')->nullable()->after('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('helper_id')->nullable()->after('driver_id');

            $table->integer('start_km')->nullable()->after('end_date');
            $table->integer('end_km')->nullable()->after('start_km');
            $table->decimal('approx_fuel_litre', 8, 2)->nullable()->after('end_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'customer_id',
                'driver_id',
                'helper_id',
                'start_km',
                'end_km',
                'approx_fuel_litre'
            ]);
        });
    }
};
