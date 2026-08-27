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
            $table->text('itinerary')->nullable()->after('notes');
            $table->decimal('est_km', 10, 2)->nullable()->after('no_of_hours');
            $table->boolean('overnight')->nullable()->after('est_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'itinerary',
                'est_km',
                'overnight',
            ]);
        });
    }
};
