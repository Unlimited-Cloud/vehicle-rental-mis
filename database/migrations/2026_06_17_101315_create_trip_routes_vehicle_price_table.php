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
        Schema::create('trip_routes_vehicle_price', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('trip_route_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_routes_vehicle_price');
    }
};
