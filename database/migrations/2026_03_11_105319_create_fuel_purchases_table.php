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
        Schema::create('fuel_purchases', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time');

            $table->string('vehicle_id');
            $table->string('driver_id');
            $table->string('petrol_pump_id');

            $table->decimal('liters', 8, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 12, 2);

            $table->string('pump_before')->nullable();
            $table->string('pump_after')->nullable();
            $table->string('tank_before')->nullable();
            $table->string('tank_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_purchases');
    }
};
