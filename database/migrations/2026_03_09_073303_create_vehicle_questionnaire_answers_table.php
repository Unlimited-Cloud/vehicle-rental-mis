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
        Schema::create('vehicle_questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_moment_id');
            $table->unsignedBigInteger('questionnaire_id');
            $table->text('answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_questionnaire_answers');
    }
};
