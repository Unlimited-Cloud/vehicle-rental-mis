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
        Schema::create('vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->date('service_date')->nullable();
            $table->string('service_done_at')->nullable();
            $table->text('service_details')->nullable();
            $table->decimal('service_amount', 10, 2)->nullable();
            $table->string('service_bill_copy')->nullable();

            $table->integer('next_service_km')->nullable();
            $table->date('next_service_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_services');
    }
};
