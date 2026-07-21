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
        Schema::table('vehicle_moments', function (Blueprint $table) {
            // Depot
            $table->dateTime('depot_departure_datetime')->nullable();
            $table->decimal('depot_departure_km', 10, 2)->nullable();
            $table->string('depot_departure_image')->nullable();
            $table->text('depot_departure_comments')->nullable();

            // Pickup
            $table->dateTime('pickup_arrival_datetime')->nullable();
            $table->decimal('pickup_arrival_km', 10, 2)->nullable();
            $table->string('pickup_arrival_image')->nullable();
            $table->text('pickup_arrival_comments')->nullable();

            // Dropoff
            $table->dateTime('dropoff_datetime')->nullable();
            $table->decimal('dropoff_km', 10, 2)->nullable();
            $table->string('dropoff_image')->nullable();
            $table->text('dropoff_comments')->nullable();

            // Estimated return
            $table->decimal('estimated_return_to_depot_km', 10, 2)->nullable();
            $table->integer('estimated_return_to_depot_minutes')->nullable();

            $table->decimal('estimated_return_to_pickup_km', 10, 2)->nullable();
            $table->integer('estimated_return_to_pickup_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_moments', function (Blueprint $table) {
            //
        });
    }
};
