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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('normalized_name')->nullable();
            // Codes
            $table->string('swift_code')->nullable();
            $table->string('bank_code')->nullable();
            // External / Config Reference
            $table->unsignedBigInteger('configuration_id')->nullable();
            // Flags
            $table->boolean('is_source_account')->default(1);
            $table->boolean('is_payee_account')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
