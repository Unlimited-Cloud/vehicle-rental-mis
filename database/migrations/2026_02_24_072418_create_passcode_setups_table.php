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
        Schema::create('passcode_setups', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('otp_valid_minutes')->default(5);
            $table->tinyInteger('max_requests')->default(5);
            $table->tinyInteger('max_attempts')->default(5);
            $table->tinyInteger('window_minutes')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passcode_setups');
    }
};
