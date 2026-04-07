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
        Schema::table('trip_routes', function (Blueprint $table) {
            $table->decimal('van_price', 10, 2)->nullable()->after('bus_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_routes', function (Blueprint $table) {
            $table->dropColumn('van_price');
        });
    }
};
