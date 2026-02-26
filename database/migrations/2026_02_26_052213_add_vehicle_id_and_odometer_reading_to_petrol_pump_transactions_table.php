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
        Schema::table('petrol_pump_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('petrol_pump_id');
            $table->decimal('odometer_reading', 10, 2)->nullable()->after('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petrol_pump_transactions', function (Blueprint $table) {
            //
        });
    }
};
