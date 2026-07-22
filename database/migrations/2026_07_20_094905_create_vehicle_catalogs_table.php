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
        Schema::create('vehicle_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable(false);
            // Vehicle specifications - all nullable
            $table->string('vehicle_type')->nullable();
            $table->string('model')->nullable();
            $table->integer('seater')->nullable();
            $table->year('year')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('image')->nullable();
            $table->string('mileage')->nullable();
            $table->string('horsepower')->nullable();
            $table->string('car_color')->nullable();
            $table->text('description')->nullable();
            $table->json('car_images')->nullable();

            // Status fields
            $table->boolean('is_helper_needed')->default(false)->nullable();

            // Registration Details - all nullable
            $table->string('registration_number')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->string('number_plate_color')->nullable();
            $table->timestamp('registration_expiry')->nullable();
            $table->string('bill_book_number')->nullable();
            $table->string('bill_book_image')->nullable();

            // Insurance Details - all nullable
            $table->string('insurance_policy_no')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_type')->nullable();
            $table->timestamp('insurance_till')->nullable();
            $table->decimal('insurance_cost_per_annum', 10, 2)->nullable();
            $table->string('insurance_policy_document')->nullable();
            $table->string('passenger_insured')->nullable();
            $table->string('passenger_insured_amount')->nullable();
            $table->string('passenger_insurance_company')->nullable();

            // Additional catalog fields
            $table->string('engine_capacity')->nullable();
            $table->string('fuel_tank_capacity')->nullable();
            $table->string('top_speed')->nullable();
            $table->string('acceleration')->nullable(); // 0-100 km/h
            $table->string('drivetrain')->nullable(); // FWD, RWD, AWD
            $table->string('emission_standard')->nullable(); // BS6, Euro 6, etc.
            $table->json('features')->nullable(); // Array of features
            $table->json('safety_features')->nullable(); // Array of safety features
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_catalogs');
    }
};
