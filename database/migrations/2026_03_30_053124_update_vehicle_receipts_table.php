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
        Schema::table('vehicle_receipts', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable()->change();
            $table->dateTime('end_datetime')->nullable()->change();
            $table->decimal('rate_per_day', 10, 2)->nullable()->change();
            $table->string('file_no')->nullable(); // or remove nullable if required

            $table->unsignedBigInteger('vehicle_booking_id')->nullable()->change();
            $table->unsignedBigInteger('vehicle_moment_id')->nullable()->change();
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_receipts', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable(false)->change();
            $table->dateTime('end_datetime')->nullable(false)->change();
            $table->unsignedBigInteger('vehicle_booking_id')->nullable(false)->change();
            $table->unsignedBigInteger('vehicle_moment_id')->nullable(false)->change();
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->dropColumn('file_no');
        });
    }
};
