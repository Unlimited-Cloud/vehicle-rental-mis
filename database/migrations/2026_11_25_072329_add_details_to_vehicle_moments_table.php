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
            $table->text('notes')->nullable();
            $table->string('pickup_destination')->nullable();
            $table->string('dropoff_destination')->nullable();
            $table->integer('pax')->nullable();
            $table->string('fuel_charge')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_moments', function (Blueprint $table) {
            $table->dropColumn([
                'notes',
                'pickup_destination',
                'dropoff_destination',
                'pax',
                'fuel_charge',
            ]);
        });
    }
};
