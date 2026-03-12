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
            $table->string('incident_image')->nullable()->after('incident_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_moments', function (Blueprint $table) {
            $table->dropColumn('incident_image');
        });
    }
};
