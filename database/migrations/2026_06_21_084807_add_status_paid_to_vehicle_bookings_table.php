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
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'started',
                'completed',
                'paid'
            ])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'started',
                'completed'
            ])
                ->default('pending')
                ->change();
        });
    }
};
