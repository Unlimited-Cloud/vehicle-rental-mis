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
        Schema::create('crew_bank_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('account_holder_name');
            $table->string('account_number');
            $table->boolean('is_active')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_bank_details');
    }
};
