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
            $table->unsignedBigInteger('trip_category_id')->nullable()->after('vehicle_no');
            $table->unsignedBigInteger('trip_route_id')->nullable()->after('trip_category_id');
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
