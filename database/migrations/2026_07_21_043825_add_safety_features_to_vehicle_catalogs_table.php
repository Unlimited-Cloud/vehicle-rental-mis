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
        Schema::table('vehicle_catalogs', function (Blueprint $table) {
            $table->boolean('dash_cam')->nullable()->after('safety_features');
            $table->string('dash_cam_image')->nullable();

            $table->boolean('ebs')->nullable();
            $table->string('ebs_image')->nullable();

            $table->boolean('air_conditioning')->nullable();
            $table->string('air_conditioning_image')->nullable();

            $table->boolean('reverse_camera')->nullable();
            $table->string('reverse_camera_image')->nullable();

            $table->boolean('camera_360')->nullable();
            $table->string('camera_360_image')->nullable();

            $table->boolean('emergency_braking_system')->nullable();
            $table->string('emergency_braking_system_image')->nullable();

            $table->boolean('hillside_braking_system')->nullable();
            $table->string('hillside_braking_system_image')->nullable();

            $table->boolean('hill_descent_control')->nullable();
            $table->string('hill_descent_control_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_catalogs', function (Blueprint $table) {
            $table->dropColumn([
                'dash_cam',
                'dash_cam_image',
                'ebs',
                'ebs_image',
                'air_conditioning',
                'air_conditioning_image',
                'reverse_camera',
                'reverse_camera_image',
                'camera_360',
                'camera_360_image',
                'emergency_braking_system',
                'emergency_braking_system_image',
                'hillside_braking_system',
                'hillside_braking_system_image',
                'hill_descent_control',
                'hill_descent_control_image',
            ]);
        });
    }
};
