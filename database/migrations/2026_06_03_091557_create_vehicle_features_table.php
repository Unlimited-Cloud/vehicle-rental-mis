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
        Schema::create('vehicle_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');

            $table->boolean('dash_cam')->default(false);
            $table->string('dash_cam_image')->nullable();

            $table->boolean('ebs')->default(false);
            $table->string('ebs_image')->nullable();

            $table->boolean('air_conditioning')->default(false);
            $table->string('air_conditioning_image')->nullable();

            $table->boolean('reverse_camera')->default(false);
            $table->string('reverse_camera_image')->nullable();

            $table->boolean('camera_360')->default(false);
            $table->string('camera_360_image')->nullable();

            $table->boolean('emergency_braking_system')->default(false);
            $table->string('emergency_braking_system_image')->nullable();

            $table->boolean('hillside_braking_system')->default(false);
            $table->string('hillside_braking_system_image')->nullable();

            $table->boolean('hill_descent_control')->default(false);
            $table->string('hill_descent_control_image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_features');
    }
};
