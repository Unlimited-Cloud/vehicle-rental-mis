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
        Schema::create('trip_route_vehicle_type_prices', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type');
            $table->decimal('per_km', 10, 2)->nullable();
            $table->decimal('per_hour', 10, 2)->nullable();
            $table->decimal('overnight_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_route_vehicle_type_prices');
    }
};
