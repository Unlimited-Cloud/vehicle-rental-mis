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
        Schema::table('agents', function (Blueprint $table) {
            $table->string('agent_code')->nullable()->after('id');
        });

        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->string('agent_code')->nullable()->after('helper_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('agent_code');
        });

        Schema::table('vehicle_bookings', function (Blueprint $table) {
            $table->dropColumn('agent_code');
        });
    }
};
