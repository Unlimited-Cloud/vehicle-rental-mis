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
        Schema::create('vehicle_tyre_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('change_date')->nullable();
            $table->enum('tyre_position', ['FL', 'FR', 'BL', 'BR'])->nullable();
            $table->string('tyre_manufacturer')->nullable();
            $table->string('tyre_specifications')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('invoice_upload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_tyre_changes');
    }
};
