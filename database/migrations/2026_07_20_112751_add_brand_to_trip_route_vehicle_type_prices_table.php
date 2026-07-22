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
        Schema::table('trip_route_vehicle_type_prices', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_route_vehicle_type_prices', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
