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
        Schema::table('trip_routes_vehicle_price', function (Blueprint $table) {
            // Make trip_route_id nullable
            $table->unsignedBigInteger('trip_route_id')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
            // Add new nullable fields
            $table->decimal('per_km', 10, 2)->nullable()->after('trip_route_id');
            $table->decimal('per_hour', 10, 2)->nullable()->after('per_km');
            $table->boolean('overnight')->nullable()->after('per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_routes_vehicle_price', function (Blueprint $table) {
            $table->dropColumn(['per_km', 'per_hour', 'overnight']);

            $table->unsignedBigInteger('trip_route_id')->nullable(false)->change();
        });
    }
};
