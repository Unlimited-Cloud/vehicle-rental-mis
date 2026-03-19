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
        Schema::table('petrol_pump_transactions', function (Blueprint $table) {
            $table->string('pump_before')->nullable()->after('remarks');
            $table->string('pump_after')->nullable()->after('pump_before');
            $table->string('tank_before')->nullable()->after('pump_after');
            $table->string('tank_after')->nullable()->after('tank_before');
            $table->unsignedBigInteger('driver_id')->nullable()->after('tank_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petrol_pump_transactions', function (Blueprint $table) {
            //
        });
    }
};
