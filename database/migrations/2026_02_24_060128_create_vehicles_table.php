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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->decimal('rent_price_per_day', 10, 2);
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1); // 1 = Available, 0 = Not Available
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
