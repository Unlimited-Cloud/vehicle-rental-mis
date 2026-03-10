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
        Schema::create('vehicle_moments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('helper_id')->nullable();

            $table->string('vehicle_no')->nullable();
            $table->string('signage_information')->nullable();

            // START INFORMATION
            $table->dateTime('start_datetime')->nullable();
            $table->integer('start_km')->nullable();
            $table->string('start_image')->nullable();
            $table->text('start_comments')->nullable();

            // END INFORMATION
            $table->dateTime('end_datetime')->nullable();
            $table->integer('end_km')->nullable();
            $table->string('end_image')->nullable();
            $table->text('end_comments')->nullable();

            // INCIDENT
            $table->boolean('has_incident')->default(0);
            $table->text('incident_report')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_moments');
    }
};
