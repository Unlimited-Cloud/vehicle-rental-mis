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
        Schema::create('vehicle_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_booking_id');
            $table->foreignId('vehicle_moment_id');

            $table->foreignId('vehicle_id');
            $table->foreignId('customer_id');

            $table->string('receipt_number')->unique();

            $table->enum('invoice_type', ['vat', 'non_vat']);

            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');

            $table->integer('hours')->nullable();
            $table->integer('days')->nullable();

            $table->decimal('rate_per_day', 10, 2);

            $table->decimal('sub_total', 10, 2)->nullable();

            $table->decimal('discount', 10, 2)->default(0);

            $table->decimal('tax', 10, 2)->default(0);

            $table->decimal('total_amount', 10, 2);

            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_receipts');
    }
};
