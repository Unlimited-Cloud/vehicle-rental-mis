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
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable();

            $table->string('invoice_number')->unique();

            $table->date('from_date');
            $table->date('to_date');

            $table->integer('days')->nullable();

            $table->decimal('rate_per_day', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);

            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->integer('version')->default(1);

            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
